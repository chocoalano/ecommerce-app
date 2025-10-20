<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSocial extends Model
{
    protected $fillable = [
        'platform',
        'url',
        'icon',
        'order',
        'is_active',
    ];
}
