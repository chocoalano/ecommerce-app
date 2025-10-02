<?php

namespace App\Models;

use App\Models\BaseModel;

class Promotion extends BaseModel
{
    
    protected $table = 'promotions';
    protected $fillable = [
        'code','name','type','landing_slug','description','start_at','end_at','is_active','priority','max_redemption','per_user_limit','conditions_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'integer',
        'priority' => 'integer',
        'max_redemption' => 'integer',
        'per_user_limit' => 'integer',
        'conditions_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bankInstallments()
    {
        return $this->hasMany(BankInstallment::class, 'promotion_id');
    }

    public function landings()
    {
        return $this->hasMany(Landing::class, 'promotion_id');
    }

    public function promotionGifts()
    {
        return $this->hasMany(PromotionGift::class, 'promotion_id');
    }

    public function promotionProducts()
    {
        return $this->hasMany(PromotionProduct::class, 'promotion_id');
    }

    public function tradeInPrograms()
    {
        return $this->hasMany(TradeInProgram::class, 'promotion_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'promotion_id');
    }

}