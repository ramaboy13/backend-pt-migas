<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Repositories\AssetRepository;
use Database\Factories\AssetFactory;

class AssetSeeder extends Seeder
{
    public function __construct(private AssetRepository $repository) {}

    public function run(): void
    {
        for ($i = 0; $i < 100000; $i++) {
            $data = AssetFactory::new()->make()->toArray();
            $this->repository->create($data);
        }
    }
}
