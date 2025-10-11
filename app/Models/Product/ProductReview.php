<?php
namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReview extends Model
{
    use HasFactory;
    protected $table = 'product_reviews';
    protected $fillable = [
        'customer_id','product_id','order_item_id','rating','title','comment','is_approved'
    ];
    protected $casts = [
        'is_approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'rating' => 'integer',
    ];
    public function product() { return $this->belongsTo(Product::class); }
    public function customer() { return $this->belongsTo(\App\Models\Auth\Customer::class); }
    public function orderItem() { return $this->belongsTo(\App\Models\OrderProduct\OrderItem::class); }
}
