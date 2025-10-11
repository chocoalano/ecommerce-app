<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        // Check if page is active
        if (!$page->is_active) {
            abort(404);
        }

        // Set SEO meta data
        $metaTitle = $page->meta_title ?: ($page->title . ' - ' . config('app.name'));
        $metaDescription = $page->meta_description ?: ($page->excerpt ?: 'Informasi lengkap tentang ' . $page->title . ' di ' . config('app.name'));

        return view('pages.show', compact('page', 'metaTitle', 'metaDescription'));
    }

    /**
     * Display pages by category (optional feature for category listing)
     */
    public function category($category)
    {
        $pages = Page::active()
            ->category($category)
            ->ordered()
            ->get();

        if ($pages->isEmpty()) {
            abort(404);
        }

        $categoryName = Page::getCategories()[$category] ?? $category;

        return view('pages.category', compact('pages', 'category', 'categoryName'));
    }

    /**
     * Display all active pages (optional site map feature)
     */
    public function index()
    {
        $pagesByCategory = Page::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        return view('pages.index', compact('pagesByCategory'));
    }
}
