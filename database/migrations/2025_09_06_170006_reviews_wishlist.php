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
         */
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key review produk');
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete()->cascadeOnUpdate()
                ->comment('User yang memberikan review (null jika anonim)');
            $table->foreignId('product_id')->nullable()
                ->constrained('products')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Produk yang direview');
            $table->unsignedTinyInteger('rating')->comment('Rating produk (1..5 bintang)');
            $table->string('title', 255)->nullable()->comment('Judul singkat review');
            $table->longText('comment')->nullable()->comment('Isi review/komentar user');
            $table->boolean('is_approved')->default(false)->comment('Status moderasi review (harus di-approve)');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu review dibuat');

            $table->unique(['product_id','user_id'], 'reviews_product_user_unique');
            $table->comment('Review & rating produk oleh user');
        });

        /**
         * Tabel: wishlists
         * Daftar keinginan milik user (bisa berisi beberapa produk/variant).
         */
        Schema::create('wishlists', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key wishlist');
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('User pemilik wishlist');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu wishlist dibuat');

            $table->comment('Wishlist milik user yang berisi produk favorit');
        });

        /**
         * Tabel: wishlist_items
         * Item produk/variant yang ditambahkan user ke wishlist.
         */
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key item wishlist');
            $table->foreignId('wishlist_id')
                ->constrained('wishlists')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke wishlist induk');
            $table->foreignId('product_id')
                ->constrained('products')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('Produk yang ditambahkan ke wishlist');
            $table->foreignId('variant_id')->nullable()
                ->constrained('product_variants')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Variant produk spesifik, opsional');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu item ditambahkan ke wishlist');

            $table->unique(['wishlist_id','product_id','variant_id'], 'wishlist_unique_item');
            $table->comment('Detail produk/variant dalam wishlist user');
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
