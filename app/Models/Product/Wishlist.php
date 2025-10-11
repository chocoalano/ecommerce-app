<?php
namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model
{
    use HasFactory;
    protected $table = 'wishlists';
    protected $fillable = [
        'customer_id','name'
    ];
    public function customer() { return $this->belongsTo(\App\Models\Auth\Customer::class); }
    public function items() { return $this->hasMany(WishlistItem::class); }
}
