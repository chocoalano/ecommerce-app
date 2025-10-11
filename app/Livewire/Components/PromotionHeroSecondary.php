<?php

namespace App\Livewire\Components;

use App\Models\Promo\Promotion;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class PromotionHeroSecondary extends Component
{
    /** Kolom show_on di tabel promotions (mis. 'HERO', 'HERO_ACCESSORIES', dll) */
    public string $showOn = 'HERO';

    /** Optional: pakai promotion tertentu (override showOn) */
    public ?int $promotionId = null;

    /** TTL cache (detik) */
    public int $cacheTtl = 300;

    /** Opsi kelas tambahan untuk section root */
    public string $sectionClass = 'bg-gray-100 rounded-2xl overflow-hidden mb-10 lg:mb-12 ring-1 ring-gray-200/60';

    /** Data yang sudah dipetakan untuk view */
    public array $data = [];

    public function mount(string $showOn = 'HERO', ?int $promotionId = null, int $cacheTtl = 300, string $sectionClass = null): void
    {
        $this->showOn      = $showOn ?: $this->showOn;
        $this->promotionId = $promotionId;
        $this->cacheTtl    = max(60, $cacheTtl); // minimal 60s
        if ($sectionClass !== null) {
            $this->sectionClass = $sectionClass;
        }

        $cacheKey = $this->promotionId
            ? "promotion:hero:id:{$this->promotionId}"
            : "promotion:hero:show_on:{$this->showOn}";

        $promo = Cache::remember($cacheKey, $this->cacheTtl, function () {
            $query = Promotion::query()
                ->where('is_active', true)
                ->orderByDesc('priority');

            if ($this->promotionId) {
                $query->whereKey($this->promotionId);
            } else {
                $query->where('show_on', $this->showOn);
            }

            return $query->first();
        });

        // Mapping field + fallback
        $title = data_get($promo, 'title') ?? data_get($promo, 'name') ?? 'Aksesoris Ponsel';
        $desc  = data_get($promo, 'subtitle') ?? data_get($promo, 'description') ?? 'Temukan aksesoris terbaik untuk melengkapi pengalaman ponsel Anda. Dari casing hingga power bank—semuanya ada.';
        $badge = data_get($promo, 'type', 'Promo');
        $slug  = data_get($promo, 'landing_slug');

        // CTA utama
        $ctaLabel = data_get($promo, 'cta_label', 'Jelajahi Sekarang');
        $ctaUrl   = data_get($promo, 'cta_url');
        if (!$ctaUrl && $slug && Route::has('category')) {
            $ctaUrl = route('products.index', ['category' => $slug]);
        }
        $ctaUrl = $ctaUrl ?: '#';

        // CTA sekunder (opsional)
        $secondaryLabel = data_get($promo, 'secondary_label');
        $secondaryUrl   = data_get($promo, 'secondary_url');
        if (!$secondaryUrl && $slug && Route::has('promotion.show')) {
            $secondaryUrl = route('promotion.show', ['promotion' => $slug]);
        }

        // Gambar
        $image = data_get($promo, 'image') ?? data_get($promo, 'image_url') ?? asset('images/galaxy-z-flip7-share-image.png');
        if ($image && ! Str::startsWith($image, ['http://', 'https://', 'data:image'])) {
            $image = asset($image);
        }
        $imageAlt = data_get($promo, 'image_alt') ?? $title;

        $this->data = [
            'title'          => $title,
            'desc'           => $desc,
            'badge'          => $badge,
            'ctaLabel'       => $ctaLabel,
            'ctaUrl'         => $ctaUrl,
            'secondaryLabel' => $secondaryLabel,
            'secondaryUrl'   => $secondaryUrl,
            'image'          => $image,
            'imageAlt'       => $imageAlt,
        ];
    }

    public function render()
    {
        return view('livewire.components.promotion-hero-secondary');
    }
}
