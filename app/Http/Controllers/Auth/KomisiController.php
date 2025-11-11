<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Mlm\TblBonusSponsor;
use App\Models\Mlm\TblBonusPairing;
use App\Models\Mlm\TblBonusMatching;
use App\Models\Mlm\TblBonus;
use App\Models\Auth\Customer;
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
                // Ambil data dari TblBonusSponsor
                $bonuses = TblBonusSponsor::where('member_id', $customer->id)
                    ->orderBy('date', 'desc')
                    ->get();

                $allData = $bonuses->map(function($bonus) {
                    $fromMember = Customer::find($bonus->from_id);
                    return [
                        'id' => 'SPO-' . str_pad($bonus->id, 6, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($bonus->date)->format('d M Y'),
                        'member' => $fromMember ? $fromMember->name : 'N/A',
                        'amount' => 'Rp ' . number_format($bonus->bonus, 0, ',', '.'),
                        'status' => $bonus->status == 1 ? 'Dibayar' : 'Pending',
                        'status_class' => $bonus->status == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ];
                })->toArray();
                $header = ['ID Transaksi', 'Tanggal', 'Member Disponsori', 'Jumlah (IDR)', 'Status'];
                break;

            case 'pairings':
                $title = 'Daftar Komisi Pairing';
                // Ambil data dari TblBonusPairing
                $bonuses = TblBonusPairing::where('member_id', $customer->id)
                    ->orderBy('date', 'desc')
                    ->get();

                $allData = $bonuses->map(function($bonus) {
                    return [
                        'id' => 'PAIR-' . str_pad($bonus->id, 6, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($bonus->date)->format('d M Y'),
                        'pair_left' => floor($bonus->pair / 2),
                        'pair_right' => ceil($bonus->pair / 2),
                        'amount' => 'Rp ' . number_format($bonus->bonus, 0, ',', '.'),
                        'status' => $bonus->status == 1 ? 'Dibayar' : 'Pending',
                        'status_class' => $bonus->status == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ];
                })->toArray();
                $header = ['ID Transaksi', 'Tanggal', 'Pasangan Kiri', 'Pasangan Kanan', 'Jumlah (IDR)', 'Status'];
                break;

            case 'matchings':
                $title = 'Daftar Komisi Matching';
                // Ambil data dari TblBonusMatching
                $bonuses = TblBonusMatching::where('member_id', $customer->id)
                    ->orderBy('date', 'desc')
                    ->get();

                $allData = $bonuses->map(function($bonus) {
                    $fromMember = Customer::find($bonus->from_id);
                    return [
                        'id' => 'MATCH-' . str_pad($bonus->id, 6, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($bonus->date)->format('d M Y'),
                        'generation' => 'Gen-' . $bonus->level,
                        'source' => $fromMember ? $fromMember->name : 'N/A',
                        'amount' => 'Rp ' . number_format($bonus->bonus, 0, ',', '.'),
                        'status' => $bonus->status == 1 ? 'Dibayar' : 'Pending',
                        'status_class' => $bonus->status == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ];
                })->toArray();
                $header = ['ID Transaksi', 'Tanggal', 'Generasi', 'Sumber Komisi', 'Jumlah (IDR)', 'Status'];
                break;

            case 'rewards':
                $title = 'Daftar Komisi Reward';
                // Ambil data dari TblBonus
                $bonuses = TblBonus::where('member_id', $customer->id)
                    ->orderBy('date', 'desc')
                    ->get();

                $allData = $bonuses->map(function($bonus) {
                    return [
                        'id' => 'REW-' . str_pad($bonus->id, 6, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($bonus->date)->format('d M Y'),
                        'reward_name' => 'Bonus #' . $bonus->index_value,
                        'requirement' => 'Rp ' . number_format($bonus->index_value * 1000000, 0, ',', '.'),
                        'amount' => 'Rp ' . number_format($bonus->tax_netto, 0, ',', '.'),
                        'status' => $bonus->status == 1 ? 'Diklaim' : 'Tersedia',
                        'status_class' => $bonus->status == 1 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                    ];
                })->toArray();
                $header = ['ID Reward', 'Tanggal Klaim', 'Nama Reward', 'Syarat Omset (IDR)', 'Status'];
                break;

            default:
                $title = 'Daftar Komisi Sponsor';
                $bonuses = TblBonusSponsor::where('member_id', $customer->id)
                    ->orderBy('date', 'desc')
                    ->get();

                $allData = $bonuses->map(function($bonus) {
                    $fromMember = Customer::find($bonus->from_id);
                    return [
                        'id' => 'SPO-' . str_pad($bonus->id, 6, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($bonus->date)->format('d M Y'),
                        'member' => $fromMember ? $fromMember->name : 'N/A',
                        'amount' => 'Rp ' . number_format($bonus->bonus, 0, ',', '.'),
                        'status' => $bonus->status == 1 ? 'Dibayar' : 'Pending',
                        'status_class' => $bonus->status == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    ];
                })->toArray();
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
