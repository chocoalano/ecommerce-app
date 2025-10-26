<?php

namespace App\Services;

use App\Models\OrderProduct\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransGateway
{
    public function __construct()
    {
        // Set your Merchant Server Key
        Config::$serverKey = config('services.midtrans.server_key');

        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = config('services.midtrans.is_production', false);

        // Set sanitization on (default)
        Config::$isSanitized = true;

        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;
    }

    /**
     * Buat transaksi Snap.
     * $method: bank_transfer | credit_card | e_wallet
     */
    public function createPayment(Order $order, string $method): array
    {
        try {
            // item_details dari order items
            $items = $order->items()->get()->map(function ($it) {
                return [
                    'id'       => (string) ($it->sku ?? ($it->variant_id ?? $it->product_id)),
                    'price'    => (int) round((float) $it->unit_price),
                    'quantity' => (int) $it->qty,
                    'name'     => (string) ($it->name ?? 'Item'),
                ];
            })->values()->all();

            if ($order->shipping_amount > 0) {
                $items[] = [
                    'id' => 'shipping',
                    'price' => (int) round((float) $order->shipping_amount),
                    'quantity' => 1,
                    'name' => 'Shipping'
                ];
            }

            if ($order->tax_amount > 0) {
                $items[] = [
                    'id' => 'tax',
                    'price' => (int) round((float) $order->tax_amount),
                    'quantity' => 1,
                    'name' => 'Tax'
                ];
            }

            if ($order->discount_amount > 0) {
                $items[] = [
                    'id' => 'discount',
                    'price' => (int) -abs(round((float) $order->discount_amount)),
                    'quantity' => 1,
                    'name' => 'Discount'
                ];
            }

            // Required
            $transaction_details = [
                'order_id' => $order->order_no,
                'gross_amount' => (int) round((float) $order->grand_total), // no decimal allowed for gross_amount
            ];

            // Optional
            $customer_details = [
                'first_name'    => $order->shippingAddress->first_name ?? '',
                'last_name'     => $order->shippingAddress->last_name ?? '',
                'email'         => $order->customer->email ?? '',
                'phone'         => $order->shippingAddress->phone ?? '',
            ];

            // Optional
            $shipping_address = [
                'first_name'   => $order->shippingAddress->first_name ?? '',
                'last_name'    => $order->shippingAddress->last_name ?? '',
                'address'      => $order->shippingAddress->address ?? '',
                'city'         => $order->shippingAddress->city ?? '',
                'postal_code'  => $order->shippingAddress->postal_code ?? '',
                'phone'        => $order->shippingAddress->phone ?? '',
                'country_code' => 'IDN'
            ];

            $customer_details['shipping_address'] = $shipping_address;
            $customer_details['billing_address'] = $shipping_address;

            // Fill transaction details
            $transaction = [
                'transaction_details' => $transaction_details,
                'customer_details'    => $customer_details,
                'item_details'        => $items,
                'enabled_payments'    => $this->enabledPaymentsFromMethod($method),
                'callbacks'           => [
                    'finish' => route('checkout.thankyou', $order),
                ]
            ];

            $snapToken = Snap::getSnapToken($transaction);
            $snapUrl = Snap::createTransaction($transaction)->redirect_url;

            return [
                'token'        => $snapToken,
                'redirect_url' => $snapUrl,
            ];

        } catch (\Exception $e) {
            \Log::error('Midtrans Payment Creation Error: ' . $e->getMessage());
            throw new \Exception('Failed to create payment: ' . $e->getMessage());
        }
    }

    protected function enabledPaymentsFromMethod(string $method): array
    {
        return match ($method) {
            'bank_transfer' => ['bca_va','bni_va','bri_va','permata_va','other_va'],
            'credit_card'   => ['credit_card'],
            'e_wallet'      => ['gopay','shopeepay','qris'],
            default         => ['credit_card','bca_va','bni_va','bri_va','permata_va','gopay','shopeepay','qris'],
        };
    }

    /**
     * Handle notification callback from Midtrans
     */
    public function handleNotification(): array
    {
        try {
            $notification = new Notification();

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $order_id = $notification->order_id;
            $fraud = $notification->fraud_status;

            $status = null;

            if ($transaction == 'capture') {
                // For credit card transaction, we need to check whether transaction is challenge by FDS or not
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $status = 'challenge';
                    } else {
                        $status = 'success';
                    }
                }
            } else if ($transaction == 'settlement') {
                $status = 'success';
            } else if ($transaction == 'pending') {
                $status = 'pending';
            } else if ($transaction == 'deny') {
                $status = 'failed';
            } else if ($transaction == 'expire') {
                $status = 'expired';
            } else if ($transaction == 'cancel') {
                $status = 'cancelled';
            }

            return [
                'order_id' => $order_id,
                'status' => $status,
                'transaction_status' => $transaction,
                'payment_type' => $type,
                'fraud_status' => $fraud,
                'gross_amount' => $notification->gross_amount ?? null,
                'signature_key' => $notification->signature_key ?? null,
                'raw_notification' => $notification
            ];

        } catch (\Exception $e) {
            \Log::error('Midtrans Notification Error: ' . $e->getMessage());
            throw new \Exception('Failed to process notification: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $raw = \Midtrans\Transaction::status($orderId);

            // Normalize response to an object so properties can be accessed safely.
            $status = is_array($raw) ? json_decode(json_encode($raw)) : $raw;

            return [
                'order_id' => $status->order_id ?? null,
                'transaction_status' => $status->transaction_status ?? null,
                'payment_type' => $status->payment_type ?? null,
                'gross_amount' => $status->gross_amount ?? null,
                'transaction_time' => $status->transaction_time ?? null,
                'settlement_time' => $status->settlement_time ?? null,
                'fraud_status' => $status->fraud_status ?? null,
                'status_code' => $status->status_code ?? null,
                'raw_response' => $raw
            ];

        } catch (\Exception $e) {
            \Log::error('Midtrans Get Status Error: ' . $e->getMessage());
            throw new \Exception('Failed to get transaction status: ' . $e->getMessage());
        }
    }
}
