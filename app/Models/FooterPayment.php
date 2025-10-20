<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterPayment extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'order',
        'is_active',
    ];
}
