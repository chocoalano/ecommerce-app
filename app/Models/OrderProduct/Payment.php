<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\PaymentFactory> */
    use HasFactory;
    public const ST_INITIATED = 'INITIATED';
    public const ST_AUTHORIZED= 'AUTHORIZED';
    public const ST_CAPTURED  = 'CAPTURED';
    public const ST_FAILED    = 'FAILED';
    public const ST_CANCELED  = 'CANCELED';
    public const ST_REFUNDED  = 'REFUNDED';
    public const ST_PARTIAL   = 'PARTIAL_REFUND';

    protected $fillable = [
        'order_id','method_id','status','amount','currency',
        'provider_txn_id','metadata_json',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'metadata_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $p) {
            $p->currency = $p->currency ?: 'IDR';
        });
    }

    public function order()  { return $this->belongsTo(Order::class); }
    public function method() { return $this->belongsTo(PaymentMethod::class, 'method_id'); }
    public function transactions() { return $this->hasMany(PaymentTransaction::class); }
    public function refunds() { return $this->hasMany(Refund::class); }

}
