<?php

namespace App\Models\OrderProduct;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\OrderProduct\PaymentTransactionFactory> */
    use HasFactory;
    public $timestamps = false; // created_at only (sesuai migration)

    protected $fillable = ['payment_id','status','amount','raw_json','created_at'];

    protected $casts = [
        'amount'   => 'decimal:2',
        'raw_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function payment() { return $this->belongsTo(Payment::class); }

}
