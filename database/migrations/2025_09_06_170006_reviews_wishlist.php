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
         * Tabel: product_reviews
         * Ulasan/review produk oleh pengguna, berisi rating dan komentar.
         * Disesuaikan: tanpa variant; opsi taut ke order_items untuk verifikasi pembelian.
         */
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key review produk');

            $table->foreignId('customer_id')->nullable()
                ->constrained('customers')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('User yang memberikan review (null jika anonim)');

            $table->foreignId('product_id')->nullable()
                ->constrained('products')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('Produk yang direview');

            // Opsional: tandai sebagai verified purchase dengan merujuk item pada order
            $table->foreignId('order_item_id')->nullable()
                ->constrained('order_items')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('Order item terkait (jika review setelah pembelian)');

            $table->unsignedTinyInteger('rating')->comment('Rating produk (1..5 bintang)');
            $table->string('title', 255)->nullable()->comment('Judul singkat review');
            $table->longText('comment')->nullable()->comment('Isi review/komentar user');

            $table->boolean('is_approved')->default(false)->index()->comment('Status moderasi review');
            $table->boolean('is_verified_purchase')->virtualAs('order_item_id IS NOT NULL')->comment('Diturunkan dari order_item_id');

            $table->timestamps();

            // Satu user satu review per produk (anonim / null customer_id dibiarkan multiple oleh DB karena NULL tidak unik)
            $table->unique(['product_id','customer_id'], 'reviews_product_user_unique');

            $table->index(['product_id','is_approved','rating'], 'reviews_query_idx');
            $table->comment('Review & rating produk oleh user (tanpa variant)');
        });

        /**
         * Tabel: wishlists
         * Daftar keinginan milik user (tanpa variant).
         */
        Schema::create('wishlists', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key wishlist');

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('User pemilik wishlist');

            $table->string('name', 100)->default('Default')->comment('Nama wishlist (mendukung multi-wishlist)');
            $table->timestamps();

            $table->unique(['customer_id','name'], 'wishlists_user_name_unique');
            $table->comment('Wishlist milik user yang berisi produk favorit');
        });

        /**
         * Tabel: wishlist_items
         * Item produk yang ditambahkan user ke wishlist (tanpa variant).
         */
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key item wishlist');

            $table->foreignId('wishlist_id')
                ->constrained('wishlists')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke wishlist induk');

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('Produk yang ditambahkan ke wishlist');

            // Snapshot ringan opsional
            $table->string('product_name', 255)->nullable()->comment('Snapshot nama produk saat ditambahkan');
            $table->string('product_sku', 100)->nullable()->comment('Snapshot SKU saat ditambahkan');

            $table->timestamps();

            $table->unique(['wishlist_id','product_id'], 'wishlist_unique_item');

            $table->index(['wishlist_id']);
            $table->comment('Detail produk dalam wishlist user (tanpa variant)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_reviews');
    }
};
