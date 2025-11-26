<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => $this->faker->randomElement([
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

            'identity' => strtoupper($this->faker->bothify('AST-####-??')),
            'qty'      => $this->faker->numberBetween(1, 20),
            'note'     => $this->faker->sentence(6),
            'its_rfu'  => $this->faker->boolean(),
        ];
    }
}
