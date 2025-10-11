<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\OrderReturnFactory> */
    use HasFactory;
    protected $table = 'returns';

    public const ST_REQUESTED = 'REQUESTED';
    public const ST_APPROVED  = 'APPROVED';
    public const ST_REJECTED  = 'REJECTED';
    public const ST_RECEIVED  = 'RECEIVED';
    public const ST_REFUNDED  = 'REFUNDED';

    protected $fillable = ['order_id','status','reason','requested_at','processed_at'];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function items() { return $this->hasMany(ReturnItem::class, 'return_id'); }

}
