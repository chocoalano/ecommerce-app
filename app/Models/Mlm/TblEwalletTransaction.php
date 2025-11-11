<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblEwalletTransaction extends Model
{
    protected $table = 'tbl_ewallet_transaction';

    const CREATED_AT = 'created_on';
    const UPDATED_AT = null;
    protected $fillable = [
        'member_id',
        'type',
        'credit',
        'debit',
        'balance',
        'note',
    ];

    protected $casts = [
        'type' => 'boolean',
        'credit' => 'float',
        'debit' => 'float',
        'balance' => 'float',
    ];
}
