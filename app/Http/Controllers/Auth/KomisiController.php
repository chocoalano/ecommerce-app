<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class KomisiController extends Controller
{
    /**
     * Menghasilkan data yang sudah di-paginate sederhana dari koleksi.
     */
    private function simplePaginateCollection(Collection $collection, Request $request, int $perPage = 10): Paginator
    {
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage * $perPage) - $perPage;

        // Paginator sederhana hanya memotong data dan tidak menghitung total
        return new Paginator(
            $collection->slice($offset, $perPage)->values(), // Data untuk halaman saat ini
            $perPage, // Item per halaman
            $currentPage, // Halaman saat ini
            ['path' => $request->url()] // Konfigurasi path untuk link
        );
    }

    // --- Generator Data Komisi ---

    private function generateSponsorData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 50;
        $statuses = ['Dibayar', 'Pending', 'Batal'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Transaksi'  => 'SPN' . $faker->unique()->randomNumber(5),
                'Tanggal'       => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i'),
                'Member Disponsori' => $faker->name,
                'Jumlah (IDR)'  => $faker->numberBetween(50000, 250000),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        $faker->unique(true);
        return $data;
    }

    private function generatePairingData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 120;
        $statuses = ['Dibayar', 'Pending'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Transaksi'  => 'PRG' . $faker->unique()->randomNumber(5),
                'Tanggal'       => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i'),
                'Pasangan Kiri' => $faker->numberBetween(1, 10),
                'Pasangan Kanan' => $faker->numberBetween(1, 10),
                'Jumlah (IDR)'  => $faker->numberBetween(100000, 500000),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        $faker->unique(true);
        return $data;
    }

    private function generateMatchingData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 75;
        $statuses = ['Dibayar', 'Pending'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Transaksi'  => 'MTCH' . $faker->unique()->randomNumber(4),
                'Tanggal'       => $faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d H:i'),
                'Generasi'      => $faker->numberBetween(1, 5),
                'Sumber Komisi' => $faker->name,
                'Jumlah (IDR)'  => $faker->numberBetween(50000, 300000),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        $faker->unique(true);
        return $data;
    }

    private function generateRewardsData(): array
    {
        $faker = Faker::create('id_ID');
        $data = [];
        $jumlah_baris = 20;
        $statuses = ['Diterima', 'Diajukan', 'Kadaluarsa'];
        $rewards = ['Handphone Baru', 'Motor Matic', 'Perjalanan Umroh', 'Mobil Murah'];

        for ($i = 1; $i <= $jumlah_baris; $i++) {
            $data[] = [
                'ID Reward'     => 'RWD' . $faker->unique()->randomNumber(3),
                'Tanggal Klaim' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'Nama Reward'   => $faker->randomElement($rewards),
                'Syarat Omset'  => $faker->numberBetween(50000000, 500000000),
                'Status'        => $faker->randomElement($statuses),
            ];
        }
        $faker->unique(true);
        return $data;
    }

    // --- Fungsi Controller Utama ---

    public function index(Request $request) {
        $customer = Auth::guard('customer')->user();
        $type = $request->input('type', 'sponsors'); // Default ke 'sponsors'
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Komisi', 'href' => null],
        ];

        // 1. Tentukan Judul, Data, dan Header berdasarkan tipe
        switch ($type) {
            case 'sponsors':
                $title = 'Daftar Komisi Sponsor';
                $allData = $this->generateSponsorData();
                $header = ['ID Transaksi', 'Tanggal', 'Member Disponsori', 'Jumlah (IDR)', 'Status'];
                break;
            case 'pairings':
                $title = 'Daftar Komisi Pairing';
                $allData = $this->generatePairingData();
                $header = ['ID Transaksi', 'Tanggal', 'Pasangan Kiri', 'Pasangan Kanan', 'Jumlah (IDR)', 'Status'];
                break;
            case 'matchings':
                $title = 'Daftar Komisi Matching';
                $allData = $this->generateMatchingData();
                $header = ['ID Transaksi', 'Tanggal', 'Generasi', 'Sumber Komisi', 'Jumlah (IDR)', 'Status'];
                break;
            case 'rewards':
                $title = 'Daftar Komisi Reward';
                $allData = $this->generateRewardsData();
                $header = ['ID Reward', 'Tanggal Klaim', 'Nama Reward', 'Syarat Omset (IDR)', 'Status'];
                break;
            default:
                $title = 'Daftar Komisi Sponsor';
                $allData = $this->generateSponsorData();
                $header = ['ID Transaksi', 'Tanggal', 'Member Disponsori', 'Jumlah (IDR)', 'Status'];
                $type = 'sponsors'; // Setel tipe default
                break;
        }

        // 2. Lakukan Simple Pagination
        $collection = collect($allData);
        $paginatedData = $this->simplePaginateCollection($collection, $request, 10);

        // 3. Kirim data ke View
        return view('pages.auth.profile.list_komisi', [
            'customer'    => $customer,
            'breadcrumbs' => $breadcrumbs,
            'data'        => $paginatedData, // Mengganti $data dengan $paginatedData
            'header'      => $header,
            'title'       => $title,
            'currentType' => $type, // Tambahkan currentType untuk navigasi
        ]);
    }
}
