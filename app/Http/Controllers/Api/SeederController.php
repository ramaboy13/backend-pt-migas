<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use Database\Factories\AssetFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeederController extends Controller
{
    public function seedAssets(Request $request)
    {
        // 🔐 proteksi sederhana
        if ($request->query('token') !== config('app.seeder_token')) {
            abort(403);
        }

        $limit = (int) $request->query('limit', 500);
        $offset = (int) $request->query('offset', 0);

        $data = [];

        for ($i = 0; $i < $limit; $i++) {
            $data[] = AssetFactory::new()->make()->toArray();
        }

        DB::table('assets')->insert($data);

        return response()->json([
            'inserted' => $limit,
            'offset'   => $offset + $limit,
            'done'     => $offset + $limit >= 100000,
        ]);
    }
}
