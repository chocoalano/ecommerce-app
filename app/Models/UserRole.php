<?php

namespace App\Models;

use App\Models\BaseModel;

class UserRole extends BaseModel
{
    
    protected $table = 'user_roles';
    public $timestamps = false;
    protected $fillable = [
        'user_id','role_id'
    ];
    protected $casts = [
        'user_id' => 'integer',
        'role_id' => 'integer',
        'created_at' => 'datetime',
    ];
    // Pivot table detected: consider defining belongsToMany() on the related models.

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}