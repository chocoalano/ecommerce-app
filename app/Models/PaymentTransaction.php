<?php

namespace App\Models;

use App\Models\BaseModel;

class PaymentTransaction extends BaseModel
{
    
    protected $table = 'payment_transactions';
    public $timestamps = false;
    protected $fillable = [
        'payment_id','status','amount','raw_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'payment_id' => 'integer',
        'amount' => 'float',
        'raw_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

}