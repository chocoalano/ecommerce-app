<?php

// Example usage in ProductController

namespace App\Http\Controllers;

use App\Traits\HasBreadcrumb;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HasBreadcrumb;

    public function show($slug)
    {
        // Fetch product and related data
        $product = Product::with(['categories', 'productVariants', 'reviews'])
                          ->where('slug', $slug)
                          ->firstOrFail();

        $primaryCategory = $product->categories->first();

        // Build breadcrumb using trait
        $breadcrumbItems = $this->buildProductBreadcrumb($product, $primaryCategory);

        return view('pages.products.detail', compact(
            'product',
            'primaryCategory',
            'breadcrumbItems'
            // ... other variables
        ));
    }
}

// Example usage in CategoryController
class CategoryController extends Controller
{
    use HasBreadcrumb;

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $breadcrumbItems = $this->buildCategoryBreadcrumb($category);

        return view('pages.categories.show', compact(
            'category',
            'breadcrumbItems'
        ));
    }
}

// Example usage in SearchController
class SearchController extends Controller
{
    use HasBreadcrumb;

    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $breadcrumbItems = $this->buildSearchBreadcrumb($query);

        return view('pages.search.index', compact(
            'query',
            'breadcrumbItems'
        ));
    }
}
