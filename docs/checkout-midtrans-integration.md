# CheckoutController Midtrans Integration

## Overview
CheckoutController telah diperbarui untuk mengintegrasikan Midtrans payment gateway sebagai solusi pembayaran utama untuk semua metode pembayaran non-COD.

## Key Features

### 1. Payment Method Support
- **COD (Cash on Delivery)**: Direct order confirmation
- **Bank Transfer**: Via Midtrans Virtual Account
- **Credit Card**: Via Midtrans Credit Card processing
- **E-Wallet**: Via Midtrans (GoPay, ShopeePay, QRIS)
- **Midtrans**: All-in-one Midtrans Snap integration

### 2. AJAX-First Approach
- Full AJAX support untuk checkout process
- JSON responses untuk semua scenarios
- Proper error handling dengan user-friendly messages
- External redirect support untuk Midtrans Snap

## Implementation Details

### 1. Service Injection
```php
public function place(Request $request, CartService $cartService, MidtransGateway $gateway)
```

MidtransGateway service diinjeksi ke controller untuk menangani payment processing.

### 2. Payment Method Validation
```php
'payment_method' => ['required', Rule::in(['bank_transfer','credit_card','e_wallet','cod','midtrans'])],
```

Validation rules updated untuk mendukung semua metode pembayaran termasuk generic 'midtrans'.

### 3. COD Handling
```php
if ($validated['payment_method'] === 'cod') {
    // Clear cart
    // Update order status to 'confirmed'
    // Return success response
}
```

COD tetap ditangani secara langsung tanpa payment gateway.

### 4. Midtrans Integration
```php
$payment = $gateway->createPayment($order, $validated['payment_method']);
```

Semua metode pembayaran non-COD diarahkan ke Midtrans Snap untuk processing.

### 5. Order Notes Enhancement
```php
$notes = [
    'gateway' => 'midtrans',
    'snap_token' => $payment['token'] ?? null,
    'redirect_url' => $payment['redirect_url'] ?? null,
    'payment_method' => $validated['payment_method'],
    'created_at' => now()->toISOString(),
];
```

Order notes disimpan dalam format JSON dengan informasi payment gateway lengkap.

## Response Formats

### 1. COD Success Response
```json
{
    "success": true,
    "message": "Pesanan berhasil dibuat dengan metode COD.",
    "redirect_url": "/checkout/thankyou/123",
    "order": {
        "id": 123,
        "order_no": "ORD-20231001-ABC123",
        "status": "confirmed",
        "grand_total": 100000
    }
}
```

### 2. Midtrans Payment Response
```json
{
    "success": true,
    "message": "Pembayaran berhasil dibuat. Anda akan diarahkan ke halaman pembayaran Midtrans.",
    "redirect_url": "https://app.midtrans.com/snap/v1/transactions/xxx",
    "external_redirect": true,
    "order": {
        "id": 123,
        "order_no": "ORD-20231001-ABC123",
        "status": "pending",
        "grand_total": 100000
    },
    "payment": {
        "gateway": "midtrans",
        "method": "credit_card",
        "snap_token": "xxx-xxx-xxx",
        "redirect_url": "https://app.midtrans.com/snap/v1/transactions/xxx"
    }
}
```

### 3. Error Response
```json
{
    "success": false,
    "message": "Gagal memproses pembayaran. Silakan coba lagi atau pilih metode pembayaran lain.",
    "errors": {
        "payment": ["Gagal memproses pembayaran melalui Midtrans."]
    },
    "error_details": "Connection timeout", // only in debug mode
    "order": {
        "id": 123,
        "order_no": "ORD-20231001-ABC123",
        "status": "pending"
    }
}
```

## New Methods Added

### 1. Enhanced thankyou()
```php
public function thankyou(Order $order)
{
    // Load relationships untuk display
    $order->load(['items.product', 'shippingAddress', 'customer']);
    
    return view('pages.checkout.thankyou', compact('order'));
}
```

### 2. paymentStatus()
```php
public function paymentStatus(Request $request, Order $order)
{
    // Return order and payment status in JSON format
    // Useful for checking payment progress via AJAX
}
```

