<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterGuarantee extends Model
{
    protected $fillable = [
        'label',
        'icon',
        'order',
        'is_active',
    ];
}
