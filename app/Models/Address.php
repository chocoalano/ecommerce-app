<?php

namespace App\Models;

use App\Models\BaseModel;

class Address extends BaseModel
{

    protected $table = 'addresses';
    protected $fillable = [
        'user_id','label','recipient_name','phone','line1','line2','city','province','postal_code','country','is_default'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'is_default' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