## Error Handling Improvements

### 1. Enhanced Logging
```php
\Log::error('Midtrans Payment Error', [
    'order_id' => $order->id ?? null,
    'order_no' => $order->order_no ?? null,
    'payment_method' => $validated['payment_method'],
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

### 2. User-Friendly Messages
- Pesan error yang jelas dan actionable
- Fallback options untuk user
- Context preservation untuk retry

### 3. Development vs Production
- Debug information hanya ditampilkan di development
- Proper error reporting ke logs
- Graceful fallback handling

## Cart Management

### 1. Transaction Safety
Cart clearing hanya dilakukan setelah payment berhasil dibuat atau order confirmed (COD).

### 2. Consistent State
```php
$cart->items()->delete();
$cart->update([
    'subtotal_amount' => 0,
    'discount_amount' => 0,
    'shipping_amount' => 0,
    'tax_amount' => 0,
    'grand_total' => 0,
]);
```

## Integration Points

### 1. MidtransGateway Service
- `createPayment(Order $order, string $method)`: Create Snap transaction
- Returns: `['token' => '...', 'redirect_url' => '...']`

### 2. Frontend Integration
- External redirect handling for Snap
- AJAX response processing
- Error state management

### 3. Webhook Integration
- Order status updates via MidtransWebhookController
- Automatic payment confirmation
- Status synchronization

## Routes Added

### 1. Payment Status Check
```php
Route::get('/order/{order}/payment-status', [CheckoutController::class, 'paymentStatus'])
    ->name('order.payment.status');
```

Untuk checking payment progress via AJAX.

## Testing Scenarios

### 1. COD Flow
1. Select COD payment method
2. Submit checkout form
3. Verify order status = 'confirmed'
4. Verify cart cleared
5. Verify redirect to thank you page

### 2. Midtrans Flow
1. Select any non-COD payment method
2. Submit checkout form
3. Verify Midtrans payment created
4. Verify order status = 'pending'
5. Verify cart cleared
6. Verify redirect to Midtrans Snap
7. Complete payment in Midtrans
8. Verify webhook updates order status

### 3. Error Handling
1. Invalid payment data
2. Midtrans connection failure
3. Order creation failure
4. Cart empty scenarios

## Security Considerations

### 1. Payment Verification
- Order amount validation
- Payment method verification
- Transaction signature checking (handled by MidtransGateway)

### 2. Cart Protection
- Session-based cart for guests
- User-based cart for authenticated users
- Cart locking during checkout

### 3. Error Information
- Sensitive information filtered in production
- Debug details only in development
- Proper error logging for monitoring

## Performance Optimizations

### 1. Database Transactions
- Order creation wrapped in DB transaction
- Atomic cart clearing operations
- Proper rollback on failures

### 2. Efficient Loading
- Eager loading relationships in thankyou page
- Optimized queries for order data
- Minimal data transfer in AJAX responses

## Monitoring & Maintenance

### 1. Log Analysis
```bash
# Monitor payment errors
grep "Midtrans Payment Error" storage/logs/laravel.log

# Check successful payments
grep "Pembayaran berhasil dibuat" storage/logs/laravel.log
```

### 2. Metrics to Track
- COD vs Midtrans conversion rates
- Payment method preferences
- Error rates by payment method
- Checkout abandonment points

## Future Enhancements

### 1. Payment Method Expansion
- Support untuk payment methods baru
- Dynamic payment method configuration
- Regional payment preferences

### 2. Enhanced Error Recovery
- Automatic retry mechanisms
- Payment method fallbacks
- Cart recovery options

### 3. Analytics Integration
- Conversion tracking
- Payment funnel analysis
- User behavior insights

## Conclusion

CheckoutController sekarang fully integrated dengan Midtrans payment gateway, memberikan:
- ✅ Seamless payment experience
- ✅ Comprehensive error handling
- ✅ Full AJAX support
- ✅ Robust logging and monitoring
- ✅ Secure transaction processing
- ✅ User-friendly error messages

Integration ini ready untuk production dengan comprehensive testing dan monitoring capabilities.
