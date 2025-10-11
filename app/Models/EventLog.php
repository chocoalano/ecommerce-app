<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    public $timestamps = false;
    protected $table = 'event_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'entity',
        'entity_id',
        'data_json',
        'created_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
