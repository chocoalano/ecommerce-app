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
                $allData = [];
                $header = ['ID Transaksi', 'Tanggal', 'Jenis', 'Jumlah (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Transaksi', 'href' => null];
                break;

            case 'withdrawal':
                $title = 'Daftar Penarikan Dana';
                $allData = [];
                $header = ['ID Penarikan', 'Tanggal', 'Jumlah (IDR)', 'Metode', 'Status'];
                $breadcrumbs[] = ['label' => 'Penarikan', 'href' => null];
                break;

            default: // member_prospect
                $title = 'Daftar Transaksi E-Wallet';
                $allData = [];
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
