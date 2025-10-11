<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            // Company Pages
            [
                'title' => 'Tentang Kami',
                'slug' => 'tentang-kami',
                'content' => 'Kami adalah perusahaan yang berkomitmen untuk menyediakan bahan baku minuman berkualitas tinggi untuk industri HORECA dan UMKM. Dengan pengalaman bertahun-tahun, kami memahami kebutuhan pelanggan dan selalu berusaha memberikan produk terbaik dengan harga yang kompetitif.',
                'excerpt' => 'Pelajari lebih lanjut tentang sejarah, visi, dan misi perusahaan kami.',
                'category' => 'company',
                'sort_order' => 1,
                'meta_title' => 'Tentang Kami - Penyedia Bahan Baku Minuman Berkualitas',
                'meta_description' => 'Pelajari tentang perusahaan kami yang menyediakan bahan baku minuman berkualitas untuk HORECA dan UMKM.',
            ],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'content' => 'Temukan artikel menarik, tips, dan tren terbaru dalam industri minuman. Blog kami menyajikan informasi berguna untuk membantu bisnis Anda berkembang.',
                'excerpt' => 'Artikel, tips, dan tren terbaru dalam industri minuman.',
                'category' => 'company',
                'sort_order' => 2,
                'meta_title' => 'Blog - Tips dan Tren Industri Minuman',
                'meta_description' => 'Baca artikel terbaru tentang tips bisnis minuman dan tren industri HORECA.',
            ],

            // Help Pages
            [
                'title' => 'Cara Belanja',
                'slug' => 'cara-belanja',
                'content' => 'Panduan lengkap cara berbelanja di toko online kami:

1. Pilih produk yang Anda inginkan
2. Tambahkan ke keranjang belanja
3. Isi data pengiriman dengan lengkap
4. Pilih metode pembayaran
5. Konfirmasi pesanan Anda
6. Lakukan pembayaran sesuai instruksi
7. Tunggu konfirmasi dan pengiriman

Tim customer service kami siap membantu Anda 24/7 jika mengalami kesulitan.',
                'excerpt' => 'Panduan lengkap cara berbelanja di toko online kami.',
                'category' => 'help',
                'sort_order' => 1,
                'meta_title' => 'Cara Belanja - Panduan Lengkap Berbelanja Online',
                'meta_description' => 'Pelajari cara mudah berbelanja di toko online kami dengan panduan step-by-step.',
            ],
            [
                'title' => 'Pengiriman',
                'slug' => 'pengiriman',
                'content' => 'Informasi pengiriman:

JANGKAUAN PENGIRIMAN:
- Seluruh Indonesia melalui ekspedisi terpercaya
- Pengiriman same-day untuk area Jakarta & sekitarnya
- Gratis ongkir untuk pembelian minimal Rp 500.000

ESTIMASI WAKTU:
- Jakarta & sekitarnya: 1-2 hari kerja
- Pulau Jawa: 2-3 hari kerja
- Luar Pulau Jawa: 3-7 hari kerja

EKSPEDISI YANG DIGUNAKAN:
- JNE, TIKI, J&T Express, SiCepat, Anteraja

Barang akan dikemas dengan aman untuk menjaga kualitas produk selama pengiriman.',
                'excerpt' => 'Informasi lengkap tentang pengiriman dan estimasi waktu.',
                'category' => 'help',
                'sort_order' => 2,
                'meta_title' => 'Pengiriman - Informasi Pengiriman dan Estimasi Waktu',
                'meta_description' => 'Ketahui informasi lengkap tentang pengiriman, ekspedisi, dan estimasi waktu pengiriman.',
            ],
            [
                'title' => 'Retur & Refund',
                'slug' => 'retur-refund',
                'content' => 'Kebijakan retur dan refund:

SYARAT RETUR:
- Barang dalam kondisi tidak rusak akibat kesalahan packing
- Laporan maksimal 2x24 jam setelah barang diterima
- Foto/video unboxing sebagai bukti
- Barang belum digunakan (untuk produk tertentu)

PROSES REFUND:
- Refund akan diproses setelah barang retur diterima
- Dana akan dikembalikan dalam 3-7 hari kerja
- Refund sesuai metode pembayaran awal

BARANG YANG TIDAK DAPAT DIRETUR:
- Produk yang sudah dibuka/digunakan (untuk kategori tertentu)
- Barang rusak akibat kelalaian pembeli

Hubungi customer service untuk bantuan proses retur.',
                'excerpt' => 'Ketahui kebijakan retur dan refund untuk pembelian Anda.',
                'category' => 'help',
                'sort_order' => 3,
                'meta_title' => 'Retur & Refund - Kebijakan Pengembalian Barang',
                'meta_description' => 'Pelajari kebijakan retur dan refund untuk pembelian di toko online kami.',
            ],
            [
                'title' => 'Hubungi Kami',
                'slug' => 'hubungi-kami',
                'content' => 'Butuh bantuan? Hubungi kami melalui:

CUSTOMER SERVICE:
- WhatsApp: ' . env('COMPANY_PHONE', '+62 8xx-xxxx-xxxx') . '
- Email: ' . env('COMPANY_EMAIL', 'support@domain.com') . '
- Jam Operasional: ' . env('COMPANY_OPERATING_HOURS', 'Senin–Sabtu 09:00–18:00 WIB') . '

ALAMAT KANTOR:
[Alamat kantor akan ditambahkan sesuai lokasi perusahaan]

MEDIA SOSIAL:
- Instagram: @namacompany
- Facebook: Nama Company
- TikTok: @namacompany

Tim customer service kami siap membantu Anda dengan segala pertanyaan dan kendala yang mungkin Anda alami.',
                'excerpt' => 'Berbagai cara untuk menghubungi tim customer service kami.',
                'category' => 'help',
                'sort_order' => 4,
                'meta_title' => 'Hubungi Kami - Customer Service dan Kontak',
                'meta_description' => 'Hubungi customer service kami melalui WhatsApp, email, atau media sosial untuk bantuan.',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }
    }
}
