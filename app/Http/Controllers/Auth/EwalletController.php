<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EwalletController extends Controller
{
    /**
     * Menghasilkan data yang sudah di-paginate dari koleksi.
     */
    private function simplePaginateCollection(Collection $collection, Request $request, int $perPage = 10): Paginator
    {
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage * $perPage) - $perPage;

        return new Paginator(
            $collection->slice($offset, $perPage)->values(), // Data untuk halaman saat ini
            $perPage, // Item per halaman
            $currentPage, // Halaman saat ini
            ['path' => $request->url()] // Konfigurasi path untuk link
        );
    }

    // --- Generator Data ---

    /**
     * Menghasilkan dummy data untuk daftar member MLM (sesuai contoh sebelumnya).
     */
    private function generateMlmData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 100;
        $levels = ['Associate', 'Bronze Manager', 'Silver Leader', 'Gold Director', 'Platinum Elite'];
        $statuses = ['Aktif', 'Non-Aktif', 'Pending Verifikasi', 'Blokir'];

        $sponsor_ids = collect(range(1, 10))->map(fn($i) => 'MLM' . str_pad($i, 4, '0', STR_PAD_LEFT))->toArray();

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $member_id = 'MLM' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $omset_pribadi = $faker->numberBetween(500000, 10000000);
            $komisi_jaringan = round($omset_pribadi * $faker->randomFloat(2, 0.05, 0.20));

            $data[] = [
                'ID Anggota'            => $member_id,
                'Nama Anggota'          => $faker->name,
                'Sponsor'               => $faker->randomElement($sponsor_ids),
                'Level'                 => $faker->randomElement($levels),
                'Omset Pribadi (IDR)'   => $omset_pribadi,
                'Komisi Jaringan (IDR)' => $komisi_jaringan,
                'Status Keanggotaan'    => $faker->randomElement($statuses),
                'Tanggal Gabung'        => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            ];
        }
        return $data;
    }

    /**
     * Menghasilkan dummy data untuk daftar transaksi E-Wallet.
     */
    private function generateTransactionData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 80; // Misalnya 80 baris
        $tipe_transaksi = ['Kredit Komisi', 'Debet Pembelian Produk', 'Kredit Bonus Refferal', 'Debet Penarikan (Pending)'];
        $statuses = ['Selesai', 'Pending', 'Dibatalkan'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Transaksi'  => 'TX' . $faker->unique()->randomNumber(6),
                'Tanggal'       => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i'),
                'Jenis'         => $faker->randomElement($tipe_transaksi),
                'Jumlah (IDR)'  => $faker->numberBetween(50000, 5000000),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        // Reset unique generator untuk digunakan kembali di tempat lain
        $faker->unique(true);
        return $data;
    }

    /**
     * Menghasilkan dummy data untuk daftar penarikan dana.
     */
    private function generateWithdrawalData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 40; // Misalnya 40 baris
        $metode = ['Bank BCA', 'Bank Mandiri', 'E-Wallet DANA', 'Bank BRI'];
        $statuses = ['Diproses', 'Selesai', 'Gagal'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Penarikan'  => 'WD' . $faker->unique()->randomNumber(5),
                'Tanggal'       => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i'),
                'Jumlah (IDR)'  => $faker->numberBetween(100000, 10000000),
                'Metode'        => $faker->randomElement($metode),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        $faker->unique(true);
        return $data;
    }

    // --- Fungsi Controller Utama ---

    public function index(Request $request)
    {
        // 1. Ambil data autentikasi dan breadcrumbs
        $customer = Auth::guard('customer')->user();
        $type = $request->input('type', 'transaction'); // Default ke transaction

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'E-Wallet', 'href' => null],
        ];

        // 2. Tentukan Data, Header, dan Judul berdasarkan 'type'
        switch ($type) {
            case 'transactions':
                $title = 'Daftar Transaksi E-Wallet';
                $allData = $this->generateTransactionData();
                $header = ['ID Transaksi', 'Tanggal', 'Jenis', 'Jumlah (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Transaksi', 'href' => null];
                break;

            case 'withdrawal':
                $title = 'Daftar Penarikan Dana';
                $allData = $this->generateWithdrawalData();
                $header = ['ID Penarikan', 'Tanggal', 'Jumlah (IDR)', 'Metode', 'Status'];
                $breadcrumbs[] = ['label' => 'Penarikan', 'href' => null];
                break;

            default: // member_prospect
                $title = 'Daftar Transaksi E-Wallet';
                $allData = $this->generateTransactionData();
                $header = ['ID Transaksi', 'Tanggal', 'Jenis', 'Jumlah (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Transaksi', 'href' => null];
                break;
        }

        // 3. Konversi Array ke Collection Laravel
        $collection = collect($allData);

        // 4. Lakukan Pagination
        $paginatedData = $this->simplePaginateCollection($collection, $request, 10);

        // 5. Kirim data yang sudah di-paginate, header, dan title ke View
        return view('pages.auth.profile.list_ewallet', [
            'customer'    => $customer,
            'breadcrumbs' => $breadcrumbs,
            'data'        => $paginatedData,
            'header'      => $header, // Sertakan header untuk view
            'title'       => $title,
            'currentType' => $type, // Kirim tipe saat ini untuk navigasi/penanda
        ]);
    }
}
