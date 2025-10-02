<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductReview extends BaseModel
{
    
    protected $table = 'product_reviews';
    public $timestamps = false;
    protected $fillable = [
        'user_id','product_id','rating','title','comment','is_approved'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'rating' => 'integer',
        'is_approved' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}