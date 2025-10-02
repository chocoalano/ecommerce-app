<?php

namespace App\Models;

use App\Models\BaseModel;

class Wishlist extends BaseModel
{
    
    protected $table = 'wishlists';
    public $timestamps = false;
    protected $fillable = [
        'user_id'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class, 'wishlist_id');
    }

}