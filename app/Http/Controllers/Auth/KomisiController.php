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
                $allData =[];
                $header = ['ID Transaksi', 'Tanggal', 'Member Disponsori', 'Jumlah (IDR)', 'Status'];
                break;
            case 'pairings':
                $title = 'Daftar Komisi Pairing';
                $allData =[];
                $header = ['ID Transaksi', 'Tanggal', 'Pasangan Kiri', 'Pasangan Kanan', 'Jumlah (IDR)', 'Status'];
                break;
            case 'matchings':
                $title = 'Daftar Komisi Matching';
                $allData =[];
                $header = ['ID Transaksi', 'Tanggal', 'Generasi', 'Sumber Komisi', 'Jumlah (IDR)', 'Status'];
                break;
            case 'rewards':
                $title = 'Daftar Komisi Reward';
                $allData =[];
                $header = ['ID Reward', 'Tanggal Klaim', 'Nama Reward', 'Syarat Omset (IDR)', 'Status'];
                break;
            default:
                $title = 'Daftar Komisi Sponsor';
                $allData =[];
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
