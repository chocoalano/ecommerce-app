<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // enum values untuk MySQL (jenis promo)
        $promoEnum = [
            'PERCENT_DISCOUNT',        // diskon persentase
            'FIXED_DISCOUNT',          // diskon nominal
            'CASHBACK',                // cashback
            'BUNDLE_PRICE',            // harga bundling
            'GIFT_WITH_PURCHASE',      // hadiah pembelian
            'BANK_INSTALLMENT',        // cicilan bank
            'PAYMENT_METHOD_DISCOUNT', // diskon metode bayar
            'FLASH_SALE',              // flash sale
            'TRADE_IN'                 // tukar tambah
        ];

        /**
         * Tabel: promotions
         * Master promosi/offer dengan periode, rule umum, dan prioritas aplikasi.
         */
        Schema::create('promotions', function (Blueprint $table) use ($promoEnum) {
            $table->bigIncrements('id')->comment('Primary key promo');
            $table->string('code', 100)->unique()->comment('Kode unik promo (mis. GALAXYWEEK)');
            $table->string('name', 255)->comment('Nama promo untuk tampilan');
            $table->enum('type', $promoEnum)->comment('Jenis promo');
            $table->string('landing_slug', 255)->nullable()->comment('Slug halaman landing/offer (opsional)');
            $table->longText('description')->nullable()->comment('Deskripsi/ketentuan promo (rich text)');
            $table->timestamp('start_at')->comment('Waktu mulai promo (UTC/DB time)');
            $table->timestamp('end_at')->comment('Waktu akhir promo');
            $table->boolean('is_active')->default(true)->index()->comment('Status aktif promo');
            $table->integer('priority')->default(100)->index()->comment('Prioritas aplikasi promo (angka kecil = lebih prioritas)');
            $table->integer('max_redemption')->nullable()->comment('Kuota global pemakaian promo');
            $table->integer('per_user_limit')->nullable()->comment('Batas penggunaan per user');
            $table->boolean('is_condition_active')->default(false)->comment('Apakah kondisi granular aktif (true = kondisi berlaku, false = tidak)');
            $table->enum('show_on', ['HERO', 'BANNER'])->default('HERO')->comment('Tampilkan pada section HERO/BANNER');
            $table->longText('custom_html')->nullable()->comment('HTML kustom untuk tampilan promo');
            $table->enum('page', ['beranda'])->default('beranda')->comment('Tampilkan pada halaman tertentu');
            $table->timestamps();

            $table->comment('Master promosi: definisi, periode, dan rule global');
        });

        /**
         * Tabel: promotion_products
         * Scoping promo ke produk tertentu + parameter diskon/bundling.
         * (Tanpa variant; relasi hanya ke products)
         */
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key mapping promo-produk');

            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke promotions.id');

            // Boleh null untuk promo global; gunakan conditions_json pada promotions
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('Opsional: FK ke products.id (level produk)');

            $table->unsignedInteger('min_qty')->default(1)->comment('Minimal qty untuk eligible promo');

            // nilai-nilai spesifik tipe promo
            $table->decimal('discount_value', 18, 2)->nullable()->comment('Diskon nominal (FIXED_DISCOUNT)');
            $table->decimal('discount_percent', 5, 2)->nullable()->comment('Diskon persen (PERCENT_DISCOUNT)');
            $table->decimal('bundle_price', 18, 2)->nullable()->comment('Harga bundling (BUNDLE_PRICE)');

            $table->timestamps();

            // Hindari duplikasi baris untuk kombinasi yang sama
            $table->unique(['promotion_id', 'product_id'], 'promotion_products_unique');

            // Indeks untuk query umum
            $table->index(['promotion_id', 'min_qty']);
            $table->comment('Cakupan produk yang terkena promo + parameter diskonnya (tanpa variant)');
        });

        /**
         * (Opsional) Tambahkan CHECK untuk menegakkan kolom yang valid per tipe.
         * Didukung MySQL 8.0+ / PostgreSQL. Abaikan jika tidak didukung.
         */
        try {
            DB::statement("
                ALTER TABLE promotion_products
                ADD CONSTRAINT chk_promo_values
                CHECK (
                    (discount_percent IS NOT NULL AND discount_percent >= 0 AND discount_percent <= 100 AND discount_value IS NULL AND bundle_price IS NULL)
                    OR (discount_value IS NOT NULL AND discount_value >= 0 AND discount_percent IS NULL AND bundle_price IS NULL)
                    OR (bundle_price IS NOT NULL AND bundle_price >= 0 AND discount_percent IS NULL AND discount_value IS NULL)
                    OR (discount_percent IS NULL AND discount_value IS NULL AND bundle_price IS NULL)
                )
            ");
        } catch (\Throwable $e) {
            // Jika CHECK tak didukung, pastikan validasi di level aplikasi.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
