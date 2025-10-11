# Fix: Order Model Method Error Resolution

## Error Description
```
Failed to create payment: Call to undefined method App\Models\OrderProduct\Order::orderItems()
```

## Root Cause Analysis
Error terjadi karena inconsistency antara method name yang dipanggil di MidtransGateway service dan relationship yang sebenarnya ada di model Order.

## Issues Found & Fixed

### 1. Method Name Mismatch
**Location:** `app/Services/MidtransGateway.php` line 35

**Problem:**
```php
$items = $order->orderItems()->get()->map(function ($it) {
```

**Solution:**
```php
$items = $order->items()->get()->map(function ($it) {
```

**Explanation:** Model Order memiliki relationship `items()` untuk mengakses OrderItem, bukan `orderItems()`.

### 2. Relationship Loading Inconsistency
**Location:** `app/Http/Controllers/CheckoutController.php` line 237

**Problem:**
```php
$order->load(['orderItems.product', 'shippingAddress', 'customer']);
```

**Solution:**
```php
$order->load(['items.product', 'shippingAddress', 'customer']);
```

### 3. Missing payment_method in Fillable
**Location:** `app/Models/OrderProduct/Order.php`

**Problem:** Field `payment_method` tidak ada di `$fillable` array.

**Solution:** Menambahkan `payment_method` ke fillable array:
```php
protected $fillable = [
    'order_no','customer_id','currency','status',
    'subtotal_amount','discount_amount','shipping_amount','tax_amount','grand_total',
    'shipping_address_id','billing_address_id','applied_promos','payment_method','notes','placed_at',
];
```

### 4. Hardcoded Payment Method
**Location:** `app/Http/Controllers/CheckoutController.php` line 122

**Problem:**
```php
'payment_method' => 'midtrans',
```

**Solution:**
```php
'payment_method' => $validated['payment_method'],
```

**Explanation:** Payment method harus menggunakan nilai dari form input, bukan hardcoded.

## Model Relationships Verification

### Order Model
```php
public function items() { return $this->hasMany(OrderItem::class); }
```

### OrderItem Model
```php
public function order() { return $this->belongsTo(Order::class); }
public function product() { return $this->belongsTo(Product::class); }
```

## Testing Verification

### 1. Test Payment Creation
```php
// Test that payment creation works
$order = Order::factory()->create();
$gateway = new MidtransGateway();
$payment = $gateway->createPayment($order, 'credit_card');
```

### 2. Test Relationship Loading
```php
// Test that relationships load correctly
$order = Order::with('items.product')->find(1);
```

## Files Modified

1. **app/Services/MidtransGateway.php**
   - Fixed method call from `orderItems()` to `items()`

2. **app/Http/Controllers/CheckoutController.php**
   - Fixed relationship loading from `orderItems.product` to `items.product`
   - Fixed hardcoded payment method to use validated input

3. **app/Models/OrderProduct/Order.php**
   - Added `payment_method` to fillable array

4. **docs/checkout-midtrans-integration.md**
   - Updated documentation to reflect correct relationship name

## Prevention Measures

### 1. Code Review Checklist
- [ ] Verify relationship names match between models and usage
- [ ] Ensure all fields used in create/update are in fillable array
- [ ] Check for hardcoded values that should be dynamic

### 2. Testing Strategy
- [ ] Unit tests for model relationships
- [ ] Integration tests for payment flow
- [ ] Validation tests for required fields

### 3. Documentation Standards
- [ ] Keep model relationship documentation up to date
- [ ] Document all fillable fields and their purposes
- [ ] Maintain consistent naming conventions

## Verification Commands

```bash
# Test the fix by running
php artisan tinker

# In tinker:
$order = App\Models\OrderProduct\Order::first();
$order->items; // Should return collection of OrderItem

# Test payment creation
$gateway = new App\Services\MidtransGateway();
$payment = $gateway->createPayment($order, 'credit_card');
```

## Status
✅ **RESOLVED** - All relationship inconsistencies fixed and payment creation should now work correctly.
