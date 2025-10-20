<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct\Order;
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
        return view('pages.auth.profile');
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
