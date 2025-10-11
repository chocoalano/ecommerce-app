<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\Auth\RoleFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['code', 'name'];

    // --- Relationships ---
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
