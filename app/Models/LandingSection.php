<?php

namespace App\Models;

use App\Models\BaseModel;

class LandingSection extends BaseModel
{
    
    protected $table = 'landing_sections';
    public $timestamps = false;
    protected $fillable = [
        'landing_id','type','content_json','sort_order'
    ];
    protected $casts = [
        'id' => 'integer',
        'landing_id' => 'integer',
        'content_json' => 'array',
        'sort_order' => 'integer',
    ];

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

}