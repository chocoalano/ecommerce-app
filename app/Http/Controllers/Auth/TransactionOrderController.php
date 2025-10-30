<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\OrderFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;

class TransactionOrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}
    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->route('auth.login');
        }

        // ---------- UI crumbs & title berdasarkan ?type= ----------
        $typeInput   = $request->input('type', 'All');
        $typeNorm    = strtolower($typeInput);
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil',  'href' => route('auth.profile')],
            ['label' => 'Daftar Order', 'href' => null],
        ];
        $title = 'Daftar Semua Order Produk';
        switch ($typeNorm) {
            case 'pending':
                $title .= ' (Pending Pembayaran)';
                $breadcrumbs[] = ['label' => 'Order Pending', 'href' => null];
                $request->merge(['status' => 'pending']);
                break;

            case 'shipped': // confirmed|processing|shipped
                $title = 'Daftar Order Diproses / Dikirim';
                $breadcrumbs[] = ['label' => 'Order Diproses', 'href' => null];
                $request->merge(['status_in' => ['confirmed','processing','shipped']]);
                break;

            case 'completed':
                $title = 'Daftar Order Selesai';
                $breadcrumbs[] = ['label' => 'Order Selesai', 'href' => null];
                $request->merge(['status' => 'completed']);
                break;

            default:
                $breadcrumbs[] = ['label' => 'Semua Order', 'href' => null];
                $typeInput = 'All';
                break;
        }

        // ---------- PerPage (clamp & default) ----------
        $perPage = (int) $request->integer('per_page', 5);
        $perPage = max(1, min(50, $perPage));
        // pastikan masuk ke DTO
        if (!$request->filled('per_page')) {
            $request->merge(['per_page' => $perPage]);
        }
        // ---------- Filter DTO ----------
        // DTO harus mendukung: search, status, status_in[], date_from, date_to, per_page, page
        $filters = OrderFilterDTO::fromRequest($request);
        // ---------- Ambil data via Service (balik LengthAwarePaginator) ----------
        $orders = $this->orderService->listCustomerOrders($customer, $filters);

        // ---------- Label status (untuk dropdown non-AJAX) ----------
        $statuses = [
            'pending'    => 'Menunggu Pembayaran',
            'confirmed'  => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
        ];

        // ---------- JSON (sinkron dengan orders-adapted.js) ----------
        if ($request->wantsJson() || $request->ajax()) {
            // Pastikan OrderResource memuat relasi: items.product, shippingAddress (alias shipping_address di resource)
            $ordersArray = collect($orders->items())
                ->map(fn ($o) => (new OrderResource($o))->toArray($request))
                ->values()
                ->all();

            return response()->json([
                'success'    => true,
                'orders'     => $ordersArray,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'total'        => $orders->total(),
                    'per_page'     => $orders->perPage(),
                ],
            ]);
        }

        // ---------- Halaman shell (AJAX yang isi list) ----------
        return view('pages.auth.profile.list_transaction', [
            'customer'           => $customer,
            'breadcrumbs'        => $breadcrumbs,
            'title'              => $title,
            'currentType'        => $typeInput,
            'statuses'           => $statuses,

            // untuk dropdown per page (kalau dipakai)
            'perPageDefault'     => $orders->perPage(),

            // data-attr dipakai JS
            'sourceUrl'          => route('auth.orders'),
            'detailUrlTemplate'  => '/auth/orders/:id',
            'cancelUrlTemplate'  => '/auth/orders/:id/cancel',
        ]);
    }
}
