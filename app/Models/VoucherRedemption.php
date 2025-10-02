<?php

namespace App\Models;

use App\Models\BaseModel;

class VoucherRedemption extends BaseModel
{
    
    protected $table = 'voucher_redemptions';
    public $timestamps = false;
    protected $fillable = [
        'voucher_id','user_id','order_id','redeemed_at'
    ];
    protected $casts = [
        'id' => 'integer',
        'voucher_id' => 'integer',
        'user_id' => 'integer',
        'order_id' => 'integer',
        'redeemed_at' => 'datetime',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}