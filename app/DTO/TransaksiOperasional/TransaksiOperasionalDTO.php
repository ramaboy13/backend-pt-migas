<?php

namespace App\DTO\TransaksiOperasional;

use App\DTO\Pangkalan\PangkalanDTO;
use App\DTO\Tabung\TabungDTO;
use Carbon\Carbon;

class TransaksiOperasionalDTO
{
  public function __construct(
    public string $id,
    public string $tanggal,
    public string $no_ref,
    public string $keterangan,
    public string $pangkalan_id,
    public string $tabung_id,
    public bool $is_in,
    public int $qty,
    public string $unit,
    public float $harga_satuan,
    public float $debit,
    public float $credit,
    public float $total,
    public string $created_at,
    public string $updated_at,
    public ?PangkalanDTO $pangkalan = null,
    public ?TabungDTO $tabung = null
  ) {}

  /**
   * Convert from Model to DTO
   */
  public static function fromModel(object $transaksi): self
  {
    $pangkalanDTO = null;
    $tabungDTO = null;

    // Load Pangkalan DTO jika relation loaded
    if ($transaksi->relationLoaded('pangkalan') && $transaksi->pangkalan) {
      $pangkalanDTO = PangkalanDTO::fromModel($transaksi->pangkalan);
    }

    // Load Tabung DTO jika relation loaded
    if ($transaksi->relationLoaded('tabung') && $transaksi->tabung) {
      $tabungDTO = TabungDTO::fromModel($transaksi->tabung);
    }

    return new self(
      id: $transaksi->id,
      tanggal: Carbon::parse($transaksi->tanggal)->toISOString(),
      no_ref: $transaksi->no_ref,
      keterangan: $transaksi->keterangan,
      pangkalan_id: $transaksi->pangkalan_id,
      tabung_id: $transaksi->tabung_id,
      is_in: (bool) $transaksi->is_in,
      qty: (int) $transaksi->qty,
      unit: $transaksi->unit,
      harga_satuan: (float) $transaksi->harga_satuan,
      debit: (float) $transaksi->debit,
      credit: (float) $transaksi->credit,
      total: (float) $transaksi->total,
      created_at: Carbon::parse($transaksi->created_at)->toISOString(),
      updated_at: Carbon::parse($transaksi->updated_at)->toISOString(),
      pangkalan: $pangkalanDTO,
      tabung: $tabungDTO
    );
  }

  /**
   * Convert DTO to array
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'tanggal' => $this->tanggal,
      'no_ref' => $this->no_ref,
      'keterangan' => $this->keterangan,
      'pangkalan_id' => $this->pangkalan_id,
      'tabung_id' => $this->tabung_id,
      'is_in' => $this->is_in,
      'qty' => $this->qty,
      'unit' => $this->unit,
      'harga_satuan' => $this->harga_satuan,
      'debit' => $this->debit,
      'credit' => $this->credit,
      'total' => $this->total,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'pangkalan' => $this->pangkalan?->toArray(),
      'tabung' => $this->tabung?->toArray()
    ];
  }
}
