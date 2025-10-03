<?php

namespace App\Services;

use App\Models\Order;
use GuzzleHttp\Client;

class MidtransGateway
{
    protected string $serverKey;
    protected bool $isProduction;
    protected string $baseUrl;

    public function __construct()
    {
        $this->serverKey    = (string) config('services.midtrans.server_key');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->baseUrl      = $this->isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    /**
     * Buat transaksi Snap.
     * $method: bank_transfer | credit_card | e_wallet
     */
    public function createPayment(Order $order, string $method): array
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ],
            'timeout'  => 20,
        ]);

        // item_details dari order_items
        $items = $order->orderItems()->get()->map(function ($it) {
            return [
                'id'       => (string) ($it->sku ?? ($it->variant_id ?? $it->product_id)),
                'price'    => (int) round($it->unit_price),
                'quantity' => (int) $it->qty,
                'name'     => (string) ($it->name ?? 'Item'),
            ];
        })->values()->all();

        if ($order->shipping_amount > 0) {
            $items[] = ['id' => 'shipping', 'price' => (int) round($order->shipping_amount), 'quantity' => 1, 'name' => 'Shipping'];
        }
        if ($order->tax_amount > 0) {
            $items[] = ['id' => 'tax', 'price' => (int) round($order->tax_amount), 'quantity' => 1, 'name' => 'Tax'];
        }
        if ($order->discount_amount > 0) {
            $items[] = ['id' => 'discount', 'price' => (int) -abs(round($order->discount_amount)), 'quantity' => 1, 'name' => 'Discount'];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->order_no,
                'gross_amount' => (int) round($order->grand_total),
            ],
            'item_details'     => $items,
            'enabled_payments' => $this->enabledPaymentsFromMethod($method),
            'callbacks'        => [
                'finish' => route('checkout.thankyou', $order),
            ],
        ];

        $resp = $client->post('/snap/v1/transactions', ['body' => json_encode($payload)]);
        $data = json_decode((string) $resp->getBody(), true);

        return [
            'token'        => $data['token']        ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
        ];
    }

    protected function enabledPaymentsFromMethod(string $method): array
    {
        return match ($method) {
            'bank_transfer' => ['bca_va','bni_va','bri_va','permata_va'],
            'credit_card'   => ['credit_card'],
            'e_wallet'      => ['gopay','shopeepay'],
            default         => ['credit_card','bca_va','bni_va','bri_va','permata_va','gopay','shopeepay'],
        };
    }
}
