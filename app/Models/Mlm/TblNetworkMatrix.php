<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblNetworkMatrix extends Model
{
    protected $table = 'tbl_network_matrix';
    protected $fillable = [
        'member_id',
        'sponsor_id',
        'level',
        'created_on',
    ];
}
