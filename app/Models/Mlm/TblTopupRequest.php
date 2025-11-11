<?php

namespace App\Models\Mlm;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\Customer;

class TblTopupRequest extends Model
{
    protected $table = 'tbl_topup_request';

    protected $fillable = [
        'order_no',
        'member_id',
        'amount',
        'payment_method',
        'payment_proof',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'admin_note',
        'approved_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relasi ke Customer (Member)
     */
    public function member()
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    /**
     * Relasi ke Admin yang approve
     */
    public function approvedBy()
    {
        return $this->belongsTo(Customer::class, 'approved_by');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
