<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Cache password default agar tidak dihitung berulang.
     */
    protected static ?string $password;

    /**
     * Default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullName = $this->faker->name();

        return [
            'name' => Str::of($fullName)->explode(' ')->first(), // username pendek
            'full_name' => $fullName,                            // sesuai model kamu
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // default login: "password"
            'remember_token' => Str::random(10),
            'phone' => $this->faker->e164PhoneNumber(),          // +628xxxxxxxxxx (acak)
            'is_active' => 1,                                     // integer (1 aktif, 0 nonaktif)
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Email belum terverifikasi.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Non-aktif (is_active = 0).
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => 0,
        ]);
    }

    /**
     * Nomor telepon Indonesia (lebih natural).
     */
    public function indoPhone(): static
    {
        return $this->state(fn () => [
            // contoh simpel, silakan ganti kalau punya helper khusus
            'phone' => '+62' . $this->faker->numberBetween(8110000000, 8999999999),
        ]);
    }
}
