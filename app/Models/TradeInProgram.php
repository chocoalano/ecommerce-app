<?php

namespace App\Models;

use App\Models\BaseModel;

class TradeInProgram extends BaseModel
{
    
    protected $table = 'trade_in_programs';
    public $timestamps = false;
    protected $fillable = [
        'promotion_id','terms_json','partner_name'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'terms_json' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

}