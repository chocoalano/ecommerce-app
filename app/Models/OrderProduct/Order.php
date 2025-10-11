<?php

namespace App\Models\OrderProduct;

use App\Models\Auth\Customer;
use App\Models\Auth\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\OrderFactory> */
    use HasFactory;
    public const ST_PENDING   = 'PENDING';
    public const ST_PAID      = 'PAID';
    public const ST_PROCESS   = 'PROCESSING';
    public const ST_SHIPPED   = 'SHIPPED';
    public const ST_COMPLETED = 'COMPLETED';
    public const ST_CANCELED  = 'CANCELED';
    public const ST_REFUNDED  = 'REFUNDED';
    public const ST_PARTIAL_REFUND = 'PARTIAL_REFUND';

    protected $fillable = [
        'order_no','customer_id','currency','status',
        'subtotal_amount','discount_amount','shipping_amount','tax_amount','grand_total',
        'shipping_address_id','billing_address_id','applied_promos','payment_method','notes','placed_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'applied_promos'  => 'array',
        'placed_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $o) {
            if (blank($o->order_no)) {
                $o->order_no = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
            }
            $o->currency = $o->currency ?: 'IDR';
        });
    }

    /* Relationships */
    public function customer()        { return $this->belongsTo(Customer::class); }
    public function shippingAddress() { return $this->belongsTo(CustomerAddress::class, 'shipping_address_id'); }
    public function billingAddress()  { return $this->belongsTo(CustomerAddress::class, 'billing_address_id'); }
    public function items()           { return $this->hasMany(OrderItem::class); }
    public function payments()        { return $this->hasMany(Payment::class); }
    public function shipments()       { return $this->hasMany(Shipment::class); }
    public function returns()         { return $this->hasMany(OrderReturn::class, 'order_id'); }
    public function refunds()         { return $this->hasMany(Refund::class); }

    /* Scopes */
    public function scopeStatus($q, string $status) { return $q->where('status', $status); }
    public function scopeLive($q) { return $q->whereNotIn('status', [self::ST_CANCELED]); }

    /* Helpers */
    public function recalcTotals(): void
    {
        $sub   = $this->items->sum(fn($i) => (float)$i->qty * (float)$i->unit_price);
        $rows  = $this->items->sum(fn($i) => (float)$i->row_total);
        $this->subtotal_amount = $sub;
        $this->discount_amount = max(0, $sub - $rows);
        $this->grand_total     = $rows + (float)$this->shipping_amount + (float)$this->tax_amount;
        $this->save();
    }

}
