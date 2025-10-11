<?php

namespace Database\Factories\Promo;

use App\Models\Promo\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promo\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Promo ' . Str::title($this->faker->words(2, true));
        $start = now()->subDays($this->faker->numberBetween(0, 3));
        $end   = now()->addDays($this->faker->numberBetween(3, 14));

        $typeKey = $this->faker->randomElement(array_keys(Promotion::TYPE));
        $showOnKey = $this->faker->randomElement(array_keys(Promotion::SHOW_ON));
        $pageKey = $this->faker->randomElement(array_keys(Promotion::PAGE));
        // dd($typeKey, $showOnKey, $pageKey);

        return [
            'code'            => Str::upper(Str::random(8)),
            'name'            => $name,
            'type'            => $typeKey,
            'landing_slug'    => $this->faker->slug(),
            'description'     => $this->faker->paragraph(),
            'start_at'        => $start,
            'end_at'          => $end,
            'is_active'       => true,
            'priority'        => $this->faker->numberBetween(1, 200),
            'max_redemption'  => $this->faker->numberBetween(100, 10000),
            'per_user_limit'  => $this->faker->numberBetween(1, 10),
            'conditions_json' => $this->faker->randomElement([
                ['channel' => 'web'], ['bank' => 'BNI'], ['min_spend' => 100000]
            ]),
            'show_on'         => $showOnKey,
            'custom_html'     => null,
            'page'            => $pageKey,
        ];
    }

    /** States praktis */
    public function percent(int $percent = 10): self
    {
        return $this->state(fn () => ['type' => 'PERCENT_DISCOUNT'])
            ->afterCreating(function (Promotion $p) use ($percent) {
                // isi nilai nanti di PromotionProductFactory (pivot)
            });
    }

    public function fixed(int $amount = 10000): self
    {
    return $this->state(fn () => ['type' => 'FIXED_DISCOUNT']);
    }

    public function bundle(): self
    {
    return $this->state(fn () => ['type' => 'BUNDLE_PRICE']);
    }

    public function flash(): self
    {
    return $this->state(fn () => ['type' => 'FLASH_SALE', 'priority' => 1]);
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function future(): self
    {
        return $this->state(function () {
            $start = now()->addDays(2);
            return ['start_at' => $start, 'end_at' => $start->copy()->addDays(7)];
        });
    }

}
