<?php

namespace App\Models;

use App\Models\BaseModel;

class TrackingUtm extends BaseModel
{
    
    protected $table = 'tracking_utm';
    public $timestamps = false;
    protected $fillable = [
        'landing_id','utm_source','utm_medium','utm_campaign','utm_content'
    ];
    protected $casts = [
        'id' => 'integer',
        'landing_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

}