<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'category',
        'sort_order',
        'is_active',
        'show_in_footer',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_footer' => 'boolean',
    ];

    // Automatically generate slug from title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFooter($query)
    {
        return $query->where('show_in_footer', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    // Relationships
    public function pageContents()
    {
        return $this->hasOne(PageContent::class);
    }

    // Helper methods
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function getCategories()
    {
        return [
            'company' => 'Perusahaan',
            'help' => 'Bantuan',
            'legal' => 'Legal',
            'other' => 'Lainnya',
        ];
    }

    public function getCategoryLabelAttribute()
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    // Get pages for footer display
    public static function getFooterPages()
    {
        return cache()->remember('footer_pages', 3600, function () {
            return self::active()
                ->footer()
                ->ordered()
                ->get()
                ->groupBy('category');
        });
    }
}
