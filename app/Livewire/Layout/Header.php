<?php

namespace App\Livewire\Layout;

use App\Models\Product\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Header extends Component
{
    public $searchQuery = '';
    public $showMobileSearch = false;
    public $showMobileMenu = false;

    public function toggleMobileSearch()
    {
        $this->showMobileSearch = !$this->showMobileSearch;
    }

    public function toggleMobileMenu()
    {
        $this->showMobileMenu = !$this->showMobileMenu;
    }

    public function search()
    {
        if (!empty($this->searchQuery)) {
            return redirect()->route('products.index', ['q' => $this->searchQuery]);
        }
    }

    public function logout()
    {
        Auth::guard('customer')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }

    public function render()
    {
        // Cache kategori untuk performa
        $categories = Cache::remember('menu:categories:top-with-children', 600, function () {
            return Category::query()
                ->select(['id', 'slug', 'name', 'description', 'is_active', 'image'])
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->with('children')
                ->get()
                ->map(function ($cat) {
                    return (object) [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description,
                        'image' => $cat->image,
                        'subCategories' => $cat->children->map(fn ($child) => (object) [
                            'slug' => $child->slug,
                            'name' => $child->name,
                        ]),
                    ];
                });
        });
        return view('livewire.layout.header', [
            'categories' => $categories,
            'user' => Auth::guard('customer')->user(),
            'isLoggedIn' => Auth::guard('customer')->check(),
        ]);
    }
}
