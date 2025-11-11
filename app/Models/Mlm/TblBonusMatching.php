<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblBonusMatching extends Model
{
    protected $table = 'tbl_bonus_matching';
    protected $fillable = [
        'member_id',
        'from_id',
        'level',
        'bonus',
        'idx',
        'date',
        'status',
    ];
}
