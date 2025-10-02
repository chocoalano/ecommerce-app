<?php

namespace App\Models;

use App\Models\BaseModel;

class Role extends BaseModel
{
    
    protected $table = 'roles';
    public $timestamps = false;
    protected $fillable = [
        'code','name'
    ];
    protected $casts = [
        'id' => 'integer',
    ];

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

}