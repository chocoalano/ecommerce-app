# Fix: Undefined array key "payment_method" Error

## Error Description
```
Undefined array key "payment_method"
```

## Root Cause Analysis
Error terjadi karena validation rule untuk `payment_method` hilang dari validator, sehingga ketika kode mencoba mengakses `$validated['payment_method']`, key tersebut tidak ada dalam array validated.

## Issues Found & Fixed

### 1. Missing Validation Rule
**Location:** `app/Http/Controllers/CheckoutController.php` - place() method

**Problem:** Validation rule untuk `payment_method` tidak ada dalam validator.
```php
$validator = \Validator::make($request->all(), [
    'first_name'      => ['required','string','max:100'],
    'last_name'       => ['required','string','max:100'],
    // ... other rules
    // 'payment_method' rule MISSING
]);
```

**Solution:** Menambahkan validation rule untuk `payment_method`:
```php
$validator = \Validator::make($request->all(), [
    'first_name'      => ['required','string','max:100'],
    'last_name'       => ['required','string','max:100'],
    'phone'           => ['required','string','max:30'],
    'address'         => ['required','string','max:500'],
    'city'            => ['required','string','max:100'],
    'province'        => ['required','string','max:100'],
    'zip'             => ['required','string','max:20'],
    'payment_method'  => ['required', Rule::in(['bank_transfer','credit_card','e_wallet','cod','midtrans'])],
    'billing_same'    => ['sometimes','boolean'],
]);
```

### 2. Missing COD Handling Logic
**Problem:** Kode tidak memiliki kondisi untuk menangani payment method COD secara terpisah.

**Solution:** Menambahkan kondisi COD sebelum Midtrans processing:
```php
// 3) Jika COD: langsung konfirmasi dan kosongkan cart
if ($validated['payment_method'] === 'cod') {
    $cart->items()->delete();
    $cart->update([
        'subtotal_amount' => 0,
        'discount_amount' => 0,
        'shipping_amount' => 0,
        'tax_amount'      => 0,
        'grand_total'     => 0,
    ]);

    $order->update(['status' => 'confirmed', 'notes' => json_encode(['payment' => 'COD'])]);

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat dengan metode COD.',
            'redirect_url' => route('checkout.thankyou', $order),
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'status' => $order->status,
                'grand_total' => $order->grand_total
            ]
        ]);
    }

    return redirect()->route('checkout.thankyou', $order);
}
```

### 3. Incorrect Parameter to MidtransGateway
**Problem:** Hardcoded 'midtrans' parameter instead of using actual payment method.
```php
$payment = $gateway->createPayment($order, 'midtrans');
```

**Solution:** Menggunakan payment method yang sebenarnya dipilih user:
```php
$payment = $gateway->createPayment($order, $validated['payment_method']);
```

## Code Flow After Fix

### 1. Validation
```php
// Validates all required fields including payment_method
$validator = \Validator::make($request->all(), [
    // ... address fields
    'payment_method'  => ['required', Rule::in(['bank_transfer','credit_card','e_wallet','cod','midtrans'])],
]);
```

### 2. Order Creation
```php
// Order created with validated payment method
$order = Order::create([
    // ... other fields
    'payment_method' => $validated['payment_method'],
]);
```

### 3. Payment Processing Branch
```php
// Branch 1: COD
if ($validated['payment_method'] === 'cod') {
    // Direct confirmation, clear cart, redirect to thank you
}

// Branch 2: Non-COD (Midtrans)
else {
    // Create Midtrans payment with specific method
    $payment = $gateway->createPayment($order, $validated['payment_method']);
    // Handle Snap redirect
}
```

## Supported Payment Methods

1. **COD (Cash on Delivery)**
   - Direct order confirmation
   - No payment gateway integration
   - Status immediately set to 'confirmed'

2. **Bank Transfer**
   - Processed via Midtrans
   - Enables Virtual Account options (BCA, BNI, BRI, Permata)

3. **Credit Card**
   - Processed via Midtrans
   - Supports Visa, Mastercard, JCB

4. **E-Wallet**
   - Processed via Midtrans
   - Supports GoPay, ShopeePay, QRIS

5. **Midtrans (All-in-one)**
   - Full Midtrans Snap experience
   - User chooses method in Snap interface

## Testing Verification

### 1. Test Each Payment Method
```bash
# Test COD
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "payment_method=cod&first_name=Test&last_name=User&..."

# Test Credit Card
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "payment_method=credit_card&first_name=Test&last_name=User&..."
```

### 2. Test Validation
```bash
# Test missing payment_method
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "first_name=Test&last_name=User&..."
# Should return validation error

# Test invalid payment_method
curl -X POST http://localhost:8000/checkout \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "payment_method=invalid&first_name=Test&last_name=User&..."
# Should return validation error
```

## Error Prevention

### 1. Form Validation
Ensure frontend form always includes payment_method:
```html
<input type="radio" name="payment_method" value="cod" required>
<input type="radio" name="payment_method" value="credit_card" required>
<!-- etc -->
```

### 2. Default Value (Optional)
If needed, set default payment method:
```php
'payment_method' => ['required', Rule::in([...]), 'default' => 'midtrans'],
```

### 3. Backend Validation
Always validate required fields before accessing them:
```php
if (!isset($validated['payment_method'])) {
    throw new \Exception('Payment method is required');
}
```

## Files Modified

1. **app/Http/Controllers/CheckoutController.php**
   - Added `payment_method` validation rule
   - Added COD handling logic
   - Fixed MidtransGateway parameter

## Status
✅ **RESOLVED** - payment_method validation and handling now works correctly for all supported payment methods.

## Additional Notes

- COD orders are immediately confirmed without payment gateway
- All other payment methods go through Midtrans Snap
- Proper error handling for invalid payment methods
- AJAX and regular form submission both supported
- Cart is properly cleared after successful order creation
