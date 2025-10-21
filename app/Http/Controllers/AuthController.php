<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\OrderReturn;
use App\Models\Product\Wishlist;
use App\Models\Product\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin(Request $request)
    {
        // Jika pengguna sudah login sebagai 'customer', arahkan langsung ke dashboard
        if (Auth::guard('customer')->check()) {
            return redirect()->route('auth.profile');
        }

        return view('pages.auth.login');
    }

    /**
     * Memproses permintaan login (POST).
     */
    public function login_submit(Request $request)
    {
        // Jika pengguna sudah login sebagai 'customer', arahkan langsung ke dashboard
        if (Auth::guard('customer')->check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anda sudah login.',
                    'redirect' => route('auth.profile')
                ]);
            }
            return redirect()->route('auth.profile');
        }

        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Persiapan kredensial untuk autentikasi
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => true, // Pastikan user aktif
        ];

        // Rate limiting - batasi percobaan login
        $key = 'login_attempts:' . $request->ip();
        $attempts = cache()->get($key, 0);

        if ($attempts >= 5) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.',
                    'errors' => ['email' => ['Terlalu banyak percobaan login. Coba lagi dalam 15 menit.']]
                ], 429);
            }

            return back()->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'])
                        ->withInput($request->only('email'));
        }

        // Coba login menggunakan guard 'customer'
        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            // Login berhasil
            $request->session()->regenerate();

            // Reset rate limiting counter
            cache()->forget($key);

            // Log aktivitas login (opsional)
            \Log::info('Customer login successful', [
                'customer_id' => Auth::guard('customer')->id(),
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil! Selamat datang kembali.',
                    'redirect' => route('auth.profile'),
                    'user' => [
                        'name' => Auth::guard('customer')->user()->name,
                        'email' => Auth::guard('customer')->user()->email,
                    ]
                ]);
            }

            return redirect()->intended(route('auth.profile'))
                           ->with('success', 'Login berhasil! Selamat datang kembali.');
        } else {
            // Login gagal - tingkatkan counter attempts
            cache()->put($key, $attempts + 1, now()->addMinutes(15));

            // Log percobaan login gagal
            \Log::warning('Customer login failed', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah.',
                    'errors' => ['email' => ['Email atau password salah.']]
                ], 422);
            }

            return back()->withErrors(['email' => 'Email atau password salah.'])
                        ->withInput($request->only('email'));
        }
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showRegister(Request $request)
    {
        return view('pages.auth.register');
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Akun', 'href' => null],
        ];

        if (!$customer) {
            $overviewStats = [];
            $statusMap = [];
            $orders = [];
            $phoneCountries = [];
        } else {
            $ordersCount = Order::where('customer_id', $customer->id)->count();
            $reviewsCount = ProductReview::where('customer_id', $customer->id)->count();
            $wishlistModel = Wishlist::where('customer_id', $customer->id)->first();
            $wishlistCount = $wishlistModel ? $wishlistModel->items()->count() : 0;
            $returnsCount = OrderReturn::whereHas('order', fn($q) => $q->where('customer_id', $customer->id))->count();

            $overviewStats = [
                ['icon' => 'truck', 'label' => 'Pesanan', 'value' => (string)$ordersCount, 'delta' => null, 'delta_bg' => 'bg-green-100', 'delta_text' => 'text-green-800', 'note' => 'Pesanan terakhir'],
                ['icon' => 'star', 'label' => 'Ulasan', 'value' => (string)$reviewsCount, 'delta' => null, 'delta_bg' => 'bg-green-100', 'delta_text' => 'text-green-800', 'note' => 'Ulasan Anda'],
                ['icon' => 'heart', 'label' => 'Produk Favorit', 'value' => (string)$wishlistCount, 'delta' => null, 'delta_bg' => 'bg-red-100', 'delta_text' => 'text-red-800', 'note' => 'Di wishlist'],
                ['icon' => 'return', 'label' => 'Retur Produk', 'value' => (string)$returnsCount, 'delta' => null, 'delta_bg' => 'bg-green-100', 'delta_text' => 'text-green-800', 'note' => 'Permintaan retur'],
            ];

            $statusMap = [
                'in_transit' => ['label' => 'Dalam pengiriman', 'badge' => 'bg-yellow-100 text-yellow-800', 'icon' => 'truck-badge'],
                'cancelled'  => ['label' => 'Dibatalkan', 'badge' => 'bg-red-100 text-red-800', 'icon' => 'x'],
                'completed'  => ['label' => 'Selesai', 'badge' => 'bg-green-100 text-green-800', 'icon' => 'check'],
            ];

            $ordersRaw = Order::where('customer_id', $customer->id)->latest('placed_at')->take(4)->get();
            $orders = $ordersRaw->map(function ($o) {
                $statusKey = match ($o->status) {
                    Order::ST_SHIPPED, Order::ST_PROCESS => 'in_transit',
                    Order::ST_CANCELED => 'cancelled',
                    Order::ST_COMPLETED => 'completed',
                    default => 'completed',
                };

                return [
                    'id' => $o->order_no,
                    'date' => $o->placed_at ? $o->placed_at->format('d.m.Y') : ($o->created_at?->format('d.m.Y') ?? ''),
                    'price' => 'Rp ' . number_format($o->grand_total ?? 0, 0, ',', '.'),
                    'status_key' => $statusKey,
                    'menu_id' => $o->id,
                    'has_cancel' => $o->status !== Order::ST_CANCELED,
                ];
            })->toArray();

            $phoneCountries = [
                ['label' => 'United States (+1)', 'code' => '+1',  'flag' => 'us'],
                ['label' => 'United Kingdom (+44)', 'code' => '+44', 'flag' => 'uk'],
                ['label' => 'Australia (+61)', 'code' => '+61', 'flag' => 'au'],
                ['label' => 'Germany (+49)', 'code' => '+49', 'flag' => 'de'],
                ['label' => 'France (+33)', 'code' => '+33', 'flag' => 'fr'],
            ];
        }

        return view('pages.auth.profile', compact('customer', 'breadcrumbs', 'overviewStats', 'statusMap', 'orders', 'phoneCountries'));
    }

    // public function register_submit(Request $request) { ... } // Tambahkan logika registrasi di sini

    /**
     * Memproses permintaan logout.
     */
    public function logout(Request $request)
    {
        // Proses logout dari guard 'customer'
        Auth::guard('customer')->logout();

        // Invalidasi sesi saat ini
        $request->session()->invalidate();

        // Regenerate token CSRF untuk keamanan tambahan
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->route('auth.login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Menampilkan halaman riwayat pesanan customer.
     */
    public function orders(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // Query orders dengan pagination dan filter
        $query = Order::where('customer_id', $customer->id)
            ->with(['items.product', 'shippingAddress'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal jika ada
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search berdasarkan order number
        if ($request->filled('search')) {
            $query->where('order_no', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(5)->withQueryString();

        // Get available statuses for filter
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                ]
            ]);
        }

        return view('pages.auth.orders', compact('orders', 'statuses'));
    }

    /**
     * Menampilkan detail pesanan specific.
     */
    public function orderDetail(Request $request, Order $order)
    {
        $customer = Auth::guard('customer')->user();

        // Pastikan order milik customer yang login
        if ($order->customer_id !== $customer->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan.'
                ], 404);
            }

            abort(404, 'Order tidak ditemukan.');
        }

        // Load relationships yang dibutuhkan
        $order->load([
            'items.product',
            'shippingAddress',
            'billingAddress',
            'customer'
        ]);

        // Parse notes jika ada (untuk info payment)
        $orderNotes = [];
        if ($order->notes) {
            $orderNotes = json_decode($order->notes, true) ?? [];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'status' => $order->status,
                    'currency' => $order->currency,
                    'subtotal_amount' => $order->subtotal_amount,
                    'discount_amount' => $order->discount_amount,
                    'shipping_amount' => $order->shipping_amount,
                    'tax_amount' => $order->tax_amount,
                    'grand_total' => $order->grand_total,
                    'payment_method' => $order->payment_method,
                    'placed_at' => $order->placed_at,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'sku' => $item->sku,
                            'qty' => $item->qty,
                            'unit_price' => $item->unit_price,
                            'row_total' => $item->row_total,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'slug' => $item->product->slug,
                            ] : null
                        ];
                    }),
                    'shipping_address' => $order->shippingAddress,
                    'billing_address' => $order->billingAddress,
                    'notes' => $orderNotes
                ]
            ]);
        }

        return view('pages.auth.order-detail', compact('order', 'orderNotes'));
    }

    /**
     * Cancel order (jika masih bisa dibatalkan).
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $customer = Auth::guard('customer')->user();

        // Pastikan order milik customer yang login
        if ($order->customer_id !== $customer->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan.'
                ], 404);
            }

            return back()->with('error', 'Order tidak ditemukan.');
        }

        // Cek apakah order bisa dibatalkan (hanya pending atau confirmed)
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak dapat dibatalkan karena sudah diproses.'
                ], 422);
            }

            return back()->with('error', 'Order tidak dapat dibatalkan karena sudah diproses.');
        }

        // Update status order
        $order->update([
            'status' => 'cancelled',
            'notes' => json_encode(array_merge(
                json_decode($order->notes, true) ?? [],
                [
                    'cancelled_at' => now()->toISOString(),
                    'cancelled_by' => 'customer',
                    'cancel_reason' => $request->input('reason', 'Cancelled by customer')
                ]
            ))
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan.',
                'order' => [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'status' => $order->status
                ]
            ]);
        }

        return back()->with('success', 'Order berhasil dibatalkan.');
    }

    public function setting(Order $order)
    {
        $customer = Auth::guard('customer')->user();

        return view('pages.auth.account-setting', compact('customer'));
    }
}
