<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Skema dasar akun & otentikasi
 *
 * Mencakup:
 * - users: data akun pengguna
 * - roles: daftar peran/otorisasi
 * - user_roles: pivot antara users & roles
 * - addresses: alamat pengiriman/penagihan pengguna
 * - password_reset_tokens: token reset password bawaan Laravel
 * - sessions: sesi login (jika menggunakan database session driver)
 *
 * Catatan:
 * - Seluruh tabel menggunakan InnoDB & utf8mb4_unicode_ci.
 * - Kolom penting diberi comment() agar mudah dipahami.
 * - Urutan drop di down() memastikan tidak terjadi pelanggaran FK.
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        /**
         * Tabel: users
         * Menyimpan profil dasar & kredensial pengguna.
         */
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Primary key pengguna (auto increment)');
            $table->string('name')
                  ->comment('Nama tampilan pengguna (umumnya nama depan/lengkap singkat)');
            $table->string('email')->unique()
                  ->comment('Email unik untuk login & korespodensi');
            $table->timestamp('email_verified_at')->nullable()
                  ->comment('Waktu verifikasi email, null jika belum terverifikasi');
            $table->string('password')
                  ->comment('Hash password (bcrypt/argon)');
            $table->rememberToken()
                  ->comment('Token remember me untuk sesi persisten');
            $table->string('phone', 30)->unique()->nullable()
                  ->comment('Nomor telepon unik (opsional) untuk verifikasi/OTP');
            $table->string('full_name', 255)->nullable()
                  ->comment('Nama lengkap legal (opsional), berbeda dari display name');
            $table->boolean('is_active')->default(true)
                  ->comment('Status aktif/non-aktif akun (soft disable)');
            $table->timestamps();

            $table->comment('Master data pengguna/aplikasi');
        });

        /**
         * Tabel: roles
         * Daftar peran (ADMIN, CUSTOMER, CS, dsb).
         */
        Schema::create('roles', function (Blueprint $table) {
            $table->smallIncrements('id')
                  ->comment('Primary key role (smallint, auto increment)');
            $table->string('code', 50)->unique()
                  ->comment('Kode unik role (mis. ADMIN, CUSTOMER)');
            $table->string('name', 100)->nullable()
                  ->comment('Nama/label role untuk tampilan');
            $table->comment('Daftar peran/otorisasi pengguna');
        });

        /**
         * Tabel: user_roles
         * Pivot many-to-many antara users & roles.
         */
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('FK ke users.id');
            $table->unsignedSmallInteger('role_id')
                  ->comment('FK ke roles.id');
            $table->timestamp('created_at')->useCurrent()
                  ->comment('Waktu penetapan role');

            $table->unique(['user_id','role_id'], 'user_roles_user_id_role_id_unique');

            // FK manual untuk smallIncrements
            $table->foreign('role_id')
                  ->references('id')->on('roles')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->comment('Pivot relasi peran yang dimiliki setiap user');
        });

        /**
         * Tabel: addresses
         * Alamat milik pengguna (pengiriman/penagihan).
         */
        Schema::create('addresses', function (Blueprint $table) {
            $table->bigIncrements('id')
                  ->comment('Primary key alamat (auto increment)');
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->cascadeOnUpdate()
                  ->comment('FK ke pemilik alamat (users.id); null jika guest)');
            $table->string('label', 100)->nullable()
                  ->comment('Label alias alamat (Rumah/Kantor/Utama)');
            $table->string('recipient_name', 255)->nullable()
                  ->comment('Nama penerima paket');
            $table->string('phone', 30)->nullable()
                  ->comment('Telepon penerima');
            $table->string('line1', 255)->nullable()
                  ->comment('Alamat baris 1 (jalan, nomor)');
            $table->string('line2', 255)->nullable()
                  ->comment('Alamat baris 2 (RT/RW/kompleks), opsional');
            $table->string('city', 100)->nullable()
                  ->comment('Kota/Kabupaten');
            $table->string('province', 100)->nullable()
                  ->comment('Provinsi');
            $table->string('postal_code', 20)->nullable()
                  ->comment('Kode pos');
            $table->string('country', 2)->nullable()
                  ->comment('Kode negara ISO-3166-1 alpha-2 (mis. ID)');
            $table->boolean('is_default')->default(false)
                  ->comment('Penanda alamat default untuk user');
            $table->timestamps();

            $table->comment('Daftar alamat yang dapat digunakan saat checkout/billing');
        });

        /**
         * Tabel: password_reset_tokens
         * Tabel bawaan Laravel untuk menyimpan token reset password.
         */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()
                  ->comment('Email yang meminta reset password (PK)');
            $table->string('token')
                  ->comment('Token sekali pakai untuk reset password');
            $table->timestamp('created_at')->nullable()
                  ->comment('Waktu pembuatan token');
            $table->comment('Token reset password (bawaan Laravel)');
        });

        /**
         * Tabel: sessions
         * Menyimpan sesi login ketika session driver = database.
         */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()
                  ->comment('Session ID (PK)');
            $table->foreignId('user_id')->nullable()->index()
                  ->comment('Opsional: user pemilik sesi (tidak ada FK default agar kompatibel dengan berbagai skenario)');
            $table->string('ip_address', 45)->nullable()
                  ->comment('Alamat IP terakhir');
            $table->text('user_agent')->nullable()
                  ->comment('User-Agent terakhir');
            $table->longText('payload')
                  ->comment('Data session ter-serialize');
            $table->integer('last_activity')->index()
                  ->comment('Timestamp unix last activity untuk garbage collection');

            $table->comment('Tabel sesi untuk Laravel session driver database');
        });
    }

    /**
     * Rollback migrasi.
     *
     * Urutan drop:
     * - sessions (tanpa FK)
     * - password_reset_tokens (tanpa FK)
     * - addresses (FK ke users)
     * - user_roles (FK ke users & roles)
     * - roles
     * - users
     */
    public function down(): void
    {
        // Tabel tanpa dependensi dulu
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        // Tabel yang bergantung ke users
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('user_roles');

        // Master reference
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
};
