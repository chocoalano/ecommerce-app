<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblNetwork extends Model
{
    protected $table = 'tbl_network';
    protected $fillable = [
        'member_id',
        'upline_id',
        'position',
        'level',
        'status',
        'created_on',
    ];
}
