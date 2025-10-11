# Testing Midtrans Payment Integration

## Overview
Panduan lengkap untuk testing integrasi Midtrans payment gateway di lingkungan development dan production.

## Prerequisites
- Midtrans Sandbox account
- Official midtrans/midtrans-php package installed
- Local development server running
- ngrok atau tunnel untuk webhook testing (optional)

## Environment Setup

### 1. Sandbox Configuration
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-sandbox-key
MIDTRANS_CLIENT_KEY=SB-Mid-client-sandbox-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

### 2. Development URLs
```
Local: http://localhost:8000
ngrok: https://abc123.ngrok.io (for webhook testing)
```

## Test Scenarios

### 1. Payment Creation Test

#### Test Case: Create Snap Token
```bash
# Test checkout process
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "payment_method": "midtrans",
    "address_id": 1
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "payment_url": "https://app.sandbox.midtrans.com/snap/v1/transactions/xxx",
  "snap_token": "xxx-xxx-xxx"
}
```

#### Test Case: Invalid Order
```bash
# Test dengan order yang tidak valid
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "payment_method": "midtrans",
    "address_id": 999
  }'
```

**Expected Response:**
```json
{
  "success": false,
  "message": "Address not found"
}
```

### 2. Webhook Notification Test

#### Test Case: Successful Payment
```bash
# Simulate successful payment notification
curl -X POST http://localhost:8000/webhook/midtrans/notification \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_time": "2024-01-01 12:00:00",
    "transaction_status": "settlement",
    "transaction_id": "test-transaction-123",
    "status_message": "midtrans payment notification",
    "status_code": "200",
    "signature_key": "valid-signature",
    "payment_type": "credit_card",
    "order_id": "ORDER-123",
    "merchant_id": "your-merchant-id",
    "gross_amount": "100000.00",
    "fraud_status": "accept",
    "currency": "IDR"
  }'
```

**Expected:**
- Order status updated to `confirmed`
- Log entry created
- Response: `{"message": "Notification processed successfully"}`

#### Test Case: Failed Payment
```bash
# Simulate failed payment notification
curl -X POST http://localhost:8000/webhook/midtrans/notification \
  -H "Content-Type: application/json" \
  -d '{
    "transaction_status": "deny",
    "order_id": "ORDER-123",
    "gross_amount": "100000.00"
  }'
```

**Expected:**
- Order status updated to `cancelled`
- Appropriate log entry

### 3. Manual Status Check Test

#### Test Case: Check Payment Status
```bash
# Manual check order status
curl -X POST http://localhost:8000/payment/check-status/1 \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -H "Authorization: Bearer your-token"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Payment status updated successfully",
  "order_status": "confirmed",
  "midtrans_status": {
    "transaction_status": "settlement",
    "order_id": "ORDER-123",
    "gross_amount": "100000.00"
  }
}
```

## Integration Testing

### 1. End-to-End Payment Flow

#### Test Steps:
1. **Add Product to Cart**
   ```javascript
   // Test add to cart AJAX
   fetch('/cart/add', {
     method: 'POST',
     headers: {
       'Content-Type': 'application/json',
       'X-CSRF-TOKEN': csrfToken
     },
     body: JSON.stringify({
       product_id: 1,
       quantity: 2
     })
   })
   ```

2. **Proceed to Checkout**
   ```javascript
   // Test checkout process
   fetch('/checkout', {
     method: 'POST',
     headers: {
       'Content-Type': 'application/json',
       'X-Requested-With': 'XMLHttpRequest'
     },
     body: JSON.stringify({
       payment_method: 'midtrans',
       address_id: 1
     })
   })
   ```

3. **Complete Payment**
   - Use Snap test cards
   - Verify webhook notification
   - Check order status update

### 2. Frontend Payment Testing

#### Test Snap Integration
```html
<!-- Test payment page -->
<!DOCTYPE html>
<html>
<head>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
</head>
<body>
    <button onclick="pay()">Pay Now</button>
    
    <script>
        function pay() {
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    alert('Payment successful!');
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    alert('Payment pending');
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    alert('Payment failed');
                },
                onClose: function() {
                    console.log('Payment popup closed');
                }
            });
        }
    </script>
</body>
</html>
```

## Test Data

### 1. Test Credit Cards

#### Successful Payment
```
Card Number: 4811 1111 1111 1114
Expiry: 12/25
CVV: 123
```

#### Failed Payment
```
Card Number: 4911 1111 1111 1113
Expiry: 12/25
CVV: 123
```

#### Challenge by FDS
```
Card Number: 4411 1111 1111 1118
Expiry: 12/25
CVV: 123
```

### 2. Test Order Data
```php
// Sample order for testing
$testOrder = [
    'order_no' => 'ORDER-TEST-' . time(),
    'total_amount' => 100000,
    'customer_name' => 'Test Customer',
    'customer_email' => 'test@example.com',
    'customer_phone' => '081234567890'
];
```

