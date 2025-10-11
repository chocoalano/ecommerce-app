<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\CourierFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['code','name','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function shipments() { return $this->hasMany(Shipment::class); }

}
