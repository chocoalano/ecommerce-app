<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\Auth\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'full_name', 'email', 'password', 'phone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
    ];

    // --- Relationships ---
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps(); // created_at dari pivot tersedia
    }

    // Helper: cek memiliki role
    public function hasRole(string $code): bool
    {
        return $this->roles()->where('code', $code)->exists();
    }
}
