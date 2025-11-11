<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblBonusSponsor extends Model
{
    protected $table = 'tbl_bonus_sponsor';
    protected $fillable = [
        'member_id',
        'from_id',
        'bonus',
        'idx',
        'date',
        'status',
    ];
}
