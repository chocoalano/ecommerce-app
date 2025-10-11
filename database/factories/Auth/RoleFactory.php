<?php

namespace Database\Factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auth\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = Str::upper($this->faker->unique()->randomElement([
            'ADMIN','CUSTOMER','CS','FINANCE','WAREHOUSE'
        ]));

        return [
            'code' => $code,
            'name' => ucwords(strtolower($code)),
        ];
    }
}
