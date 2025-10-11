<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\ShipmentFactory> */
    use HasFactory;
    public const ST_READY = 'READY_TO_SHIP';
    public const ST_TRANSIT = 'IN_TRANSIT';
    public const ST_DELIVERED = 'DELIVERED';
    public const ST_FAILED = 'FAILED';
    public const ST_RETURNED = 'RETURNED';

    protected $fillable = [
        'order_id','courier_id','tracking_no','status',
        'shipped_at','delivered_at','shipping_fee',
    ];

    protected $casts = [
        'shipping_fee' => 'decimal:2',
        'shipped_at'   => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function courier() { return $this->belongsTo(Courier::class); }
    public function items() { return $this->hasMany(ShipmentItem::class); }

}
