<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblBonusPairingDetail extends Model
{
    protected $table = 'tbl_bonus_pairing_detail';
    protected $fillable = [
        'member_id',
        'from_id',
        'pair',
        'bonus',
        'idx',
        'date',
        'status',
    ];
}
