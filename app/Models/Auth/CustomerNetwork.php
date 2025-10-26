<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class CustomerNetwork extends Model
{
    protected $table = 'customer_networks';

    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth',
    ];

    public function ancestor()
    {
        return $this->belongsTo(Customer::class, 'ancestor_id');
    }

    public function descendant()
    {
        return $this->belongsTo(Customer::class, 'descendant_id');
    }
}
