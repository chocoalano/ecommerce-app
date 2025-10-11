<?php

namespace Database\Factories\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auth\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name'              => $name,
            'full_name'         => $name,
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'), // ganti di seed/ENV
            'remember_token'    => Str::random(10),
            'phone'             => $this->faker->unique()->numerify('08##########'),
            'is_active'         => true,
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }

}
