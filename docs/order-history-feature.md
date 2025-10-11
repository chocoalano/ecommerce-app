# Order History User Feature Documentation

## Overview
Fitur order history memungkinkan customer untuk melihat, mengelola, dan melacak riwayat pesanan mereka. Fitur ini terintegrasi penuh dengan sistem checkout dan payment gateway Midtrans.

## Features Implemented

### 1. Order History List
- **Route**: `/auth/orders`
- **Method**: `GET`
- **Controller**: `AuthController@orders`
- **View**: `pages.auth.orders`

#### Features:
- Pagination (10 orders per page)
- Filter berdasarkan status order
- Filter berdasarkan rentang tanggal
- Search berdasarkan order number
- Status badges dengan color coding
- Empty state untuk user yang belum pernah order

#### Query Parameters:
- `status`: Filter berdasarkan status (pending, confirmed, processing, shipped, completed, cancelled)
- `date_from`: Filter tanggal mulai (YYYY-MM-DD)
- `date_to`: Filter tanggal akhir (YYYY-MM-DD)
- `search`: Search berdasarkan order number

### 2. Order Detail
- **Route**: `/auth/orders/{order}`
- **Method**: `GET`
- **Controller**: `AuthController@orderDetail`
- **View**: `pages.auth.order-detail`

#### Features:
- Detail lengkap order items dengan gambar
- Informasi alamat pengiriman
- Ringkasan pembayaran
- Timeline order status
- Informasi payment gateway (Midtrans)
- Action buttons berdasarkan status

### 3. Cancel Order
- **Route**: `/auth/orders/{order}/cancel`
- **Method**: `POST`
- **Controller**: `AuthController@cancelOrder`

#### Features:
- Hanya bisa cancel order dengan status `pending` atau `confirmed`
- AJAX support dengan confirmation modal
- Audit trail dengan timestamp dan reason

## Controller Implementation

### AuthController Methods

#### 1. orders(Request $request)
```php
public function orders(Request $request)
{
    $customer = Auth::guard('customer')->user();
    
    // Query dengan filter dan pagination
    $query = Order::where('customer_id', $customer->id)
        ->with(['items.product', 'shippingAddress'])
        ->orderBy('created_at', 'desc');

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // ... other filters
    
    $orders = $query->paginate(10)->withQueryString();
    
    return view('pages.auth.orders', compact('orders', 'statuses'));
}
```

#### 2. orderDetail(Request $request, Order $order)
```php
public function orderDetail(Request $request, Order $order)
{
    // Security check - pastikan order milik customer
    if ($order->customer_id !== $customer->id) {
        abort(404);
    }
    
    // Load relationships
    $order->load(['items.product', 'shippingAddress', 'billingAddress']);
    
    // Parse payment notes
    $orderNotes = json_decode($order->notes, true) ?? [];
    
    return view('pages.auth.order-detail', compact('order', 'orderNotes'));
}
```

#### 3. cancelOrder(Request $request, Order $order)
```php
public function cancelOrder(Request $request, Order $order)
{
    // Security & business logic checks
    if ($order->customer_id !== $customer->id) {
        abort(404);
    }
    
    if (!in_array($order->status, ['pending', 'confirmed'])) {
        return response()->json(['success' => false, 'message' => '...'], 422);
    }
    
    // Update order status with audit trail
    $order->update([
        'status' => 'cancelled',
        'notes' => json_encode([...])
    ]);
    
    return response()->json(['success' => true, 'message' => '...']);
}
```

## Route Configuration

```php
// routes/web.php
Route::middleware(['customer'])->group(function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        // Order History Routes
        Route::get('/orders', [AuthController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AuthController::class, 'orderDetail'])->name('order.detail');
        Route::post('/orders/{order}/cancel', [AuthController::class, 'cancelOrder'])->name('order.cancel');
    });
});
```

## View Implementation

### 1. Order List View (orders.blade.php)

#### Key Components:
- **Breadcrumb Navigation**
- **Filter Controls**: Status dropdown, date range picker
- **Search Bar**: Order number search dengan reset functionality
- **Order Cards**: Compact view dengan key information
- **Status Badges**: Color-coded berdasarkan status
- **Action Buttons**: View detail, cancel order
- **Pagination**: Laravel pagination dengan query parameters
- **Empty State**: User-friendly message untuk no orders
- **Cancel Modal**: Confirmation dialog dengan AJAX

#### JavaScript Features:
- Dynamic filtering dengan URL parameters
- AJAX cancel order dengan toast notifications
- Responsive design dengan Tailwind CSS

### 2. Order Detail View (order-detail.blade.php)

