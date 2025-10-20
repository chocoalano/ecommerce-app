<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    protected $fillable = [
        'company_name',
        'company_logo',
        'company_description',
        'whatsapp',
        'email',
        'operating_hours',
    ];
}
