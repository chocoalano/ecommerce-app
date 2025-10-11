<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auth\CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'   => Customer::factory(),
            'label'         => $this->faker->randomElement(['Rumah', 'Kantor', 'Utama']),
            'recipient_name'=> $this->faker->name(),
            'phone'         => $this->faker->numerify('08##########'),
            'line1'         => $this->faker->streetAddress(),
            'line2'         => $this->faker->streetAddress(),
            'city'          => $this->faker->city(),
            'province'      => $this->faker->state(),
            'postal_code'   => $this->faker->postcode(),
            'country'       => 'ID',
            'is_default'    => false,
        ];
    }

    public function default(): self
    {
        return $this->state(fn () => ['is_default' => true]);
    }

}
