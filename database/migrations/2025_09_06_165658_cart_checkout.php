<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Tabel: carts
         * Keranjang belanja untuk user/guest.
         */
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key keranjang');

            // Pemilik keranjang: user atau guest (session)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('FK opsional ke user pemilik keranjang; null jika guest');
            $table->string('session_id', 100)->nullable()->index()->comment('ID sesi untuk keranjang guest (tanpa login)');

            // Ringkasan nilai (cache)
            $table->char('currency', 3)->default('IDR')->comment('Kode mata uang (mis. IDR)');
            $table->decimal('subtotal_amount', 18, 2)->default(0)->comment('Total harga item sebelum diskon/ongkir/pajak');
            $table->decimal('discount_amount', 18, 2)->default(0)->comment('Total diskon yang diterapkan');
            $table->decimal('shipping_amount', 18, 2)->default(0)->comment('Biaya pengiriman');
            $table->decimal('tax_amount', 18, 2)->default(0)->comment('Total pajak');
            $table->decimal('grand_total', 18, 2)->default(0)->comment('Total akhir yang harus dibayar');

            // Promo yang diaplikasikan pada level cart
            // Kolom promo yang lebih efektif: relasi ke tabel promos dan voucher
            $table->foreignId('promo_id')
                ->nullable()
                ->constrained('promos')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('FK opsional ke promo yang diterapkan pada cart');
            $table->foreignId('voucher_id')
                ->nullable()
                ->constrained('vouchers')
                ->nullOnDelete()
                ->cascadeOnUpdate()
                ->comment('FK opsional ke voucher yang diterapkan pada cart');

            $table->timestamps();

            $table->comment('Master keranjang belanja untuk user/guest');
        });

        /**
         * Tabel: cart_items
         * Item dalam keranjang, detail per produk (tanpa variant).
         */
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key item keranjang');

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('FK ke carts.id (keranjang induk)');

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('FK ke products.id (produk yang dipilih)');

            // Kuantitas & harga snapshot saat add-to-cart
            $table->unsignedInteger('qty')->default(1)->comment('Jumlah unit produk dalam keranjang');

            // Snapshot harga & info produk agar stabil saat checkout
            $table->decimal('unit_price', 18, 2)->comment('Harga per unit produk saat ditambahkan (sebelum diskon item)');
            $table->char('currency', 3)->default('IDR')->comment('Mata uang pada saat penambahan');
            $table->string('product_sku', 100)->nullable()->comment('SKU snapshot');
            $table->string('product_name', 255)->nullable()->comment('Nama produk snapshot');

            // Nilai total baris setelah diskon item (bukan grand total cart)
            $table->decimal('row_total', 18, 2)->comment('Subtotal untuk item ini (qty x unit_price - diskon item)');

            // Meta: breakdown promo/bundle/gift, catatan, dll
            // Kolom meta yang lebih efektif tanpa tipe JSON.
            // Simpan breakdown promo/bundle/gift sebagai string terstruktur (misal: kode promo, nama bundle, dst).
            $table->string('promo_code', 100)->nullable()->comment('Kode promo yang diterapkan pada item');
            $table->string('bundle_name', 100)->nullable()->comment('Nama bundle/gift yang terkait dengan item');
            $table->string('note', 255)->nullable()->comment('Catatan khusus untuk item ini');
            // Tambahkan kolom lain sesuai kebutuhan bisnis.

            $table->timestamps();

            // Gabungkan item yang sama (opsional: lihat catatan di bawah)
            $table->unique(['cart_id', 'product_id'], 'cart_items_cart_product_unique');

            // Indeks umum
            $table->index(['cart_id']);
            $table->comment('Detail item produk dalam keranjang belanja (tanpa variant)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
