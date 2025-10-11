<?php

namespace Database\Factories\OfferContent;

use App\Models\Auth\Customer;
use App\Models\OfferContent\Landing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<OfferContent\TrackingUtm>
 */
class TrackingUtmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'landing_id'   => $this->faker->optional(0.7)->passthrough(Landing::factory()),
            'utm_source'   => $this->faker->randomElement(['google','facebook','tiktok','email','affiliate']),
            'utm_medium'   => $this->faker->randomElement(['cpc','banner','video','social','newsletter']),
            'utm_campaign' => 'cmp-' . Str::random(6),
            'utm_content'  => $this->faker->randomElement(['adset_a','adset_b','hero','sidebar','teaser']),
            'utm_term'     => $this->faker->optional()->word(),
            'session_id'   => Str::uuid()->toString(),
            'customer_id'  => null, // gunakan state ->forCustomer() jika perlu
        ];
    }

    public function forLanding(Landing $landing): self
    {
        return $this->state(fn () => ['landing_id' => $landing->id]);
    }

    public function forCustomer(int|Customer $customer): self
    {
        return $this->state(function () use ($customer) {
            return ['customer_id' => $customer instanceof Customer ? $customer->id : $customer];
        });
    }

    public function source(string $source): self
    {
        return $this->state(fn () => ['utm_source' => $source]);
    }

    public function campaign(string $campaign): self
    {
        return $this->state(fn () => ['utm_campaign' => $campaign]);
    }

}
