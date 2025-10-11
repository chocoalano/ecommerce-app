<?php

namespace App\Models\OrderProduct;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\OrderItemFactory> */
    use HasFactory;
    protected $fillable = [
        'order_id','product_id','name','sku',
        'qty','unit_price','discount_amount','row_total',
        'weight_gram','length_mm','width_mm','height_mm',
        'meta_json',
    ];

    protected $casts = [
        'qty'             => 'integer',
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'row_total'       => 'decimal:2',
        'meta_json'       => 'array',
        'weight_gram'     => 'integer',
        'length_mm'       => 'integer',
        'width_mm'        => 'integer',
        'height_mm'       => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderItem $i) {
            if ($i->product && blank($i->sku))  $i->sku  = $i->product->sku;
            if ($i->product && blank($i->name)) $i->name = $i->product->name;
            $i->row_total = $i->row_total ?? ($i->qty * $i->unit_price - $i->discount_amount);
        });

        static::saved(function (OrderItem $i)  { $i->order?->recalcTotals(); });
        static::deleted(function (OrderItem $i){ $i->order?->recalcTotals(); });
    }

    public function order()   { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }

}
