<?php

namespace App\Models;

use App\Models\BaseModel;

class Landing extends BaseModel
{
    
    protected $table = 'landings';
    protected $fillable = [
        'promotion_id','slug','title','hero_image_url','meta_json','is_active'
    ];
    protected $casts = [
        'id' => 'integer',
        'promotion_id' => 'integer',
        'meta_json' => 'array',
        'is_active' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function landingSections()
    {
        return $this->hasMany(LandingSection::class, 'landing_id');
    }

    public function trackingUtms()
    {
        return $this->hasMany(TrackingUtm::class, 'landing_id');
    }

}