<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Customer;

class TblWithdrawalRequest extends Model
{
    protected $table = 'tbl_withdrawal_request';

    protected $fillable = [
        'member_id',
        'amount',
        'withdrawal_method',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relasi ke Customer (Member)
     */
    public function member()
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Relasi ke Admin yang memproses
     */
    public function processedBy()
    {
        return $this->belongsTo(Customer::class, 'processed_by');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
