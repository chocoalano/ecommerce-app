<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;

class TblBonus extends Model
{
    protected $table = 'tbl_bonus';
    protected $fillable = [
        'member_id',
        'bonus',
        'index_value',
        'tax_netto',
        'tax_persen',
        'tax_value',
        'date',
        'status',
    ];
}
