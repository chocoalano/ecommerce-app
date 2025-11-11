<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct\Order;
use App\Models\Mlm\TblTopupRequest;
use App\Services\MidtransGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected MidtransGateway $midtrans;

    public function __construct(MidtransGateway $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Handle notification callback from Midtrans
     */
    public function handleNotification(Request $request)
    {
        try {
            // Get notification data from Midtrans
            $notification = $this->midtrans->handleNotification();

            Log::info('Midtrans Notification Received', $notification);

            $orderId = $notification['order_id'];

            // Check if it's a topup order (starts with TOPUP-)
            if (str_starts_with($orderId, 'TOPUP-')) {
                $topup = TblTopupRequest::where('order_no', $orderId)->first();

                if (!$topup) {
                    Log::error('Topup request not found', ['order_id' => $orderId]);
                    return response()->json(['message' => 'Topup request not found'], 404);
                }

                // Call the EwalletController method to handle the topup notification
                app(\App\Http\Controllers\Auth\EwalletController::class)->handleTopupNotification($notification);

                return response()->json(['message' => 'Topup notification processed successfully']);
            }

            // Find the order (regular product order)
            $order = Order::where('order_no', $orderId)->first();

            if (!$order) {
                Log::error('Order not found for notification', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update order status based on notification
            $this->updateOrderStatus($order, $notification);

            return response()->json(['message' => 'Notification processed successfully']);

        } catch (\Exception $e) {
            Log::error('Error processing Midtrans notification: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    /**
     * Update order status based on Midtrans notification
     */
    protected function updateOrderStatus(Order $order, array $notification): void
    {
        $currentNotes = json_decode($order->notes, true) ?? [];

        // Add notification data to order notes
        $currentNotes['midtrans_notifications'][] = [
            'timestamp' => now()->toISOString(),
            'status' => $notification['status'],
            'transaction_status' => $notification['transaction_status'],
            'payment_type' => $notification['payment_type'],
            'fraud_status' => $notification['fraud_status'],
        ];

        switch ($notification['status']) {
            case 'success':
                // Payment berhasil - update status jadi paid
                $order->update([
                    'status' => Order::ST_PAID,
                    'notes' => json_encode($currentNotes),
                    'paid_at' => now(),
                ]);

                Log::info('Order payment successful - Status updated to PAID', [
                    'order_id' => $order->order_no,
                    'order_status' => Order::ST_PAID,
                    'transaction_status' => $notification['transaction_status'],
                    'payment_type' => $notification['payment_type'],
                ]);
                break;

            case 'pending':
                // Payment masih pending - tetap pending
                $order->update([
                    'status' => Order::ST_PENDING,
                    'notes' => json_encode($currentNotes)
                ]);

                Log::info('Order payment pending - Status remains PENDING', [
                    'order_id' => $order->order_no,
                    'transaction_status' => $notification['transaction_status'],
                ]);
                break;

            case 'failed':
            case 'cancelled':
            case 'expired':
                // Payment gagal - tetap pending (bukan cancelled agar user bisa coba lagi)
                $order->update([
                    'status' => Order::ST_PENDING,
                    'notes' => json_encode($currentNotes)
                ]);

                Log::info('Order payment failed/cancelled/expired - Status remains PENDING', [
                    'order_id' => $order->order_no,
                    'reason' => $notification['status'],
                    'transaction_status' => $notification['transaction_status'],
                ]);
                break;

            case 'challenge':
                // Payment di-challenge fraud detection - tetap pending
                $order->update([
                    'status' => Order::ST_PENDING,
                    'notes' => json_encode($currentNotes)
                ]);

                Log::warning('Order payment challenged by FDS - Status remains PENDING', [
                    'order_id' => $order->order_no,
                    'fraud_status' => $notification['fraud_status'],
                ]);
                break;

            default:
                Log::warning('Unknown payment status received', [
                    'order_id' => $order->order_no,
                    'status' => $notification['status'],
                    'transaction_status' => $notification['transaction_status'] ?? 'unknown',
                ]);
        }
    }

    /**
     * Manual check payment status
     */
    public function checkStatus(Request $request, Order $order)
    {
        try {
            $status = $this->midtrans->getTransactionStatus($order->order_no);

            Log::info('Manual payment status check', [
                'order_id' => $order->order_no,
                'status' => $status
            ]);

            // Update order based on current status
            $notification = [
                'order_id' => $status['order_id'],
                'status' => $this->mapTransactionStatus($status['transaction_status']),
                'transaction_status' => $status['transaction_status'],
                'payment_type' => $status['payment_type'],
                'fraud_status' => $status['fraud_status'],
            ];

            $this->updateOrderStatus($order, $notification);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status updated successfully',
                    'order_status' => $order->fresh()->status,
                    'midtrans_status' => $status
                ]);
            }

            return redirect()->back()->with('success', 'Payment status updated successfully');

        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error checking payment status'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error checking payment status');
        }
    }

    /**
     * Map Midtrans transaction status to our status
     */
    protected function mapTransactionStatus(string $transactionStatus): string
    {
        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending'
        };
    }
}
