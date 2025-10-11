<?php

namespace Database\Factories\OfferContent;

use App\Models\OfferContent\Landing;
use App\Models\OfferContent\LandingSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferContent\LandingSection>
 */
class LandingSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(LandingSection::TYPES);

        return [
            'landing_id'   => Landing::factory(),
            'type'         => $type,
            'content_json' => $this->fakeContent($type),
            'sort_order'   => $this->faker->numberBetween(0, 20),
            'is_active'    => $this->faker->boolean(90),
        ];
    }

    protected function fakeContent(string $type): array
    {
        return match ($type) {
            'text' => [
                'heading' => $this->faker->sentence(5),
                'body'    => $this->faker->paragraphs(2, true),
                'cta'     => ['label' => 'Shop Now', 'url' => '/products'],
            ],
            'grid' => [
                'heading' => $this->faker->sentence(4),
                'items'   => collect(range(1, 6))->map(fn() => [
                    'title' => $this->faker->words(2, true),
                    'image' => $this->faker->imageUrl(600, 600, 'technics', true),
                    'url'   => $this->faker->url(),
                ])->all(),
            ],
            'carousel' => [
                'slides' => collect(range(1, 4))->map(fn() => [
                    'image'   => $this->faker->imageUrl(1600, 600, 'abstract', true),
                    'caption' => $this->faker->sentence(6),
                    'url'     => $this->faker->url(),
                ])->all(),
                'autoplay' => true,
            ],
            'faq' => [
                'items' => collect(range(1, 4))->map(fn() => [
                    'q' => $this->faker->sentence(6),
                    'a' => $this->faker->paragraph(),
                ])->all(),
            ],
            'terms' => [
                'title' => 'Syarat & Ketentuan',
                'html'  => '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            ],
            'products' => [
                'heading' => 'Produk Pilihan',
                'layout'  => 'grid-4',
                'note'    => 'Produk diambil dari tabel landing_products',
            ],
            default => [],
        };
    }

    public function type(string $type): self
    {
        return $this->state(fn () => ['type' => $type]);
    }

    public function ordered(int $order): self
    {
        return $this->state(fn () => ['sort_order' => $order]);
    }

}
