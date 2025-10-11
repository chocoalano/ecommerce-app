# Cart System Documentation

## Overview

The Cart system is designed to handle shopping cart functionality for both authenticated customers and guest users. It includes robust relationships, calculation methods, and helper services.

## Models Structure

### Cart Model (`App\Models\CartProduct\Cart`)

#### Relationships
- `customer()` - BelongsTo Customer (for authenticated users)
- `items()` - HasMany CartItem (cart items)
- `products()` - BelongsToMany Product (through cart_items pivot)

#### Key Attributes
- `customer_id` - Customer ID (nullable for guest carts)
- `session_id` - Session ID (for guest carts)
- `currency` - Cart currency (default: IDR)
- `subtotal_amount` - Subtotal before discounts
- `discount_amount` - Total discount amount
- `shipping_amount` - Shipping cost
- `tax_amount` - Tax amount
- `grand_total` - Final total amount

#### Calculated Attributes
- `total_quantity` - Total quantity of all items
- `total_items` - Number of unique products
- `total_weight` - Total weight of all items
- `formatted_grand_total` - Formatted grand total (Rp X.XXX)
- `formatted_subtotal` - Formatted subtotal (Rp X.XXX)

#### Key Methods
- `addOrIncrementProduct(Product $product, int $qty, ?float $unitPrice, array $meta)` - Add or update product in cart
- `recalcTotals()` - Recalculate cart totals
- `isEmpty()` - Check if cart is empty
- `hasItems()` - Check if cart has items
- `clearItems()` - Remove all items from cart
- `removeProduct(Product $product)` - Remove specific product
- `updateProductQuantity(Product $product, int $quantity)` - Update product quantity

### CartItem Model (`App\Models\CartProduct\CartItem`)

#### Relationships
- `cart()` - BelongsTo Cart
- `product()` - BelongsTo Product
- `customer()` - HasOneThrough Customer (via Cart)

#### Key Attributes
- `cart_id` - Cart ID
- `product_id` - Product ID
- `qty` - Item quantity
- `unit_price` - Price per unit
- `row_total` - Total for this item (qty × unit_price - discounts)
- `product_sku` - Product SKU snapshot
- `product_name` - Product name snapshot
- `meta_json` - Additional metadata (variants, options, etc.)

#### Calculated Attributes
- `formatted_unit_price` - Formatted unit price (Rp X.XXX)
- `formatted_row_total` - Formatted row total (Rp X.XXX)
- `subtotal` - Subtotal before item discounts
- `discount_amount` - Item-level discount amount
- `discount_percentage` - Discount percentage
- `total_weight` - Total weight for this item

#### Key Methods
- `updateQuantity(int $quantity)` - Update item quantity
- `incrementQuantity(int $amount)` - Increase quantity
- `decrementQuantity(int $amount)` - Decrease quantity
- `hasDiscount()` - Check if item has discount

### Customer Model Enhancements (`App\Models\Auth\Customer`)

#### New Cart Relationships
- `carts()` - HasMany Cart
- `activeCart()` - HasOne Cart (latest)
- `cartItems()` - HasManyThrough CartItem

#### New Cart Attributes
- `cart_items_count` - Total items in active cart
- `formatted_cart_total` - Formatted active cart total

#### New Cart Methods
- `getOrCreateCart()` - Get or create active cart for customer

## CartService (`App\Services\Cart\CartService`)

### Purpose
Centralized service for cart operations, handling both authenticated and guest users.

### Key Methods

#### Cart Management
- `getCurrentCart()` - Get current cart (customer or session-based)
- `getOrCreateCart()` - Get or create cart for current user
- `clearCart()` - Clear current cart

#### Product Operations
- `addProduct(Product $product, int $quantity, array $meta)` - Add product to cart
- `updateProductQuantity(Product $product, int $quantity)` - Update product quantity
- `removeProduct(Product $product)` - Remove product from cart
- `hasProduct(Product $product)` - Check if product is in cart
- `getProductQuantity(Product $product)` - Get product quantity in cart

#### Information & Summary
- `getCartSummary()` - Get complete cart summary for API responses
- `getCartItemsCount()` - Get total items count for display
- `transferGuestCartToCustomer(Customer $customer)` - Transfer guest cart on login

## Usage Examples

### Basic Cart Operations

```php
use App\Services\Cart\CartService;
use App\Models\Product\Product;

$cartService = new CartService();

// Add product to cart
$product = Product::find(1);
$cartItem = $cartService->addProduct($product, 2);

// Update quantity
$cartService->updateProductQuantity($product, 5);

// Remove product
$cartService->removeProduct($product);

// Get cart summary
$summary = $cartService->getCartSummary();
```

