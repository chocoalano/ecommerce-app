<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\ShipmentItemFactory> */
    use HasFactory;
    protected $fillable = ['shipment_id','order_item_id','qty'];

    protected $casts = ['qty' => 'integer'];

    public function shipment()  { return $this->belongsTo(Shipment::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }

}
