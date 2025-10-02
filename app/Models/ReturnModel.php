<?php

namespace App\Models;

use App\Models\BaseModel;

class ReturnModel extends BaseModel
{

    protected $table = 'returns';
    public $timestamps = false;
    protected $fillable = [
        'order_id','status','reason','requested_at','processed_at'
    ];
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

}
