<?php

namespace App\Models;

use App\Models\BaseModel;

class PasswordResetToken extends BaseModel
{
    
    protected $table = 'password_reset_tokens';
    public $timestamps = false;
    protected $fillable = [
        'email','token'
    ];
    protected $casts = [
        'created_at' => 'datetime',
    ];

}