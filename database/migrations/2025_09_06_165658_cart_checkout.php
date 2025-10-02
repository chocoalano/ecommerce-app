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
         * Keranjang belanja, bisa dimiliki user login maupun guest (pakai session_id).
         */
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key keranjang');
            $table->foreignId('user_id')
                ->nullable()->constrained('users')
                ->nullOnDelete()->cascadeOnUpdate()
                ->comment('FK opsional ke user pemilik keranjang; null jika guest');
            $table->string('session_id', 100)->nullable()->comment('ID sesi untuk keranjang guest (tanpa login)');
            $table->string('currency', 3)->nullable()->comment('Kode mata uang (mis. IDR)');
            $table->decimal('subtotal_amount', 18, 2)->default(0)->comment('Total harga item sebelum diskon/ongkir/pajak');
            $table->decimal('discount_amount', 18, 2)->default(0)->comment('Total diskon yang diterapkan');
            $table->decimal('shipping_amount', 18, 2)->default(0)->comment('Biaya pengiriman');
            $table->decimal('tax_amount', 18, 2)->default(0)->comment('Total pajak');
            $table->decimal('grand_total', 18, 2)->default(0)->comment('Total akhir yang harus dibayar');
            $table->json('applied_promos')->nullable()->comment('Daftar promo/voucher yang diterapkan (format JSON)');
            $table->timestamps();

            $table->comment('Master keranjang belanja untuk user/guest');
        });

        /**
         * Tabel: cart_items
         * Item dalam keranjang, detail per variant produk.
         */
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key item keranjang');
            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke carts.id (keranjang induk)');
            $table->foreignId('variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke product_variants.id (produk yang dipilih)');
            $table->integer('qty')->default(1)->comment('Jumlah unit produk dalam keranjang');
            $table->decimal('unit_price', 18, 2)->comment('Harga per unit variant saat ditambahkan');
            $table->decimal('row_total', 18, 2)->comment('Subtotal untuk item ini (qty x unit_price - diskon item)');
            $table->json('meta_json')->nullable()->comment('Informasi tambahan (mis. breakdown promo/bundle/gift)');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu item ditambahkan ke keranjang');

            $table->unique(['cart_id','variant_id'], 'cart_items_cart_variant_unique');
            $table->comment('Detail item produk dalam keranjang belanja');
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
