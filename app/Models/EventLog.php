<?php

namespace App\Models;

use App\Models\BaseModel;

class EventLog extends BaseModel
{
    
    protected $table = 'event_logs';
    public $timestamps = false;
    protected $fillable = [
        'user_id','event_type','entity','entity_id','data_json'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'entity_id' => 'integer',
        'data_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

}