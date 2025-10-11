<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    /** @use HasFactory<\Database\Factories\Auth\CustomerAddressFactory> */
    use HasFactory;
    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id', 'label', 'recipient_name', 'phone',
        'line1', 'line2', 'city', 'province', 'postal_code',
        'country', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // --- Relationships ---
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
