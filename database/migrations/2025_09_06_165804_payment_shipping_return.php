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
        // Enum values
        $orderEnum   = ['PENDING','PAID','PROCESSING','SHIPPED','COMPLETED','CANCELED','REFUNDED','PARTIAL_REFUND'];
        $payEnum     = ['INITIATED','AUTHORIZED','CAPTURED','FAILED','CANCELED','REFUNDED','PARTIAL_REFUND'];
        $shipEnum    = ['READY_TO_SHIP','IN_TRANSIT','DELIVERED','FAILED','RETURNED'];
        $returnEnum  = ['REQUESTED','APPROVED','REJECTED','RECEIVED','REFUNDED'];

        /**
         * Tabel: orders
         * Ringkasan pesanan dari checkout hingga selesai/refund.
         */
        Schema::create('orders', function (Blueprint $table) use ($orderEnum) {
            $table->bigIncrements('id')->comment('Primary key order');
            $table->string('order_no', 50)->unique()->comment('Nomor order unik yang ditampilkan ke pengguna');
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Pemilik order (null jika guest checkout)');
            $table->string('currency', 3)->nullable()->comment('Kode mata uang (mis. IDR)');
            $table->enum('status', $orderEnum)->default('PENDING')->comment('Status order terkini');
            $table->decimal('subtotal_amount', 18, 2)->default(0)->comment('Subtotal item sebelum diskon/ongkir/pajak');
            $table->decimal('discount_amount', 18, 2)->default(0)->comment('Total diskon yang diterapkan pada order');
            $table->decimal('shipping_amount', 18, 2)->default(0)->comment('Biaya pengiriman');
            $table->decimal('tax_amount', 18, 2)->default(0)->comment('Total pajak');
            $table->decimal('grand_total', 18, 2)->default(0)->comment('Total akhir yang harus dibayar');
            $table->foreignId('shipping_address_id')->nullable()
                ->constrained('addresses')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Alamat pengiriman yang digunakan');
            $table->foreignId('billing_address_id')->nullable()
                ->constrained('addresses')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Alamat penagihan yang digunakan');
            $table->json('applied_promos')->nullable()->comment('Daftar promo/voucher yang diterapkan (JSON)');
            $table->text('notes')->nullable()->comment('Catatan tambahan dari pengguna/CS');
            $table->timestamp('placed_at')->useCurrent()->comment('Waktu order dibuat/checkout selesai');
            $table->timestamp('updated_at')->useCurrent()->comment('Waktu terakhir pembaruan order');

            $table->comment('Ringkasan pesanan pelanggan');
        });

        /**
         * Tabel: order_items
         * Item barang/jasa yang termasuk dalam sebuah order.
         */
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key order item');
            $table->foreignId('order_id')
                ->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke orders.id');
            $table->foreignId('product_id')
                ->constrained('products')->cascadeOnUpdate()
                ->comment('Produk dasar yang dipesan');
            $table->foreignId('variant_id')->nullable()
                ->constrained('product_variants')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Variant produk, jika ada (warna/ukuran, dll)');
            $table->string('name', 255)->nullable()->comment('Nama item saat checkout (snapshot)');
            $table->string('sku', 120)->nullable()->comment('SKU item saat checkout (snapshot)');
            $table->integer('qty')->comment('Kuantitas item pada order');
            $table->decimal('unit_price', 18, 2)->comment('Harga per unit saat checkout');
            $table->decimal('discount_amount', 18, 2)->default(0)->comment('Total diskon yang menempel pada item ini');
            $table->decimal('row_total', 18, 2)->comment('Subtotal baris (qty x unit_price - diskon item)');
            $table->json('meta_json')->nullable()->comment('Metadata tambahan (mis. rincian promo per item, bundle)');

            $table->comment('Detail item yang dipesan pada suatu order');
        });

        /**
         * Tabel: payment_methods
         * Master metode pembayaran yang tersedia di sistem.
         */
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('Primary key payment method');
            $table->string('code', 50)->unique()->comment('Kode metode bayar (CC, VA, QRIS, E-WALLET, COD)');
            $table->string('name', 100)->nullable()->comment('Nama tampilan metode bayar');
            $table->boolean('is_active')->default(true)->comment('Status aktif metode bayar');

            $table->comment('Daftar metode pembayaran yang didukung');
        });

        /**
         * Tabel: payments
         * Transaksi pembayaran untuk sebuah order (bisa lebih dari satu).
         */
        Schema::create('payments', function (Blueprint $table) use ($payEnum) {
            $table->bigIncrements('id')->comment('Primary key pembayaran');
            $table->foreignId('order_id')
                ->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke orders.id yang dibayar');
            $table->unsignedSmallInteger('method_id')->comment('FK ke payment_methods.id');
            $table->enum('status', $payEnum)->default('INITIATED')->comment('Status pembayaran terkini');
            $table->decimal('amount', 18, 2)->comment('Jumlah yang dibayar (gross/charged)');
            $table->string('currency', 3)->nullable()->comment('Mata uang pembayaran (mis. IDR)');
            $table->string('provider_txn_id', 100)->nullable()->comment('ID transaksi pada provider gateway');
            $table->json('metadata_json')->nullable()->comment('Payload/response gateway (JSON)');
            $table->timestamps();

            $table->foreign('method_id')->references('id')->on('payment_methods')->cascadeOnUpdate();

            $table->comment('Data pembayaran yang terkait dengan suatu order');
        });

        /**
         * Tabel: payment_transactions
         * Riwayat tiap event/status pada sebuah payment (log granular).
         */
        Schema::create('payment_transactions', function (Blueprint $table) use ($payEnum) {
            $table->bigIncrements('id')->comment('Primary key log transaksi pembayaran');
            $table->foreignId('payment_id')
                ->constrained('payments')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke payments.id');
            $table->enum('status', $payEnum)->comment('Status pada event ini (AUTHORIZED, CAPTURED, dll)');
            $table->decimal('amount', 18, 2)->comment('Jumlah pada event ini (jika relevan)');
            $table->json('raw_json')->nullable()->comment('Payload mentah dari gateway (JSON)');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu event terjadi');

            $table->comment('Log historis status/payment events');
        });

        /**
         * Tabel: couriers
         * Master jasa kurir/ekspedisi.
         */
        Schema::create('couriers', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('Primary key kurir');
            $table->string('code', 50)->unique()->comment('Kode kurir (JNE, SiCepat, dsb.)');
            $table->string('name', 100)->nullable()->comment('Nama kurir');
            $table->boolean('is_active')->default(true)->comment('Status aktif kurir');

            $table->comment('Master data kurir/ekspedisi');
        });

        /**
         * Tabel: shipments
         * Informasi pengiriman untuk sebuah order.
         */
        Schema::create('shipments', function (Blueprint $table) use ($shipEnum) {
            $table->bigIncrements('id')->comment('Primary key shipment');
            $table->foreignId('order_id')
                ->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke orders.id');
            $table->unsignedSmallInteger('courier_id')->nullable()->comment('FK ke couriers.id');
            $table->string('tracking_no', 100)->nullable()->comment('Nomor resi/penelusuran pengiriman');
            $table->enum('status', $shipEnum)->default('READY_TO_SHIP')->comment('Status pengiriman');
            $table->timestamp('shipped_at')->nullable()->comment('Waktu barang dikirim');
            $table->timestamp('delivered_at')->nullable()->comment('Waktu barang diterima');
            $table->decimal('shipping_fee', 18, 2)->default(0)->comment('Biaya pengiriman yang dikenakan');
            $table->timestamps();

            $table->foreign('courier_id')->references('id')->on('couriers')->nullOnDelete()->cascadeOnUpdate();

            $table->comment('Data pengiriman (shipment) terkait order');
        });

        /**
         * Tabel: shipment_items
         * Detil item yang termasuk dalam satu shipment.
         */
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key shipment item');
            $table->foreignId('shipment_id')
                ->constrained('shipments')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke shipments.id');
            $table->foreignId('order_item_id')
                ->constrained('order_items')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke order_items.id');
            $table->integer('qty')->comment('Jumlah unit item yang dikirim dalam shipment ini');

            $table->comment('Item-item yang dikirim pada sebuah shipment');
        });

        /**
         * Tabel: returns
         * Permintaan retur/penukaran barang dari pelanggan.
         */
        Schema::create('returns', function (Blueprint $table) use ($returnEnum) {
            $table->bigIncrements('id')->comment('Primary key retur');
            $table->foreignId('order_id')
                ->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('Order yang diretur');
            $table->enum('status', $returnEnum)->default('REQUESTED')->comment('Status pengajuan retur');
            $table->string('reason', 255)->nullable()->comment('Alasan retur dari pelanggan/CS');
            $table->timestamp('requested_at')->useCurrent()->comment('Waktu retur diajukan');
            $table->timestamp('processed_at')->nullable()->comment('Waktu retur diproses/diapprove/ditolak');

            $table->comment('Pengajuan retur untuk item pada order');
        });

        /**
         * Tabel: return_items
         * Item mana saja pada order yang diretur beserta jumlahnya.
         */
        Schema::create('return_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key retur item');
            $table->foreignId('return_id')
                ->constrained('returns')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke returns.id');
            $table->foreignId('order_item_id')
                ->constrained('order_items')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('FK ke order_items.id yang diretur');
            $table->integer('qty')->comment('Jumlah unit yang diretur');
            $table->string('condition_note', 255)->nullable()->comment('Catatan kondisi barang (opsional)');

            $table->comment('Detail item yang termasuk dalam pengajuan retur');
        });

        /**
         * Tabel: refunds
         * Pengembalian dana (penuh/parsial) yang terkait order/pembayaran.
         */
        Schema::create('refunds', function (Blueprint $table) use ($payEnum) {
            $table->bigIncrements('id')->comment('Primary key refund');
            $table->foreignId('order_id')
                ->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete()
                ->comment('Order yang direfund');
            $table->foreignId('payment_id')->nullable()
                ->constrained('payments')->nullOnDelete()->cascadeOnUpdate()
                ->comment('Pembayaran yang direfund (opsional, jika diketahui)');
            $table->enum('status', $payEnum)->default('REFUNDED')->comment('Status refund (REFUNDED/PARTIAL_REFUND/dll)');
            $table->decimal('amount', 18, 2)->comment('Jumlah dana yang dikembalikan');
            $table->string('reason', 255)->nullable()->comment('Alasan/ref catatan refund');
            $table->timestamp('created_at')->useCurrent()->comment('Waktu refund dibuat');

            $table->comment('Catatan pengembalian dana untuk suatu order/pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan drop untuk menghindari konflik FK
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
