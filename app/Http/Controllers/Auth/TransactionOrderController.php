<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class TransactionOrderController extends Controller
{
    /**
     * Menghasilkan data yang sudah di-paginate sederhana dari koleksi.
     * Digunakan untuk Simple Pagination (hanya Next/Previous).
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

    public function index(Request $request)
    {
        // 1. Ambil data autentikasi dan breadcrumbs
        $customer = Auth::guard('customer')->user();
        // Mengubah default type ke 'Pending' yang mewakili 'Pending Pembayaran'
        $type = $request->input('type', 'Pending');
        $allData = [];
        $header = [];

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Daftar Order', 'href' => null],
        ];

        // 2. Tentukan Data, Header, dan Judul berdasarkan 'type'
        switch ($type) {
            case 'Pending':
                $title = 'Daftar Order Pending Pembayaran';
                $baseData = [];
                $allData = array_filter($baseData, fn($item) => $item['Status'] === 'Pending Pembayaran');
                // Header disesuaikan untuk Order Produk
                $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Order Pending', 'href' => null];
                break;

            case 'Berbayar':
                $title = 'Daftar Order Diproses / Dikirim';
                // Menggabungkan status Diproses dan Dikirim
                $baseData = [];
                $allData = array_filter($baseData, fn($item) => $item['Status'] === 'Diproses' || $item['Status'] === 'Dikirim');
                $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Order Diproses', 'href' => null];
                break;
            case 'Selesai':
                $title = 'Daftar Order Selesai';
                $baseData = [];
                $allData = array_filter($baseData, fn($item) => $item['Status'] === 'Selesai');
                $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Order Selesai', 'href' => null];
                break;

            default:
                $title = 'Daftar Semua Order Produk';
                $allData = $this->generateProductOrderData(); // Tampilkan semua order
                $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Semua Order', 'href' => null];
                $type = 'All'; // Setel tipe default non-filter
                break;
        }

        // 3. Konversi Array ke Collection Laravel
        // array_filter mengembalikan array yang perlu di-reset key-nya
        $collection = collect(array_values($allData));

        // 4. Lakukan Pagination
        $paginatedData = $this->simplePaginateCollection($collection, $request, 10);

        // 5. Kirim data yang sudah di-paginate, header, dan title ke View
        return view('pages.auth.profile.list_transaction', [
            'customer'    => $customer,
            'breadcrumbs' => $breadcrumbs,
            'data'        => $paginatedData,
            'header'      => $header, // Sertakan header untuk view
            'title'       => $title,
            'currentType' => $type, // Kirim tipe saat ini untuk navigasi/penanda
        ]);
    }
}
