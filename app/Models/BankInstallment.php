<?php

namespace App\Models;

use App\Models\BaseModel;

class BankInstallment extends BaseModel
{
    
    protected $table = 'bank_installments';
    public $timestamps = false;
    protected $fillable = [
        'promotion_id','bank_code','tenor_months','interest_rate_pa','admin_fee','min_spend'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'tenor_months' => 'integer',
        'interest_rate_pa' => 'float',
        'admin_fee' => 'float',
        'min_spend' => 'float',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

}