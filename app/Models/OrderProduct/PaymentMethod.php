<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\PaymentMethodFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['code','name','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments() { return $this->hasMany(Payment::class, 'method_id'); }

}