#### Key Sections:
- **Order Header**: Order number, status, action buttons
- **Order Items**: Product details dengan images
- **Shipping Address**: Complete delivery information
- **Payment Information**: Method dan gateway details
- **Order Summary**: Price breakdown
- **Order Timeline**: Visual status progression
- **Action Buttons**: Context-specific actions

#### JavaScript Features:
- Cancel order functionality
- Responsive layout (3-column grid)
- Modal interactions

## Security Features

### 1. Authentication & Authorization
```php
// Middleware 'customer' required untuk semua order routes
Route::middleware(['customer'])->group(function () {
    // ... order routes
});

// Security check di setiap method
if ($order->customer_id !== Auth::guard('customer')->id()) {
    abort(404);
}
```

### 2. CSRF Protection
```javascript
// AJAX requests include CSRF token
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### 3. Input Validation
- Filter parameters divalidasi
- Order existence check dengan implicit model binding
- Business rule validation (cancel only pending/confirmed orders)

## Integration Points

### 1. Order Model Relationships
```php
// Used relationships
$order->load([
    'items.product',        // Order items dengan product details
    'shippingAddress',      // Alamat pengiriman
    'billingAddress',       // Alamat billing
    'customer'              // Customer information
]);
```

### 2. Payment Gateway Integration
```php
// Parse Midtrans payment notes
$orderNotes = json_decode($order->notes, true) ?? [];

// Display payment gateway information
if (isset($orderNotes['gateway'])) {
    // Show gateway, token, method, etc.
}
```

### 3. Checkout Integration
- Order dibuat dari CheckoutController
- Status tracking dari payment webhooks
- Cancel functionality terintegrasi dengan cart management

## Status Management

### Order Status Flow
1. **pending** → Menunggu pembayaran
2. **confirmed** → Pembayaran dikonfirmasi
3. **processing** → Sedang diproses
4. **shipped** → Sedang dikirim
5. **completed** → Selesai
6. **cancelled** → Dibatalkan

### Status Color Coding
```php
$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-blue-100 text-blue-800',
    'processing' => 'bg-indigo-100 text-indigo-800',
    'shipped' => 'bg-purple-100 text-purple-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
];
```

## AJAX API Responses

### Order List API
```json
{
    "success": true,
    "orders": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "total": 47,
        "per_page": 10
    }
}
```

### Order Detail API
```json
{
    "success": true,
    "order": {
        "id": 123,
        "order_no": "ORD-20231001-ABC123",
        "status": "confirmed",
        "items": [...],
        "shipping_address": {...},
        "notes": {...}
    }
}
```

### Cancel Order API
```json
{
    "success": true,
    "message": "Order berhasil dibatalkan.",
    "order": {
        "id": 123,
        "order_no": "ORD-20231001-ABC123",
        "status": "cancelled"
    }
}
```

## Frontend Assets

### JavaScript Dependencies
- Toast notification system
- AJAX utilities
- Modal management
- URL parameter handling

### CSS Framework
- Tailwind CSS untuk responsive design
- Custom status color classes
- Modal overlay styles

## Testing Scenarios

### 1. Functional Testing
- [ ] View order list dengan various filters
- [ ] Pagination functionality
- [ ] Order detail view
- [ ] Cancel order (allowed statuses)
- [ ] Cancel order (forbidden statuses)
- [ ] Security: Access other customer's orders

### 2. UI/UX Testing
- [ ] Responsive design pada mobile
- [ ] Filter interactions
- [ ] Modal behavior
- [ ] Toast notifications
- [ ] Empty states

### 3. Performance Testing
- [ ] Order list dengan large dataset
- [ ] Image loading optimization
- [ ] Query performance dengan relationships

## Future Enhancements

### 1. Advanced Features
- Order tracking dengan courier API
- Bulk actions (cancel multiple orders)
- Order export (PDF, Excel)
- Review dan rating system

### 2. Notifications
- Email notifications untuk status changes
- SMS notifications untuk shipping updates
- Push notifications untuk mobile app

### 3. Analytics
- Order analytics dashboard
- Customer behavior tracking
- Revenue analytics per customer

## Maintenance

### 1. Regular Tasks
- Monitor order status distribution
- Check for stuck orders
- Review cancellation patterns
- Performance optimization

### 2. Database Optimization
- Index optimization untuk query performance
- Archive old orders
- Clean up orphaned data

## Conclusion

Order history feature memberikan complete customer experience untuk mengelola pesanan mereka. Fitur ini terintegrasi penuh dengan sistem checkout dan payment gateway, memberikan transparency dan control yang dibutuhkan customer modern.

**Key Benefits:**
- ✅ Complete order visibility
- ✅ Self-service order management  
- ✅ Integrated payment tracking
- ✅ Responsive design
- ✅ Security & privacy protection
- ✅ AJAX-powered smooth UX
