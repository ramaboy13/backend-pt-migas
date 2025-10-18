// app/Services/PayrollCalculationService.php
namespace App\Services;

use App\Models\TbKaryawan;
use App\Models\TbLemburKaryawan;
use App\Models\TbPendapatan;
use App\Models\TbPotongan;
use App\Models\TbGajiKaryawan;
use Illuminate\Support\Facades\DB;

class PayrollCalculationService
{
    /**
     * Calculate lembur payroll
     */
    public function calculateLemburPayroll($karyawanId, $tanggal)
    {
        $karyawan = TbKaryawan::findOrFail($karyawanId);
        $upahLemburPerJam = $karyawan->gapok / 173;
        
        // Logic calculation di service
        return [
            'upah_lembur_perjam' => $upahLemburPerJam,
            'rupiah_lembur' => $upahLemburPerJam * $totalJamLembur
        ];
    }
    
    /**
     * Calculate total pendapatan
     */
    public function calculateTotalPendapatan($karyawanId, $periode)
    {
        $karyawan = TbKaryawan::findOrFail($karyawanId);
        $totalLembur = TbLemburKaryawan::where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', date('m', strtotime($periode)))
            ->whereYear('tanggal', date('Y', strtotime($periode)))
            ->sum('rupiah_lembur');
        
        return $karyawan->gapok + $totalLembur + $tunjangan;
    }
    
    /**
     * Calculate potongan
     */
    public function calculatePotongan($karyawanId)
    {
        $karyawan = TbKaryawan::findOrFail($karyawanId);
        
        return [
            'rp_bpjs_kesehatan' => ($karyawan->gapok * $karyawan->bpjs_kesehatan) / 100,
            'rp_bpjs_tenagakerja' => ($karyawan->gapok * $karyawan->bpjs_tenagakerja) / 100
        ];
    }
    
    /**
     * Process full payroll calculation
     */
    public function processMonthlyPayroll($karyawanId, $periode, $tunjangan = 0)
    {
        return DB::transaction(function () use ($karyawanId, $periode, $tunjangan) {
            // 1. Calculate pendapatan
            $totalPendapatan = $this->calculateTotalPendapatan($karyawanId, $periode, $tunjangan);
            
            // 2. Calculate potongan
            $potongan = $this->calculatePotongan($karyawanId);
            $totalPotongan = $potongan['rp_bpjs_kesehatan'] + $potongan['rp_bpjs_tenagakerja'];
            
            // 3. Calculate gaji bersih
            $subtotal = $totalPendapatan - $totalPotongan;
            $gajiBersih = $subtotal - $pph21; // PPH21 logic bisa ditambahkan
            
            return [
                'total_pendapatan' => $totalPendapatan,
                'potongan' => $potongan,
                'total_potongan' => $totalPotongan,
                'subtotal' => $subtotal,
                'gaji_bersih' => $gajiBersih
            ];
        });
    }
}