### Using Cart Model Directly

```php
use App\Models\CartProduct\Cart;
use App\Models\Product\Product;

$cart = Cart::find(1);
$product = Product::find(1);

// Add product
$cartItem = $cart->addOrIncrementProduct($product, 2);

// Check cart status
echo "Items: " . $cart->total_quantity;
echo "Total: " . $cart->formatted_grand_total;

// Clear cart
$cart->clearItems();
```

### Customer Cart Integration

```php
use App\Models\Auth\Customer;

$customer = Customer::find(1);

// Get customer's cart
$cart = $customer->getOrCreateCart();

// Get cart items count
echo "Cart items: " . $customer->cart_items_count;

// Get formatted total
echo "Total: " . $customer->formatted_cart_total;
```

## API Response Format

### Cart Summary Response

```json
{
  "count": 3,
  "total_quantity": 5,
  "total_items": 3,
  "subtotal": 150000,
  "discount": 15000,
  "shipping": 10000,
  "tax": 15000,
  "grand_total": 160000,
  "formatted_grand_total": "Rp 160.000",
  "items": [
    {
      "id": 1,
      "product_id": 101,
      "product_name": "Product A",
      "product_sku": "SKU-001",
      "quantity": 2,
      "unit_price": 50000,
      "row_total": 100000,
      "formatted_unit_price": "Rp 50.000",
      "formatted_row_total": "Rp 100.000",
      "meta": {
        "color": "red",
        "size": "M"
      }
    }
  ]
}
```

## Database Schema

### carts table
- `id` - Primary key
- `customer_id` - Foreign key to customers (nullable)
- `session_id` - Session ID for guest carts (nullable)
- `currency` - Cart currency (default: IDR)
- `subtotal_amount` - Decimal(10,2)
- `discount_amount` - Decimal(10,2)
- `shipping_amount` - Decimal(10,2)
- `tax_amount` - Decimal(10,2)
- `grand_total` - Decimal(10,2)
- `applied_promos` - JSON field for applied promotions
- `timestamps`

### cart_items table
- `id` - Primary key
- `cart_id` - Foreign key to carts
- `product_id` - Foreign key to products
- `qty` - Integer quantity
- `unit_price` - Decimal(10,2)
- `row_total` - Decimal(10,2)
- `currency` - Item currency
- `product_sku` - Product SKU snapshot
- `product_name` - Product name snapshot
- `meta_json` - JSON metadata
- `timestamps`

## Features

### Multi-User Support
- ✅ Authenticated customer carts
- ✅ Guest session-based carts
- ✅ Cart transfer on login

### Calculation Features
- ✅ Automatic total calculation
- ✅ Item-level discounts
- ✅ Cart-level discounts
- ✅ Shipping and tax calculation
- ✅ Weight calculation

### Data Integrity
- ✅ Product data snapshots (SKU, name)
- ✅ Automatic total recalculation
- ✅ Database triggers on cart item changes

### Developer Experience
- ✅ Rich model relationships
- ✅ Eloquent accessors and mutators
- ✅ Service layer abstraction
- ✅ Comprehensive helper methods

## Integration Points

### Controllers
Use `CartService` in controllers for cart operations:

```php
class CartController extends Controller
{
    private CartService $cartService;
    
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function store(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $this->cartService->addProduct($product, $request->quantity);
        
        return response()->json($this->cartService->getCartSummary());
    }
}
```

### Livewire Components
Access cart data in Livewire components:

```php
class CartIndicator extends Component
{
    private CartService $cartService;
    
    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function render()
    {
        return view('livewire.cart-indicator', [
            'itemsCount' => $this->cartService->getCartItemsCount(),
            'cartSummary' => $this->cartService->getCartSummary(),
        ]);
    }
}
```

### Frontend Integration
Use AJAX to interact with cart:

```javascript
// Add to cart
fetch('/cart/items', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        product_id: productId,
        quantity: quantity
    })
})
.then(response => response.json())
.then(data => {
    // Update cart display with data.count, data.formatted_grand_total, etc.
});
```

## Best Practices

1. **Always use CartService** for cart operations in controllers
2. **Load relationships** when needed to avoid N+1 queries
3. **Handle guest carts** properly in authentication flows
4. **Validate stock** before adding products to cart
5. **Clean up expired** guest carts regularly
6. **Use database transactions** for critical cart operations
7. **Cache cart summaries** for high-traffic applications

This cart system provides a robust foundation for e-commerce functionality with support for both guest and authenticated users, comprehensive calculation features, and a clean API for frontend integration.