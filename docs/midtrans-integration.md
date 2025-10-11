# Midtrans Payment Gateway Integration

## Overview
Integrasi dengan Midtrans payment gateway menggunakan official PHP SDK untuk menangani pembayaran e-commerce dengan aman dan reliabel.

## Installation & Configuration

### 1. Install Midtrans PHP SDK
```bash
composer require midtrans/midtrans-php
```

### 2. Configuration
Edit `config/services.php`:
```php
'midtrans' => [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
],
```

### 3. Environment Variables
Add to `.env`:
```
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

## Service Implementation

### MidtransGateway Service
Location: `app/Services/MidtransGateway.php`

#### Key Methods:

1. **createPayment(Order $order): array**
   - Creates Snap token for payment
   - Generates payment URL
   - Returns token and redirect URL

2. **handleNotification(): array**
   - Processes webhook notifications
   - Maps Midtrans status to internal status
   - Returns normalized notification data

3. **getTransactionStatus(string $orderId): array**
   - Checks current payment status
   - Useful for manual verification
   - Returns detailed transaction data

#### Example Usage:
```php
use App\Services\MidtransGateway;

$midtrans = new MidtransGateway();

// Create payment
$payment = $midtrans->createPayment($order);
$snapToken = $payment['snap_token'];
$redirectUrl = $payment['redirect_url'];

// Handle notification
$notification = $midtrans->handleNotification();

// Check status
$status = $midtrans->getTransactionStatus($order->order_no);
```

## Controller Implementation

### MidtransWebhookController
Location: `app/Http/Controllers/MidtransWebhookController.php`

#### Key Methods:

1. **handleNotification(Request $request)**
   - Endpoint untuk webhook Midtrans
   - Memproses notifikasi pembayaran
   - Update status order otomatis

2. **checkStatus(Request $request, Order $order)**
   - Manual check payment status
   - Berguna untuk admin atau customer
   - Dapat dipanggil via AJAX

#### Order Status Mapping:
- `success` → `confirmed`
- `pending` → `pending`
- `failed/cancelled/expired` → `cancelled`
- `challenge` → `pending` (FDS review)

## Routes Configuration

### Webhook Routes
Location: `routes/midtrans.php`

```php
// Webhook dari Midtrans (tidak perlu auth)
Route::post('/webhook/midtrans/notification', [MidtransWebhookController::class, 'handleNotification'])
    ->name('midtrans.webhook.notification');

// Manual check status (perlu auth)
Route::middleware(['customer'])->group(function () {
    Route::post('/payment/check-status/{order}', [MidtransWebhookController::class, 'checkStatus'])
        ->name('payment.check.status');
});
```

### Webhook URL Configuration
Configure di Midtrans Dashboard:
```
Production: https://yourdomain.com/webhook/midtrans/notification
Sandbox: https://yourdomain.com/webhook/midtrans/notification
```

## Frontend Integration

### Checkout Process
1. User melakukan checkout
2. System creates Snap token via MidtransGateway
3. Redirect user ke Snap payment page
4. User melakukan pembayaran
5. Midtrans mengirim notification ke webhook
6. System update order status otomatis

### JavaScript Integration
```javascript
// Redirect ke payment page
window.location.href = redirectUrl;

// Or embed Snap
snap.pay(snapToken, {
    onSuccess: function(result) {
        // Handle success
    },
    onPending: function(result) {
        // Handle pending
    },
    onError: function(result) {
        // Handle error
    }
});
```

## Security Features

### 1. Notification Verification
- Menggunakan official SDK untuk verifikasi signature
- Validasi source notification dari Midtrans
- Logging semua notification untuk audit

### 2. Order Validation
- Verify order exists before processing
- Check order amount matches payment
- Prevent duplicate processing

### 3. Error Handling
- Comprehensive try-catch blocks
- Detailed logging for debugging
- Graceful fallback for failed operations

## Testing

### Sandbox Testing
1. Use sandbox credentials
2. Test payment scenarios:
   - Successful payment
   - Failed payment
   - Pending payment
   - Cancelled payment

### Test Cards
```
Success: 4811 1111 1111 1114
Failed: 4911 1111 1111 1113
Challenge: 4411 1111 1111 1118
```

## Monitoring & Logging

### Log Locations
- Payment creation: `storage/logs/laravel.log`
- Webhook notifications: `storage/logs/laravel.log`
- Error handling: `storage/logs/laravel.log`

### Log Formats
```
[INFO] Midtrans payment created: order_123, token: xxx
[INFO] Midtrans notification: order_123, status: success
[ERROR] Midtrans error: Invalid signature
```

## Troubleshooting

### Common Issues

1. **Invalid Signature**
   - Check server key configuration
   - Verify notification source
   - Ensure webhook URL is correct

2. **Order Not Found**
   - Verify order_no format
   - Check order exists in database
   - Validate notification data

3. **Payment Amount Mismatch**
   - Check gross_amount calculation
   - Verify item details
   - Ensure no currency conversion issues

### Debug Mode
Enable detailed logging in development:
```php
// In MidtransGateway constructor
Log::debug('Midtrans config loaded', config('services.midtrans'));
```

## Maintenance

### Regular Tasks
1. Monitor webhook logs
2. Check failed payments
3. Validate order status consistency
4. Update SDK when available

### Production Checklist
- [ ] Production credentials configured
- [ ] Webhook URL configured in Midtrans Dashboard
- [ ] SSL certificate active
- [ ] Log monitoring setup
- [ ] Error notification setup
- [ ] Payment reconciliation process

## Support
- Midtrans Documentation: https://docs.midtrans.com/
- PHP SDK: https://github.com/Midtrans/midtrans-php
- Support: support@midtrans.com
