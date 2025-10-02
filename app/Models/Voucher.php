<?php

namespace App\Models;

use App\Models\BaseModel;

class Voucher extends BaseModel
{
    
    protected $table = 'vouchers';
    public $timestamps = false;
    protected $fillable = [
        'promotion_id','code','is_stackable','start_at','end_at','max_redemption','per_user_limit','conditions_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'is_stackable' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'max_redemption' => 'integer',
        'per_user_limit' => 'integer',
        'conditions_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function voucherRedemptions()
    {
        return $this->hasMany(VoucherRedemption::class, 'voucher_id');
    }

}