<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct\Order;
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

            // Find the order
            $order = Order::where('order_no', $notification['order_id'])->first();

            if (!$order) {
                Log::error('Order not found for notification', ['order_id' => $notification['order_id']]);
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
                $order->update([
                    'status' => 'confirmed',
                    'notes' => json_encode($currentNotes)
                ]);

                // You can add email notification or other actions here
                Log::info('Order payment confirmed', ['order_id' => $order->order_no]);
                break;

            case 'pending':
                $order->update([
                    'status' => 'pending',
                    'notes' => json_encode($currentNotes)
                ]);

                Log::info('Order payment pending', ['order_id' => $order->order_no]);
                break;

            case 'failed':
            case 'cancelled':
            case 'expired':
                $order->update([
                    'status' => 'cancelled',
                    'notes' => json_encode($currentNotes)
                ]);

                Log::info('Order payment failed/cancelled/expired', [
                    'order_id' => $order->order_no,
                    'reason' => $notification['status']
                ]);
                break;

            case 'challenge':
                $order->update([
                    'status' => 'pending',
                    'notes' => json_encode($currentNotes)
                ]);

                Log::warning('Order payment challenged by FDS', ['order_id' => $order->order_no]);
                break;

            default:
                Log::warning('Unknown payment status received', [
                    'order_id' => $order->order_no,
                    'status' => $notification['status']
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
