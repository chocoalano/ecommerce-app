<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Mlm\TblEwalletTransaction;
use App\Models\Mlm\TblTopupRequest;
use App\Models\Mlm\TblWithdrawalRequest;
use App\Services\MidtransGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $type = $request->input('type', 'transactions'); // Default ke transactions

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'E-Wallet', 'href' => null],
        ];

        // 2. Tentukan Data, Header, dan Judul berdasarkan 'type'
        switch ($type) {
            case 'transactions':
                $title = 'Daftar Transaksi E-Wallet';

                // Ambil semua transaksi dari TblEwalletTransaction
                $transactions = TblEwalletTransaction::where('member_id', $customer->id)
                    ->orderBy('created_on', 'desc')
                    ->get();

                $allData = $transactions->map(function ($transaction) {
                    // Tentukan jenis transaksi
                    $jenis = '-';
                    $amount = 0;
                    $status = 'Selesai';

                    if ($transaction->credit > 0) {
                        $jenis = 'Kredit (Masuk)';
                        $amount = $transaction->credit;
                    } elseif ($transaction->debit > 0) {
                        $jenis = 'Debit (Keluar)';
                        $amount = $transaction->debit;
                    }

                    return [
                        'id' => 'TRX-'.str_pad($transaction->id, 8, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($transaction->created_on)->format('d M Y H:i'),
                        'type' => $jenis,
                        'note' => $transaction->note ?: '-',
                        'amount' => 'Rp '.number_format($amount, 0, ',', '.'),
                        'balance' => 'Rp '.number_format($transaction->balance, 0, ',', '.'),
                        'status' => $status,
                        'status_class' => 'bg-green-100 text-green-800',
                    ];
                })->toArray();

                $header = ['ID Transaksi', 'Tanggal', 'Jenis', 'Keterangan', 'Jumlah (IDR)', 'Saldo (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Transaksi', 'href' => null];
                break;

            case 'withdrawal':
                $title = 'Daftar Penarikan Dana';

                // Ambil hanya transaksi debit (penarikan) dari TblEwalletTransaction
                $withdrawals = TblEwalletTransaction::where('member_id', $customer->id)
                    ->where('debit', '>', 0)
                    ->orderBy('created_on', 'desc')
                    ->get();

                $allData = $withdrawals->map(function ($withdrawal) {
                    // Parse note untuk mendapatkan metode jika ada
                    $note = $withdrawal->note ?: 'Transfer Bank';
                    $metode = 'Transfer Bank';

                    if (stripos($note, 'bank') !== false) {
                        $metode = 'Transfer Bank';
                    } elseif (stripos($note, 'ewallet') !== false || stripos($note, 'wallet') !== false) {
                        $metode = 'E-Wallet';
                    }

                    return [
                        'id' => 'WD-'.str_pad($withdrawal->id, 8, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($withdrawal->created_on)->format('d M Y H:i'),
                        'amount' => 'Rp '.number_format($withdrawal->debit, 0, ',', '.'),
                        'method' => $metode,
                        'note' => $withdrawal->note ?: '-',
                        'status' => 'Selesai',
                        'status_class' => 'bg-green-100 text-green-800',
                    ];
                })->toArray();

                $header = ['ID Penarikan', 'Tanggal', 'Jumlah (IDR)', 'Metode', 'Keterangan', 'Status'];
                $breadcrumbs[] = ['label' => 'Penarikan', 'href' => null];
                break;

            default:
                $title = 'Daftar Transaksi E-Wallet';
                $transactions = TblEwalletTransaction::where('member_id', $customer->id)
                    ->orderBy('created_on', 'desc')
                    ->get();

                $allData = $transactions->map(function ($transaction) {
                    $jenis = '-';
                    $amount = 0;
                    $status = 'Selesai';

                    if ($transaction->credit > 0) {
                        $jenis = 'Kredit (Masuk)';
                        $amount = $transaction->credit;
                    } elseif ($transaction->debit > 0) {
                        $jenis = 'Debit (Keluar)';
                        $amount = $transaction->debit;
                    }

                    return [
                        'id' => 'TRX-'.str_pad($transaction->id, 8, '0', STR_PAD_LEFT),
                        'date' => \Carbon\Carbon::parse($transaction->created_on)->format('d M Y H:i'),
                        'type' => $jenis,
                        'note' => $transaction->note ?: '-',
                        'amount' => 'Rp '.number_format($amount, 0, ',', '.'),
                        'balance' => 'Rp '.number_format($transaction->balance, 0, ',', '.'),
                        'status' => $status,
                        'status_class' => 'bg-green-100 text-green-800',
                    ];
                })->toArray();

                $header = ['ID Transaksi', 'Tanggal', 'Jenis', 'Keterangan', 'Jumlah (IDR)', 'Saldo (IDR)', 'Status'];
                $breadcrumbs[] = ['label' => 'Transaksi', 'href' => null];
                $type = 'transactions';
                break;
        }

        // 3. Konversi Array ke Collection Laravel
        $collection = collect($allData);

        // 4. Lakukan Pagination
        $paginatedData = $this->simplePaginateCollection($collection, $request, 10);

        // 5. Kirim data yang sudah di-paginate, header, dan title ke View
        return view('pages.auth.profile.list_ewallet', [
            'customer' => $customer,
            'breadcrumbs' => $breadcrumbs,
            'data' => $paginatedData,
            'header' => $header, // Sertakan header untuk view
            'title' => $title,
            'currentType' => $type, // Kirim tipe saat ini untuk navigasi/penanda
        ]);
    }

    /**
     * Tampilkan form topup
     */
    public function showTopupForm()
    {
        $customer = Auth::guard('customer')->user();
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'E-Wallet', 'href' => route('auth.ewallet')],
            ['label' => 'Topup', 'href' => null],
        ];

        return view('pages.auth.profile.topup_form', [
            'customer' => $customer,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Proses request topup dengan Midtrans
     */
    public function submitTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000', // Minimal topup 50rb
            'payment_method' => 'required|in:bank_transfer,credit_card,e_wallet',
        ]);

        $customer = Auth::guard('customer')->user();

        try {
            DB::beginTransaction();

            // Generate order ID unik untuk topup
            $orderNo = 'TOPUP-'.$customer->id.'-'.time();

            // Simpan request topup dengan status pending
            $topupRequest = TblTopupRequest::create([
                'member_id' => $customer->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_proof' => null, // Tidak perlu upload bukti untuk Midtrans
                'bank_name' => null,
                'account_number' => null,
                'account_name' => null,
                'status' => TblTopupRequest::STATUS_PENDING,
            ]);

            // Update order_no di model jika ada kolom
            $topupRequest->order_no = $orderNo;
            $topupRequest->save();

            // Buat transaksi Midtrans
            $midtrans = new MidtransGateway;

            // Siapkan data transaksi untuk Midtrans
            $transactionDetails = [
                'order_id' => $orderNo,
                'gross_amount' => (int) $request->amount,
            ];

            $customerDetails = [
                'first_name' => $customer->name,
                'last_name' => '',
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
            ];

            $itemDetails = [[
                'id' => 'topup',
                'price' => (int) $request->amount,
                'quantity' => 1,
                'name' => 'E-Wallet Topup',
            ]];

            $enabledPayments = match ($request->payment_method) {
                'bank_transfer' => ['bca_va', 'bni_va', 'bri_va', 'permata_va', 'other_va'],
                'credit_card' => ['credit_card'],
                'e_wallet' => ['gopay', 'shopeepay', 'qris'],
                default => ['credit_card', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'gopay', 'shopeepay', 'qris'],
            };

            $transaction = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                'enabled_payments' => $enabledPayments,
                'callbacks' => [
                    'finish' => route('auth.ewallet.topup.finish', ['id' => $topupRequest->id]),
                ],
            ];

            // Dapatkan Snap Token
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $snapToken = \Midtrans\Snap::getSnapToken($transaction);

            DB::commit();

            // Redirect ke halaman payment
            return view('pages.auth.profile.topup_payment', [
                'customer' => $customer,
                'topup' => $topupRequest,
                'snapToken' => $snapToken,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Topup Error: '.$e->getMessage());

            return back()->withInput()->with('error', 'Gagal membuat transaksi topup: '.$e->getMessage());
        }
    }

    /**
     * Halaman finish payment
     */
    public function topupFinish($id, Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $topup = TblTopupRequest::where('id', $id)
            ->where('member_id', $customer->id)
            ->first();

        if (! $topup) {
            return redirect()->route('auth.ewallet')->with('error', 'Transaksi tidak ditemukan.');
        }

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'E-Wallet', 'href' => route('auth.ewallet')],
            ['label' => 'Status Topup', 'href' => null],
        ];

        $isSuccess = strtolower((string) $request->query('status', '')) === 'success';

        if ($isSuccess) {
            DB::transaction(function () use ($topup) {
                // Kunci saldo terakhir agar konsisten
                $latestTx = TblEwalletTransaction::where('member_id', $topup->member_id)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $currentBalance = $latestTx ? (float) $latestTx->balance : 0.0;
                $amount = (float) $topup->amount;
                $newBalance = $currentBalance + $amount;

                // Eloquent akan mengisi created_on otomatis (CREATED_AT sudah di-map)
                TblEwalletTransaction::create([
                    'member_id' => $topup->member_id,
                    'type' => true,
                    'credit' => $amount,
                    'debit' => 0,
                    'balance' => $newBalance,
                    'note' => 'Topup (finish?status=success) - Order: '.$topup->order_no,
                ]);

                // Update status topup → approved (sesuai permintaan: tanpa validasi lain)
                $topup->update([
                    'status' => TblTopupRequest::STATUS_APPROVED,
                    'approved_at' => Carbon::now(),
                    'admin_note' => 'Approved via finish?status=success',
                ]);
            });

            \Log::info('Topup finish success: transaksi e-wallet ditambahkan', [
                'topup_id' => $topup->id,
                'member_id' => $topup->member_id,
                'topup_amount' => (float) $topup->amount,
                'order_no' => $topup->order_no,
            ]);
        }

        return view('pages.auth.profile.topup_finish', [
            'customer' => $customer,
            'breadcrumbs' => $breadcrumbs,
            'topup' => $topup->fresh(),
        ]);
    }

    /**
     * Manual approve topup for testing (simulate successful payment)
     * Gunakan ini untuk testing jika webhook Midtrans belum bisa diakses
     */
    public function manualApproveTopup($id)
    {
        $customer = Auth::guard('customer')->user();

        $topupRequest = TblTopupRequest::where('id', $id)
            ->where('member_id', $customer->id)
            ->first();

        if (! $topupRequest) {
            return redirect()->route('auth.ewallet')->with('error', 'Transaksi tidak ditemukan.');
        }

        if ($topupRequest->status !== TblTopupRequest::STATUS_PENDING) {
            return redirect()->route('auth.ewallet')->with('info', 'Topup sudah diproses sebelumnya.');
        }

        // Simulate successful payment notification
        $notification = [
            'order_id' => $topupRequest->order_no,
            'status' => 'success',
            'transaction_status' => 'settlement',
            'payment_type' => 'manual_test',
            'fraud_status' => 'accept',
        ];

        $result = $this->handleTopupNotification($notification);

        if ($result) {
            return redirect()->route('auth.ewallet')->with('success', 'Topup berhasil diproses! Saldo Anda telah ditambahkan.');
        } else {
            return redirect()->route('auth.ewallet')->with('error', 'Gagal memproses topup. Silakan cek log.');
        }
    }

    /**
     * Webhook handler untuk notifikasi Midtrans (dipanggil dari MidtransWebhookController)
     */
    public function handleTopupNotification(array $notification)
    {
        try {
            DB::beginTransaction();

            $orderNo = $notification['order_id'];
            $status = $notification['status'];

            \Log::info('Processing Topup Notification', [
                'order_no' => $orderNo,
                'status' => $status,
                'transaction_status' => $notification['transaction_status'] ?? null,
                'payment_type' => $notification['payment_type'] ?? null,
            ]);

            // Cari topup request berdasarkan order_no
            $topupRequest = TblTopupRequest::where('order_no', $orderNo)->first();

            if (! $topupRequest) {
                \Log::warning('Topup request not found for order: '.$orderNo);
                DB::rollBack();

                return false;
            }

            \Log::info('Topup Request Found', [
                'id' => $topupRequest->id,
                'member_id' => $topupRequest->member_id,
                'amount' => $topupRequest->amount,
                'current_status' => $topupRequest->status,
            ]);

            // Update status berdasarkan notifikasi
            if ($status === 'success') {
                // Payment berhasil, approve topup
                $topupRequest->update([
                    'status' => TblTopupRequest::STATUS_APPROVED,
                    'approved_at' => Carbon::now(),
                    'admin_note' => 'Auto-approved via Midtrans payment',
                ]);

                \Log::info('Topup Status Updated to Approved', ['topup_id' => $topupRequest->id]);

                // Tambahkan saldo
                $latestTransaction = TblEwalletTransaction::where('member_id', $topupRequest->member_id)
                    ->orderBy('created_on', 'desc')
                    ->lockForUpdate()
                    ->first();

                $currentBalance = $latestTransaction ? $latestTransaction->balance : 0;
                $newBalance = $currentBalance + $topupRequest->amount;

                \Log::info('Creating E-Wallet Transaction', [
                    'member_id' => $topupRequest->member_id,
                    'current_balance' => $currentBalance,
                    'credit_amount' => $topupRequest->amount,
                    'new_balance' => $newBalance,
                ]);

                $transaction = TblEwalletTransaction::create([
                    'member_id' => $topupRequest->member_id,
                    'type' => 'topup',
                    'credit' => $topupRequest->amount,
                    'debit' => 0,
                    'balance' => $newBalance,
                    'note' => 'Topup via Midtrans ('.($notification['payment_type'] ?? 'unknown').') - Order: '.$orderNo,
                    'created_on' => Carbon::now(),
                ]);

                \Log::info('E-Wallet Transaction Created Successfully', [
                    'transaction_id' => $transaction->id,
                    'new_balance' => $transaction->balance,
                ]);

            } elseif (in_array($status, ['failed', 'cancelled', 'expired'])) {
                // Payment gagal
                $topupRequest->update([
                    'status' => TblTopupRequest::STATUS_REJECTED,
                    'rejected_at' => Carbon::now(),
                    'admin_note' => 'Payment '.$status.' via Midtrans',
                ]);

                \Log::info('Topup Status Updated to Rejected', [
                    'topup_id' => $topupRequest->id,
                    'reason' => $status,
                ]);
            }

            DB::commit();
            \Log::info('Topup Notification Processing Completed Successfully');

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Topup notification error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Tampilkan form withdrawal
     */
    public function showWithdrawalForm()
    {
        $customer = Auth::guard('customer')->user();

        // Ambil saldo terkini
        $latestTransaction = TblEwalletTransaction::where('member_id', $customer->id)
            ->orderBy('created_on', 'desc')
            ->first();
        $currentBalance = $latestTransaction ? $latestTransaction->balance : 0;

        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'E-Wallet', 'href' => route('auth.ewallet')],
            ['label' => 'Penarikan', 'href' => null],
        ];

        return view('pages.auth.profile.withdrawal_form', [
            'customer' => $customer,
            'breadcrumbs' => $breadcrumbs,
            'currentBalance' => $currentBalance,
        ]);
    }

    /**
     * Proses request withdrawal
     */
    public function submitWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000', // Minimal withdrawal 100rb
            'withdrawal_method' => 'required|in:transfer_bank,ewallet',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ]);

        $customer = Auth::guard('customer')->user();

        try {
            DB::beginTransaction();

            // Cek saldo tersedia
            $latestTransaction = TblEwalletTransaction::where('member_id', $customer->id)
                ->orderBy('created_on', 'desc')
                ->lockForUpdate()
                ->first();

            $currentBalance = $latestTransaction ? $latestTransaction->balance : 0;

            if ($currentBalance < $request->amount) {
                return back()->withInput()->with('error', 'Saldo tidak mencukupi untuk penarikan.');
            }

            // Simpan request withdrawal
            $withdrawalRequest = TblWithdrawalRequest::create([
                'member_id' => $customer->id,
                'amount' => $request->amount,
                'withdrawal_method' => $request->withdrawal_method,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'status' => TblWithdrawalRequest::STATUS_PENDING,
            ]);

            // Hold saldo (buat transaksi debit pending)
            $newBalance = $currentBalance - $request->amount;

            TblEwalletTransaction::create([
                'member_id' => $customer->id,
                'type' => true,
                'debit' => $request->amount,
                'credit' => 0,
                'balance' => $newBalance,
                'note' => 'Penarikan dana pending - Request ID: '.$withdrawalRequest->id,
                'created_on' => Carbon::now(),
            ]);

            DB::commit();

            return redirect()->route('auth.ewallet', ['type' => 'withdrawal'])
                ->with('success', 'Request penarikan berhasil diajukan. Menunggu proses admin.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal mengajukan penarikan: '.$e->getMessage());
        }
    }

    /**
     * Approve topup request (untuk admin)
     */
    public function approveTopup(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $topupRequest = TblTopupRequest::findOrFail($id);

            if ($topupRequest->status !== TblTopupRequest::STATUS_PENDING) {
                return back()->with('error', 'Request topup sudah diproses sebelumnya.');
            }

            // Update status topup
            $topupRequest->update([
                'status' => TblTopupRequest::STATUS_APPROVED,
                'approved_by' => Auth::guard('customer')->id(),
                'approved_at' => Carbon::now(),
                'admin_note' => $request->admin_note,
            ]);

            // Ambil saldo terakhir
            $latestTransaction = TblEwalletTransaction::where('member_id', $topupRequest->member_id)
                ->orderBy('created_on', 'desc')
                ->lockForUpdate()
                ->first();

            $currentBalance = $latestTransaction ? $latestTransaction->balance : 0;
            $newBalance = $currentBalance + $topupRequest->amount;

            // Buat transaksi kredit
            TblEwalletTransaction::create([
                'member_id' => $topupRequest->member_id,
                'type' => 'topup',
                'credit' => $topupRequest->amount,
                'debit' => 0,
                'balance' => $newBalance,
                'note' => 'Topup via '.$topupRequest->payment_method.' - Request ID: '.$topupRequest->id,
                'created_on' => Carbon::now(),
            ]);

            DB::commit();

            return back()->with('success', 'Topup berhasil diapprove dan saldo telah ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal approve topup: '.$e->getMessage());
        }
    }

    /**
     * Process withdrawal request (untuk admin)
     */
    public function processWithdrawal(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $withdrawalRequest = TblWithdrawalRequest::findOrFail($id);

            if ($withdrawalRequest->status !== TblWithdrawalRequest::STATUS_PENDING) {
                return back()->with('error', 'Request penarikan sudah diproses sebelumnya.');
            }

            // Update status withdrawal
            $withdrawalRequest->update([
                'status' => TblWithdrawalRequest::STATUS_PROCESSED,
                'processed_by' => Auth::guard('customer')->id(),
                'processed_at' => Carbon::now(),
                'admin_note' => $request->admin_note,
            ]);

            // Update transaksi pending menjadi completed
            $pendingTransaction = TblEwalletTransaction::where('member_id', $withdrawalRequest->member_id)
                ->where('type', 'withdrawal_pending')
                ->where('note', 'like', '%Request ID: '.$withdrawalRequest->id)
                ->first();

            if ($pendingTransaction) {
                $pendingTransaction->update([
                    'type' => 'withdrawal',
                    'note' => 'Penarikan dana diproses - Request ID: '.$withdrawalRequest->id,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Penarikan berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses penarikan: '.$e->getMessage());
        }
    }
}
