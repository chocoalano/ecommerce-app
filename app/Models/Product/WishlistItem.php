<?php
namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishlistItem extends Model
{
    use HasFactory;
    protected $table = 'wishlist_items';
    protected $fillable = [
        'wishlist_id','product_id','product_name','product_sku','meta_json'
    ];
    protected $casts = [
        'meta_json' => 'array',
    ];
    public function wishlist() { return $this->belongsTo(Wishlist::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
