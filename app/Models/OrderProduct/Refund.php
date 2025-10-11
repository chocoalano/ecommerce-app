<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\RefundFactory> */
    use HasFactory;
    protected $fillable = ['order_id','payment_id','status','amount','reason'];

    protected $casts = ['amount' => 'decimal:2'];

    public function order()   { return $this->belongsTo(Order::class); }
    public function payment() { return $this->belongsTo(Payment::class); }

}
