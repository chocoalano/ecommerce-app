<?php

namespace App\Services;

use App\Models\Promo\Promotion;
use Illuminate\Support\Facades\Cache;

class PromotionService
{
    /**
     * Ambil promo berdasarkan slot, dengan cache 5 menit (300 detik).
     */
    public function getPromotionSlot(string $slot): ?Promotion
    {
        return Cache::remember("promo:slot:{$slot}", 300, function () use ($slot) {
            return Promotion::query()
                ->where('is_active', true)
                ->where('show_on', $slot)
                ->orderByDesc('priority')
                ->first();
        });
    }

    /**
     * Ambil promo kedua untuk slot HERO, mengecualikan promo pertama.
     */
    public function getSecondHeroPromo(?Promotion $firstPromo): ?Promotion
    {
        return Cache::remember('promo:slot:HERO:second', 300, function () use ($firstPromo) {
            $query = Promotion::query()
                ->with('products')
                ->where('is_active', true)
                ->where('show_on', 'HERO')
                ->orderByDesc('priority');

            if ($firstPromo) {
                $query->whereKeyNot($firstPromo->getKey());
            }

            return $query->first();
        });
    }

    /**
     * Format data Promo untuk komponen view.
     */
    public function formatPromotionProps(?Promotion $promo, string $defaultName, string $defaultDesc): array
    {
        $slug = data_get($promo, 'landing_slug', '');
        $image = data_get($promo, 'image');
        $name = data_get($promo, 'name', $defaultName);
        $desc = data_get($promo, 'description', $defaultDesc);

        return [
            'promo' => $promo, // Objek promo
            'name'  => $name,
            'desc'  => $desc,
            'slug'  => $slug,
            'image' => $image ?: asset('images/galaxy-z-flip7-share-image.png'),
        ];
    }
}
