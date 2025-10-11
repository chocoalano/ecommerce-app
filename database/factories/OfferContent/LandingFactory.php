<?php

namespace Database\Factories\OfferContent;

use App\Models\Promo\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<OfferContent\Landing>
 */
class LandingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);
        $slug  = Str::slug($title) . '-' . Str::random(5);

        $hero = $this->faker->imageUrl(1600, 900, 'business', true);

        // Random jadwal (kadang null)
        $start = $this->faker->optional(0.6)->dateTimeBetween('-5 days', '+2 days');
        $end   = $start ? $this->faker->optional(0.7)->dateTimeBetween($start, '+10 days') : null;

        return [
            'promotion_id'  => null, // gunakan state ->withPromotion() jika perlu
            'slug'          => $slug,
            'title'         => $title,
            'hero_image_url'=> $hero,
            'start_at'      => $start,
            'end_at'        => $end,
            'meta_json'     => [
                'title'       => $title,
                'description' => $this->faker->sentence(12),
                'og_image'    => $hero,
                'keywords'    => $this->faker->words(5),
            ],
            'is_active'     => $this->faker->boolean(85),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function liveNow(): self
    {
        return $this->state(function () {
            $now = now();
            return [
                'is_active' => true,
                'start_at'  => $now->copy()->subDay(),
                'end_at'    => $now->copy()->addDays(7),
            ];
        });
    }

    public function withPromotion(int|Promotion $promotion = null): self
    {
        return $this->state(function () use ($promotion) {
            if ($promotion instanceof Promotion) {
                return ['promotion_id' => $promotion->id];
            }
            return [
                'promotion_id' => Promotion::factory(),
            ];
        });
    }

}
