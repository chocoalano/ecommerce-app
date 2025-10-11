<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\ReturnItemFactory> */
    use HasFactory;
    protected $fillable = ['return_id','order_item_id','qty','condition_note'];

    protected $casts = ['qty' => 'integer'];

    public function return() { return $this->belongsTo(OrderReturn::class, 'return_id'); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }

}
