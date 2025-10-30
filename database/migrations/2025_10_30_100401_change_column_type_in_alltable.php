<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan ini diimpor

return new class extends Migration
{
    /**
     * Daftar tabel dan kolom Foreign Key (FK) untuk diubah.
     * Diperbarui berdasarkan skema database yang disediakan.
     */
    private $tables = [
        // Nama tabel => [kolom foreign key]
        'customer_addresses' => ['customer_id'],
        // PERBAIKAN: Kolom 'media_id' telah dihapus karena tidak ditemukan di tabel 'product_media' (Error 1054).
        'product_media' => ['product_id'],
        'product_categories' => ['product_id', 'category_id'],
        'stock_movements' => ['product_id'], // Diubah sesuai permintaan
        'inventories' => ['product_id', 'location_id'], // location_id merujuk ke inventory_locations
        'promotion_products' => ['promotion_id', 'product_id'],
        'cart_items' => ['cart_id', 'product_id'],
        'refunds' => ['order_id'], // FK 'payment_id' diabaikan di sini, tetapi ditangani oleh workaround eksplisit
        'return_items' => ['return_id', 'order_item_id'],
        'returns' => ['order_id'],
        'shipment_items' => ['shipment_id', 'order_item_id'],
        'shipments' => ['order_id', 'courier_id'],
        'payment_transactions' => ['payment_id'],
        'payments' => ['order_id'], // Diubah sesuai permintaan
        'order_items' => ['order_id', 'product_id'],
        'orders' => ['customer_id', 'shipping_address_id', 'billing_address_id'],
        'wishlist_items' => ['wishlist_id', 'product_id'],
        'wishlists' => ['customer_id'],
        'product_reviews' => ['product_id', 'order_item_id', 'customer_id'],
        'page_contents' => ['page_id'],
        'footer_payments' => [], // Diubah sesuai permintaan
        'articles' => [], // Tidak ada FK di dump yang tersedia
        'article_contents' => ['article_id'],
        'customer_networks' => ['ancestor_id', 'descendant_id'],
        'categories' => [], // Diubah sesuai permintaan
        'carts' => ['customer_id'],
        'customers' => ['parent_id', 'sponsor_id'],
        'event_logs' => ['user_id'], // Diambil dari dump event_logs
    ];

    /**
     * Daftar tabel yang memiliki Primary Key 'id' (BIGINT UNSIGNED) untuk diubah.
     * Tabel 'couriers' dikecualikan karena menggunakan SMALLINT UNSIGNED.
     */
    private $pkTables = [
        // 'customers', // PERBAIKAN: Dihapus karena PK ini mungkin memiliki nilai > 4.2M, menyebabkan truncation/error.
        'products', 'categories', 'inventory_locations', 'promotions',
        'carts', 'payment_methods', 'product_reviews', 'pages',
        'footers', 'newsletter_subscribers', 'articles', 'returns', 'shipments',
        'payments', 'payment_transactions', 'order_items', 'orders', 'wishlists',
        'customer_addresses', 'stock_movements', 'inventories', 'refunds',
        'article_contents', 'customer_networks', 'footer_payments',
        'event_logs', 'failed_jobs', 'footer_guarantees', 'footer_links', 'footer_socials', 'jobs', 'wishlist_items', 'users'
    ];

    /**
     * Mapping untuk Foreign Key yang merujuk ke tabel non-plural (misal: 'media_id' -> 'media', bukan 'medias').
     */
    private $singularReferenceMap = [
        'media_id' => 'media',
    ];

    /**
     * Daftar kolom FK yang kemungkinan besar bersifat nullable.
     */
    private $nullableFks = [
        'parent_id', 'sponsor_id', 'location_id', 'shipping_address_id', 'billing_address_id', 'courier_id',
        'ancestor_id', 'descendant_id', 'user_id', 'category_id',
        // 'inventory_id' dihapus karena tidak lagi menjadi FK di 'stock_movements'
    ];

    /**
     * Run the migrations (Up).
     */
    public function up(): void
    {
        // PENTING: Nonaktifkan pemeriksaan Foreign Key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // WORKAROUND FIX: Menghapus secara eksplisit FK yang sering bermasalah saat PK diubah

        // Constraint 1: FK stock_movements.location_id
        try {
            DB::statement("ALTER TABLE `stock_movements` DROP FOREIGN KEY `stock_movements_location_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }

        // Constraint 2: FK inventories.location_id
        try {
            DB::statement("ALTER TABLE `inventories` DROP FOREIGN KEY `inventories_location_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }

        // Constraint 3: FK payments.method_id
        try {
            DB::statement("ALTER TABLE `payments` DROP FOREIGN KEY `payments_method_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }

        // Constraint 4: FK refunds.payment_id
        try {
            DB::statement("ALTER TABLE `refunds` DROP FOREIGN KEY `refunds_payment_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }

        // Constraint 5: FK user_roles.user_id
        try {
            DB::statement("ALTER TABLE `user_roles` DROP FOREIGN KEY `user_roles_user_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }

        // 1. Drop Foreign Key Constraints (menggunakan Raw SQL untuk menghindari masalah penamaan)
        foreach ($this->tables as $tableName => $foreignKeys) {
            foreach ($foreignKeys as $fk) {
                // Opsi A: Nama konvensional Laravel
                $defaultConstraintName = "{$tableName}_{$fk}_foreign";
                // Opsi B: Nama constraint yang sama dengan nama kolom
                $columnConstraintName = $fk;

                try {
                    // Coba hapus menggunakan nama konvensional Laravel
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$defaultConstraintName}`");
                } catch (\Exception $e) {
                    try {
                        // Coba hapus menggunakan nama kolom (FK)
                        DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$columnConstraintName}`");
                    } catch (\Exception $e2) {
                        // Abaikan jika constraint memang tidak ada.
                    }
                }
            }
        }

        // 2. Ubah tipe data kolom FK ke unsigned integer (32-bit)
        foreach ($this->tables as $tableName => $foreignKeys) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {

                    // Tentukan tipe data
                    $typeMethod = 'unsignedInteger';
                    if ($fk === 'courier_id') {
                        // courier_id merujuk ke couriers.id (SMALLINT UNSIGNED)
                        $typeMethod = 'unsignedSmallInteger';
                    } else if ($fk === 'customer_id') {
                        // PERBAIKAN TRUNCATION: customer_id dipertahankan sebagai 64-bit (BigInteger)
                        $typeMethod = 'unsignedBigInteger';
                    }

                    if (in_array($fk, $this->nullableFks)) {
                        // FIX TRUNCATED DATA: Panggil nullable() sebelum change() secara eksplisit
                        $table->{$typeMethod}($fk)->nullable()->change();
                    } else {
                        // Jika tidak nullable, panggil change()
                        $table->{$typeMethod}($fk)->change();
                    }
                }
            });
        }

        // 3. Ubah Primary Keys ke unsignedInteger (32-bit)
        foreach ($this->pkTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Mengubah tipe PK.
                $table->unsignedInteger('id')->change();
            });
        }

        // 4. Tambahkan kembali Foreign Key Constraints
        foreach ($this->tables as $tableName => $foreignKeys) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    $refTable = str_replace('_id', '', $fk);

                    // Cek mapping khusus, jika tidak ada, gunakan konvensi plural ('s')
                    $targetTable = $this->singularReferenceMap[$fk] ?? ($refTable . 's');

                    // Tambahkan Foreign Key Constraint baru
                    $foreign = $table->foreign($fk)
                          ->references('id')->on($targetTable)
                          ->onDelete('cascade'); // Gunakan onDelete/onUpdate sesuai kebutuhan skema Anda

                    // PERBAIKAN NULLABLE: Tambahkan nullable() pada FK saat pembuatan constraint
                    if (in_array($fk, $this->nullableFks)) {
                        $foreign->nullable(true); // Memastikan constraint dibuat sebagai nullable FK
                    }
                }
            });
        }

        // PENTING: Aktifkan kembali pemeriksaan Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations (Rollback - Mengembalikan ke bigInteger).
     */
    public function down(): void
    {
        // PENTING: Nonaktifkan pemeriksaan Foreign Key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // WORKAROUND FIX: Drop constraint yang bermasalah juga saat rollback
        try {
            DB::statement("ALTER TABLE `stock_movements` DROP FOREIGN KEY `stock_movements_location_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan jika constraint tidak ada
        }
        try {
            DB::statement("ALTER TABLE `inventories` DROP FOREIGN KEY `inventories_location_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan
        }
        try {
            DB::statement("ALTER TABLE `payments` DROP FOREIGN KEY `payments_method_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan
        }
        try {
            DB::statement("ALTER TABLE `refunds` DROP FOREIGN KEY `refunds_payment_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan
        }
        try {
            DB::statement("ALTER TABLE `user_roles` DROP FOREIGN KEY `user_roles_user_id_foreign`");
        } catch (\Exception $e) {
            // Abaikan
        }

        // 1. Hapus Foreign Key Constraints (menggunakan Raw SQL)
        foreach ($this->tables as $tableName => $foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $defaultConstraintName = "{$tableName}_{$fk}_foreign";
                $columnConstraintName = $fk;

                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$defaultConstraintName}`");
                } catch (\Exception $e) {
                    try {
                        DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$columnConstraintName}`");
                    } catch (\Exception $e2) {
                        // Abaikan
                    }
                }
            }
        }

        // 2. Ubah Primary Keys kembali ke bigIncrements
        // Tabel 'customers' harus di-handle secara eksplisit di sini karena dikeluarkan dari $pkTables di up()
        $rollbackPkTables = array_merge($this->pkTables, ['customers']);

        foreach ($rollbackPkTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // bigIncrements akan mengembalikan ke BIGINT UNSIGNED AUTO_INCREMENT
                $table->bigIncrements('id')->change();
            });
        }

        // 3. Ubah Foreign Keys kembali ke unsignedBigInteger
        foreach ($this->tables as $tableName => $foreignKeys) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    // Rollback semua FK ke unsignedBigInteger (tipe aslinya)
                    if (in_array($fk, $this->nullableFks)) {
                         // Tambahkan nullable() saat rollback jika kolom bersifat nullable
                        $table->unsignedBigInteger($fk)->nullable()->change();
                    } else {
                        $table->unsignedBigInteger($fk)->change();
                    }
                }
            });
        }

        // 4. Tambahkan kembali Foreign Key Constraints
        foreach ($this->tables as $tableName => $foreignKeys) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    $refTable = str_replace('_id', '', $fk);
                    $targetTable = $this->singularReferenceMap[$fk] ?? ($refTable . 's');

                    $foreign = $table->foreign($fk)
                          ->references('id')->on($targetTable)
                          ->onDelete('cascade');

                    // Tambahkan nullable() pada FK saat pembuatan constraint
                    if (in_array($fk, $this->nullableFks)) {
                        $foreign->nullable(true);
                    }
                }
            });
        }

        // PENTING: Aktifkan kembali pemeriksaan Foreign Key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
