<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'         => (string) Str::uuid(), // ✅ WAJIB
            'nama'       => $this->faker->randomElement([
                'Laptop',
                'Monitor',
                'Keyboard',
                'Mouse',
                'Printer',
                'Scanner',
                'Router',
                'Switch',
                'Access Point',
                'Camera CCTV',
            ]) . ' ' . $this->faker->numerify('###'),

            'identitas'  => strtoupper($this->faker->bothify('AST-####-??')),
            'jumlah'     => $this->faker->numberBetween(1, 20),
            'catatan'    => $this->faker->sentence(6),
            'status'     => $this->faker->boolean(),
        ];
    }
}

