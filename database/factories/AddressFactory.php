<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null, // diisi saat pemanggilan factory di seeder
            'label' => $this->faker->randomElement(['Rumah', 'Kantor', 'Apartemen']),
            'recipient_name' => $this->faker->name(),
            'phone' => $this->faker->e164PhoneNumber(),
            'line1' => $this->faker->streetAddress(),
            'line2' => $this->faker->optional()->address(),
            'city' => $this->faker->city(),
            'province' => $this->faker->randomElement([
                'DKI Jakarta',
                'Jawa Barat',
                'Jawa Tengah',
                'Jawa Timur',
                'Bali'
            ]),
            'postal_code' => $this->faker->postcode(),
            'country' => 'ID',
            'is_default' => $this->faker->boolean(30),
        ];
    }
}
