<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\FooterSocial; // Pastikan Anda mengimpor Model ini

class SubscribeEmail extends Mailable
{
    use Queueable, SerializesModels;

    // Properti Konten (Dynamic Data)
    public string $mainTitle;
    public string $mainDescription;
    public string $shopNowUrl;
    public ?array $featuredProduct;
    public array $socialLinks;
    public string $logoUrl;
    public string $bannerImageUrl;
    public string $bottomBannerUrl;
    public string $closingMessage;
    public string $unsubscribeUrl;

    /**
     * Buat instance pesan baru.
     */
    public function __construct(array $data)
    {
        // 1. Tetapkan Nilai Default & Data Dinamis
        $this->mainTitle = $data['main_title'] ?? 'The New Collection is Here!';
        $this->mainDescription = $data['main_description'] ?? 'Explore our latest arrivals and refresh your style.';
        $this->shopNowUrl = $data['shop_now_url'] ?? URL::to('/shop'); // Menggunakan URL::to() lebih disarankan
        $this->featuredProduct = $data['featured_product'] ?? null;
        $this->logoUrl = $data['logo_url'] ?? asset('images/logo-puranura-id.png'); // Gunakan asset()
        $this->bannerImageUrl = $data['banner_image_url'] ?? asset('images/smartphone.png');
        $this->bottomBannerUrl = $data['bottom_banner_url'] ?? asset('images/galaxy-z-flip7-share-image.png');
        $this->closingMessage = $data['closing_message'] ?? 'P.S. Share your look with #NewCollection!';

        // 2. Ambil Data Model untuk Social Links (Mengatasi masalah serialisasi antrian)
        $this->socialLinks = $this->getSocialLinks($data);
        $this->unsubscribeUrl = $data['unsubscribe_url'] ?? URL::to('/unsubscribe'); // Gunakan URL::to()
    }

    /**
     * Dapatkan amplop pesan.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mainTitle,
        );
    }

    /**
     * Dapatkan definisi konten pesan.
     * Pastikan view menunjuk ke lokasi template Blade Anda yang sebenarnya.
     */
    public function content(): Content
    {
        return new Content(
            // Ganti 'mail.pages.subscribe' dengan lokasi template Anda yang benar (misal: emails.new_collection_promo)
            // Asumsi: 'mail.pages.subscribe' adalah lokasi yang benar sesuai setup Anda.
            view: 'mail.pages.subscribe',
        );
    }

    // --- Metode Bantuan Khusus ---

    /**
     * Mengambil dan memformat data social links dari database.
     */
    protected function getSocialLinks(array $data): array
    {
        // Jika data social links sudah disediakan di array $data, gunakan itu
        if (isset($data['social_links'])) {
            return $data['social_links'];
        }

        // Jika tidak, ambil dari Model FooterSocial
        if (class_exists(FooterSocial::class)) {
             return FooterSocial::where('is_active', true)
                ->get()
                ->map(fn ($social) => [
                    'platform' => $social->name ?? 'Social', // Asumsi ada kolom 'name'
                    'url' => $social->url,
                    // Pastikan ikon dapat diakses publik saat email dibuka
                    'icon' => URL::asset('storage/' . $social->icon),
                ])
                ->toArray();
        }

        // Nilai default jika Model tidak ada atau data tidak ditemukan
        return [
            ['platform' => 'Facebook', 'url' => 'https://facebook.com', 'icon' => URL::asset('icons/fb.png')],
            ['platform' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => URL::asset('icons/ig.png')],
        ];
    }
}