## Automated Testing

### 1. PHPUnit Tests

#### Test MidtransGateway Service
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\MidtransGateway;
use App\Models\OrderProduct\Order;

class MidtransGatewayTest extends TestCase
{
    protected MidtransGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MidtransGateway();
    }

    public function test_create_payment_returns_snap_token()
    {
        $order = Order::factory()->create();
        
        $result = $this->gateway->createPayment($order);
        
        $this->assertArrayHasKey('snap_token', $result);
        $this->assertArrayHasKey('redirect_url', $result);
    }

    public function test_handle_notification_processes_correctly()
    {
        // Mock notification data
        $notificationData = [
            'order_id' => 'ORDER-123',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept'
        ];

        $result = $this->gateway->handleNotification();
        
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
    }
}
```

#### Test Webhook Controller
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OrderProduct\Order;

class MidtransWebhookTest extends TestCase
{
    public function test_webhook_processes_successful_payment()
    {
        $order = Order::factory()->create([
            'order_no' => 'ORDER-123',
            'status' => 'pending'
        ]);

        $response = $this->postJson('/webhook/midtrans/notification', [
            'order_id' => 'ORDER-123',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept'
        ]);

        $response->assertOk();
        $this->assertEquals('confirmed', $order->fresh()->status);
    }
}
```

### 2. Test Commands

#### Run All Tests
```bash
# Run unit tests
php artisan test --filter=MidtransGatewayTest

# Run feature tests
php artisan test --filter=MidtransWebhookTest

# Run all payment-related tests
php artisan test tests/Feature/PaymentTest.php
```

## Performance Testing

### 1. Load Testing Webhook
```bash
# Using Apache Bench
ab -n 100 -c 10 -T application/json -p notification.json http://localhost:8000/webhook/midtrans/notification

# Using Artillery
artillery quick --count 10 --num 5 http://localhost:8000/webhook/midtrans/notification
```

### 2. Payment Creation Load Test
```javascript
// Using k6
import http from 'k6/http';

export default function () {
  const payload = JSON.stringify({
    payment_method: 'midtrans',
    address_id: 1
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  };

  http.post('http://localhost:8000/checkout', payload, params);
}
```

## Debugging & Monitoring

### 1. Log Analysis
```bash
# Monitor payment logs in real-time
tail -f storage/logs/laravel.log | grep -i midtrans

# Filter webhook notifications
grep "Midtrans Notification" storage/logs/laravel.log

# Check error patterns
grep -i "error" storage/logs/laravel.log | grep -i midtrans
```

### 2. Database Monitoring
```sql
-- Check order status distribution
SELECT status, COUNT(*) as count 
FROM orders 
WHERE payment_method = 'midtrans' 
GROUP BY status;

-- Check recent payment activities
SELECT order_no, status, created_at, updated_at 
FROM orders 
WHERE payment_method = 'midtrans' 
ORDER BY updated_at DESC 
LIMIT 10;
```

## Troubleshooting Common Issues

### 1. Webhook Not Received
**Check:**
- Webhook URL configuration in Midtrans Dashboard
- Firewall settings
- SSL certificate validity
- Server accessibility from internet

**Debug:**
```bash
# Test webhook URL accessibility
curl -X POST https://yourdomain.com/webhook/midtrans/notification \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

### 2. Payment Creation Fails
**Check:**
- Midtrans credentials
- Order amount format
- Required fields completeness
- Network connectivity

**Debug:**
```php
// Add debug logging in MidtransGateway
Log::debug('Creating payment for order', $order->toArray());
Log::debug('Midtrans config', config('services.midtrans'));
```

### 3. Order Status Not Updated
**Check:**
- Order ID format matching
- Notification signature validation
- Database connection
- Transaction rollback issues

**Debug:**
```php
// Add debug in webhook controller
Log::debug('Processing notification', $notification);
Log::debug('Order found', $order ? $order->toArray() : 'Not found');
```

## Production Deployment Checklist

- [ ] Production Midtrans credentials configured
- [ ] Webhook URL registered in Midtrans Dashboard
- [ ] SSL certificate installed and valid
- [ ] Error monitoring setup (Sentry, Bugsnag, etc.)
- [ ] Log rotation configured
- [ ] Database backup strategy
- [ ] Payment reconciliation process
- [ ] Customer support process for payment issues
- [ ] Performance monitoring
- [ ] Security audit completed

## Support Resources

### Midtrans Resources
- [Snap Integration Guide](https://docs.midtrans.com/en/snap/overview)
- [Notification Handling](https://docs.midtrans.com/en/after-payment/http-notification)
- [Testing Guide](https://docs.midtrans.com/en/technical-reference/sandbox-test)

### Internal Resources
- MidtransGateway Service: `app/Services/MidtransGateway.php`
- Webhook Controller: `app/Http/Controllers/MidtransWebhookController.php`
- Configuration: `config/services.php`
- Routes: `routes/midtrans.php`
