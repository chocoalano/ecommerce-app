<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\ReturnModel;

class ReturnItem extends BaseModel
{

    protected $table = 'return_items';
    public $timestamps = false;
    protected $fillable = [
        'return_id','order_item_id','qty','condition_note'
    ];
    protected $casts = [
        'id' => 'integer',
        'return_id' => 'integer',
        'order_item_id' => 'integer',
        'qty' => 'integer',
    ];

    public function return()
    {
        return $this->belongsTo(ReturnModel::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

}
