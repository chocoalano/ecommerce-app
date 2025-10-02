<?php

namespace App\Models;

use App\Models\BaseModel;

class Category extends BaseModel
{
    protected $fillable = [
        'parent_id','slug','name','description','sort_order','is_active','image'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }
}

