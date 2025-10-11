<?php

namespace Database\Seeders;

use App\Models\Auth\Customer;
use App\Models\Product\Product;
use App\Models\Product\ProductReview;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get some products and customers
        $products = Product::limit(5)->get();
        $customers = Customer::limit(10)->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            $this->command->info('Tidak ada produk atau customer. Seeder dibatalkan.');
            return;
        }

        $reviewTitles = [
            'Produk sangat berkualitas!',
            'Sesuai ekspektasi',
            'Pengalaman berbelanja yang memuaskan',
            'Kualitas bagus dengan harga terjangkau',
            'Produk original dan berkualitas',
            'Pelayanan ramah dan produk OK',
            'Worth it untuk dibeli',
            'Produk sesuai deskripsi',
            'Packaging rapi dan aman',
            'Recommended seller dan produk',
        ];

        $reviewComments = [
            'Produk sampai dengan selamat, packaging rapi dan aman. Kualitas produk sangat baik sesuai dengan deskripsi. Pelayanan penjual juga sangat memuaskan. Recommended!',
            'Barang sesuai dengan foto dan deskripsi. Kualitas ok untuk harga segini. Pengiriman cepat dan packaging aman. Terima kasih!',
            'Alhamdulillah produk sudah sampai dengan selamat. Kualitas bagus, sesuai ekspektasi. Harga juga terjangkau. Pelayanan penjual responsif.',
            'Produk original dan berkualitas. Packaging sangat rapi dan aman. Pengiriman cepat. Penjual responsif dan ramah. Puas dengan pembelian ini.',
            'Barang sesuai gambar dan deskripsi. Kualitas memuaskan untuk harga yang ditawarkan. Pengiriman standar, packaging ok.',
            'Produk bagus, sesuai ekspektasi. Pelayanan penjual ramah dan fast response. Packaging rapi. Akan order lagi kalau ada kebutuhan.',
            'Kualitas produk sangat baik, sesuai dengan yang diharapkan. Packaging aman dan rapi. Pengiriman cepat. Terima kasih!',
            'Barang sampai dengan kondisi baik. Kualitas sesuai harga. Penjual komunikatif dan ramah. Packaging cukup aman.',
            'Produk berkualitas dan sesuai deskripsi. Pelayanan excellent, fast response. Packaging sangat rapi dan aman. Highly recommended!',
            'Alhamdulillah barang sudah sampai. Kualitas bagus, sesuai foto. Harga worth it. Packaging ok, pengiriman standar.',
        ];

        foreach ($products as $product) {
            // Generate 3-8 reviews per product
            $reviewCount = rand(3, 8);

            for ($i = 0; $i < $reviewCount; $i++) {
                $customer = $customers->random();

                // Check if customer already reviewed this product
                if (ProductReview::where('product_id', $product->id)
                    ->where('customer_id', $customer->id)
                    ->exists()) {
                    continue;
                }

                ProductReview::create([
                    'product_id' => $product->id,
                    'customer_id' => $customer->id,
                    'rating' => $faker->numberBetween(3, 5), // Mostly positive reviews
                    'title' => $faker->randomElement($reviewTitles),
                    'comment' => $faker->randomElement($reviewComments),
                    'is_approved' => $faker->boolean(85), // 85% approved
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Product reviews seeded successfully!');
    }
}
