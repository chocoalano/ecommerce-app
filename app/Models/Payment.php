<?php

namespace App\Models;

use App\Models\BaseModel;

class Payment extends BaseModel
{

    protected $table = 'payments';
    protected $fillable = [
        'order_id','method_id','status','amount','currency','provider_txn_id','metadata_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'method_id' => 'integer',
        'amount' => 'float',
        'metadata_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function providerTxn()
    {
        return $this->belongsTo(ProviderTxn::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }

}
