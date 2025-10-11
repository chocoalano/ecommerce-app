# Komponen Livewire Breadcrumb

## Deskripsi
Komponen Livewire yang dinamis untuk menampilkan breadcrumb (navigation trail) di aplikasi Laravel. Tersedia dalam 2 versi:
1. `Breadcrumb` - Manual configuration
2. `AutoBreadcrumb` - Auto-generation dari route

## Fitur
- ✅ Responsive design dengan Tailwind CSS
- ✅ Support untuk nested categories
- ✅ Auto-generation berdasarkan route
- ✅ Customizable home label dan URL
- ✅ Optional home link
- ✅ Active state management
- ✅ SEO-friendly dengan structured data

## Instalasi & Setup

### 1. Manual Breadcrumb Component

#### Component: `app/Livewire/Components/Breadcrumb.php`
```php
@livewire('components.breadcrumb', [
    'items' => $breadcrumbItems,
    'showHome' => true,
    'homeLabel' => 'Beranda',
    'homeUrl' => '/'
])
```

#### Parameters:
- `items` (array): Array berisi breadcrumb items
- `showHome` (bool): Tampilkan link home (default: true)
- `homeLabel` (string): Label untuk home link (default: 'Beranda')
- `homeUrl` (string): URL untuk home link (default: '/')

#### Format Item Array:
```php
[
    'label' => 'Category Name',
    'url' => 'https://example.com/category',
    'params' => [],
    'is_active' => false  // true untuk item terakhir/aktif
]
```

### 2. Auto Breadcrumb Component

#### Component: `app/Livewire/Components/AutoBreadcrumb.php`
```php
@livewire('components.auto-breadcrumb')
```

Otomatis generate breadcrumb berdasarkan route name dan parameters.

## Contoh Penggunaan

### 1. Product Detail Page
```php
// Manual way
@php
    $breadcrumbItems = [];
    
    if(isset($primaryCategory) && $primaryCategory) {
        $breadcrumbItems[] = [
            'label' => $primaryCategory->name,
            'url' => route('products.index', ['category' => $primaryCategory->slug]),
            'params' => [],
            'is_active' => false
        ];
    }
    
    $breadcrumbItems[] = [
        'label' => $product->name,
        'url' => null,
        'params' => [],
        'is_active' => true
    ];
@endphp

@livewire('components.breadcrumb', ['items' => $breadcrumbItems])

// Auto way (recommended)
@livewire('components.auto-breadcrumb')
```

### 2. Category Page
```php
@livewire('components.breadcrumb', [
    'items' => [
        [
            'label' => $category->name,
            'url' => null,
            'params' => [],
            'is_active' => true
        ]
    ]
])
```

### 3. Search Results
```php
@livewire('components.breadcrumb', [
    'items' => [
        [
            'label' => 'Pencarian: ' . $query,
            'url' => null,
            'params' => [],
            'is_active' => true
        ]
    ]
])
```

### 4. Multi-level Navigation
```php
@livewire('components.breadcrumb', [
    'items' => [
        [
            'label' => 'Elektronik',
            'url' => route('products.index', ['category' => 'elektronik']),
            'params' => [],
            'is_active' => false
        ],
        [
            'label' => 'Smartphone',
            'url' => route('products.index', ['category' => 'smartphone']),
            'params' => [],
            'is_active' => false
        ],
        [
            'label' => 'iPhone 15 Pro',
            'url' => null,
            'params' => [],
            'is_active' => true
        ]
    ]
])
```

### 5. Without Home Link
```php
@livewire('components.breadcrumb', [
    'items' => [
        [
            'label' => 'Bantuan',
            'url' => null,
            'params' => [],
            'is_active' => true
        ]
    ],
    'showHome' => false
])
```

### 6. Custom Home
```php
@livewire('components.breadcrumb', [
    'items' => $breadcrumbItems,
    'homeLabel' => 'Dashboard',
    'homeUrl' => '/admin'
])
```

## Menggunakan Trait HasBreadcrumb

### Setup Controller
```php
use App\Traits\HasBreadcrumb;

class ProductController extends Controller
{
    use HasBreadcrumb;

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $primaryCategory = $product->categories->first();
        
        $breadcrumbItems = $this->buildProductBreadcrumb($product, $primaryCategory);
        
        return view('pages.products.detail', compact(
            'product',
            'primaryCategory',
            'breadcrumbItems'
        ));
    }
}
```

### Available Trait Methods
- `buildProductBreadcrumb($product, $primaryCategory = null)`
- `buildCategoryBreadcrumb($category)`
- `buildSimpleBreadcrumb($label, $url = null)`
- `buildSearchBreadcrumb($query = '')`

## Auto-Generation Routes Support

AutoBreadcrumb secara otomatis mengenali route patterns:

- `product.*` → Product breadcrumb
- `category*` → Category breadcrumb
- `search*` → Search breadcrumb
- `cart*` → Cart/Checkout breadcrumb
- `user.*` → User profile breadcrumb
- Default → Generate dari route name

## Customization

### Styling
Edit file `resources/views/livewire/components/breadcrumb.blade.php` untuk mengubah styling.

### Logic
Override methods di `AutoBreadcrumb` component untuk custom logic:
```php
protected function generateProductBreadcrumb(array $parameters): void
{
    // Your custom logic here
}
```

## Tips & Best Practices

1. **Performance**: Gunakan eager loading untuk relationships
```php
$product = Product::with(['categories'])->find($id);
```

2. **SEO**: Breadcrumb sudah SEO-friendly dengan structured markup

3. **Responsive**: Component responsive by default dengan Tailwind CSS

4. **Cache**: Consider caching breadcrumb data untuk performa lebih baik

5. **Lazy Loading**: Gunakan `@livewire` untuk lazy loading component

## Troubleshooting

### Error: Route not found
Pastikan route yang digunakan dalam breadcrumb sudah terdefinisi:
```php
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('category');
```

### Error: Relationship not found
Pastikan model memiliki relationship yang diperlukan:
```php
// Product model
public function categories()
{
    return $this->belongsToMany(Category::class, 'product_categories');
}
```

### Styling Issues
Pastikan Tailwind CSS classes sudah ter-compile dan tersedia.

## Migration dari Static Breadcrumb

Ganti static breadcrumb HTML dengan:
```php
// Dari:
<nav class="text-sm mb-6" aria-label="Breadcrumb">
    <!-- static HTML -->
</nav>

// Ke:
@livewire('components.auto-breadcrumb')
```
