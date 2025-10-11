<?php

namespace Database\Factories\OrderProduct;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'      => $this->faker->unique()->randomElement(['JNE','SICEPAT','JNT','POS','ANTERAJA']),
            'name'      => $this->faker->company(),
            'is_active' => true,
        ];
    }

}
