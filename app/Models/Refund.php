<?php

namespace App\Models;

use App\Models\BaseModel;

class Refund extends BaseModel
{
    
    protected $table = 'refunds';
    public $timestamps = false;
    protected $fillable = [
        'order_id','payment_id','status','amount','reason'
    ];
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'payment_id' => 'integer',
        'amount' => 'float',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

}