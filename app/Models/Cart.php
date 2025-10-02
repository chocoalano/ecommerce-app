<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Session;

class Cart extends BaseModel
{

    protected $table = 'carts';
    protected $fillable = [
        'user_id','session_id','currency','subtotal_amount','discount_amount','shipping_amount','tax_amount','grand_total','applied_promos'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'subtotal_amount' => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'tax_amount' => 'float',
        'grand_total' => 'float',
        'applied_promos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

}
