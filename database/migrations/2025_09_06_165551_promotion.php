<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->enum('type', $promoEnum)->comment('Jenis promo (percent/fixed/cashback/bundle/gift/installment/payment/flash/trade-in)');
            $table->string('landing_slug', 255)->nullable()->comment('Slug halaman landing/offer (opsional)');
            $table->longText('description')->nullable()->comment('Deskripsi/ketentuan promo (rich text)');
            $table->timestamp('start_at')->comment('Waktu mulai promo (UTC/DB time)');
            $table->timestamp('end_at')->comment('Waktu akhir promo');
            $table->boolean('is_active')->default(true)->comment('Status aktif promo');
            $table->integer('priority')->default(100)->comment('Prioritas aplikasi promo (angka kecil = lebih prioritas)');
            $table->integer('max_redemption')->nullable()->comment('Kuota global pemakaian promo');
            $table->integer('per_user_limit')->nullable()->comment('Batas penggunaan per user');
            $table->json('conditions_json')->nullable()->comment('Syarat & filter granular (min spend, channel, bank, whitelists, dll) dalam JSON');
            $table->enum('show_on', ['HERO', 'BANNER'])->default('HERO')->comment('Tampilkan pada section HERO/BANNER');
            $table->enum('page', ['beranda'])->default('beranda')->comment('Tampilkan pada halaman tertentu');
            $table->timestamps();

            $table->comment('Master promosi: berisi definisi, periode, dan rule global');
        });

        /**
         * Tabel: promotion_products
         * Scoping promo ke produk/variant tertentu + nilai diskon/bundling.
         */
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key mapping promo-produk');
            $table->foreignId('promotion_id')
                ->constrained('promotions')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke promotions.id');
            $table->foreignId('product_id')
                ->nullable()->constrained('products')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Opsional: FK ke products.id (jika level produk)');
            $table->foreignId('variant_id')
                ->nullable()->constrained('product_variants')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Opsional: FK ke product_variants.id (level variant)');
            $table->integer('min_qty')->default(1)->comment('Minimal qty untuk eligible promo');
            $table->decimal('discount_value', 18, 2)->nullable()->comment('Diskon nominal (untuk FIXED_DISCOUNT)');
            $table->decimal('discount_percent', 5, 2)->nullable()->comment('Diskon persen (untuk PERCENT_DISCOUNT)');
            $table->decimal('bundle_price', 18, 2)->nullable()->comment('Harga bundling (untuk BUNDLE_PRICE)');

            $table->comment('Cakupan produk/variant yang terkena promo beserta nilai diskonnya');
        });

        /**
         * Tabel: promotion_gifts
         * Definisi hadiah pembelian (GWP) berdasarkan syarat minimal belanja/qty.
         */
        Schema::create('promotion_gifts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key hadiah promo');
            $table->foreignId('promotion_id')
                ->constrained('promotions')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke promotions.id');
            $table->foreignId('gift_variant_id')
                ->constrained('product_variants')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('Variant produk yang diberikan sebagai hadiah');
            $table->decimal('min_spend', 18, 2)->default(0)->comment('Minimal belanja untuk mendapatkan hadiah');
            $table->integer('min_qty')->default(0)->comment('Minimal kuantitas item tertentu (opsional)');

            $table->comment('Konfigurasi hadiah pembelian untuk sebuah promo');
        });

        /**
         * Tabel: vouchers
         * Kode voucher yang bisa dikombinasikan dengan promo (opsional).
         */
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key voucher');
            $table->foreignId('promotion_id')
                ->nullable()->constrained('promotions')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Opsional: FK ke promotions.id jika voucher terikat ke promo tertentu');
            $table->string('code', 100)->unique()->comment('Kode voucher unik');
            $table->boolean('is_stackable')->default(false)->comment('True jika bisa ditumpuk dengan promo lain');
            $table->timestamp('start_at')->nullable()->comment('Mulai berlaku');
            $table->timestamp('end_at')->nullable()->comment('Berakhir');
            $table->integer('max_redemption')->nullable()->comment('Kuota maksimal pemakaian semua user');
            $table->integer('per_user_limit')->nullable()->comment('Batas penggunaan per user');
            $table->json('conditions_json')->nullable()->comment('Syarat granular dalam JSON (min spend, channel, dll)');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu dibuat');

            $table->comment('Master kode voucher yang dapat diredeem saat checkout');
        });

        /**
         * Tabel: voucher_redemptions
         * Log penebusan voucher per user/order.
         */
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key redeem voucher');
            $table->foreignId('voucher_id')
                ->constrained('vouchers')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke vouchers.id');

            // user_id boleh dibiarkan constrained jika tabel users pasti ada.
            // Jika tidak yakin, gunakan pola seperti order_id di bawah.
            $table->foreignId('user_id')
                ->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate()
                ->comment('User yang menebus (null jika guest)');

            // NOTE: jangan langsung constrained ke orders agar tidak gagal jika orders belum ada
            $table->unsignedBigInteger('order_id')->nullable()->index()
                ->comment('Order terkait penebusan (nullable)');

            $table->timestamp('redeemed_at')->useCurrent()->comment('Waktu penebusan voucher');

            $table->index(['voucher_id', 'user_id'], 'voucher_redemptions_voucher_user_idx');
            $table->comment('Catatan penebusan voucher oleh user terhadap order tertentu');
        });

        // Tambahkan FK order_id hanya jika tabel orders sudah ada
        if (Schema::hasTable('orders')) {
            Schema::table('voucher_redemptions', function (Blueprint $table) {
                $table->foreign('order_id')
                    ->references('id')->on('orders')
                    ->nullOnDelete()->cascadeOnUpdate();
            });
        }

        /**
         * Tabel: bank_installments
         * Ketentuan cicilan bank untuk suatu promo.
         */
        Schema::create('bank_installments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key cicilan bank');
            $table->foreignId('promotion_id')
                ->constrained('promotions')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke promotions.id');
            $table->string('bank_code', 50)->comment('Kode bank (BCA/BNI/Mandiri/dll)');
            $table->integer('tenor_months')->comment('Tenor cicilan (bulan), mis. 3/6/12');
            $table->decimal('interest_rate_pa', 7, 4)->nullable()->comment('Suku bunga efektif per tahun');
            $table->decimal('admin_fee', 18, 2)->default(0)->comment('Biaya admin tambahan');
            $table->decimal('min_spend', 18, 2)->default(0)->comment('Minimal transaksi agar eligible cicilan');

            $table->comment('Konfigurasi opsi cicilan bank untuk promo terkait');
        });

        /**
         * Tabel: trade_in_programs
         * Aturan & partner untuk program tukar tambah (trade-in) dalam promo.
         */
        Schema::create('trade_in_programs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key program trade-in');
            $table->foreignId('promotion_id')
                ->constrained('promotions')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke promotions.id');
            $table->json('terms_json')->nullable()->comment('Syarat & ketentuan trade-in (brand/model yang diterima, kondisi, dsb.)');
            $table->string('partner_name', 255)->nullable()->comment('Nama partner pihak ketiga (jika ada)');

            $table->comment('Definisi program trade-in yang terhubung dengan promo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutkan drop untuk menghindari konflik FK
        Schema::dropIfExists('trade_in_programs');
        Schema::dropIfExists('bank_installments');
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('promotion_gifts');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
