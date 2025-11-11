<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblBonusPairing extends Model
{
    protected $table = 'tbl_bonus_pairing';
    protected $fillable = [
        'member_id',
        'pair',
        'bonus',
        'idx',
        'date',
        'status',
    ];
}
