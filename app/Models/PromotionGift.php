<?php

namespace App\Models;

use App\Models\BaseModel;

class PromotionGift extends BaseModel
{

    protected $table = 'promotion_gifts';
    public $timestamps = false;
    protected $fillable = [
        'promotion_id','gift_variant_id','min_spend','min_qty'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'gift_variant_id' => 'integer',
        'min_spend' => 'float',
        'min_qty' => 'integer',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function giftVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

}
