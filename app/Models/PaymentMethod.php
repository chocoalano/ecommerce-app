<?php

namespace App\Models;

use App\Models\BaseModel;

class PaymentMethod extends BaseModel
{
    
    protected $table = 'payment_methods';
    public $timestamps = false;
    protected $fillable = [
        'code','name','is_active'
    ];
    protected $casts = [
        'id' => 'integer',
        'is_active' => 'integer',
    ];

}