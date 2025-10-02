<?php

namespace App\Models;

use App\Models\BaseModel;

class Order extends BaseModel
{

    protected $table = 'orders';
    public $timestamps = false;
    protected $fillable = [
        'order_no','user_id','currency','status','subtotal_amount','discount_amount','shipping_amount','tax_amount','grand_total','shipping_address_id','billing_address_id','applied_promos','notes','placed_at'
    ];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'subtotal_amount' => 'float',
        'discount_amount' => 'float',
        'shipping_amount' => 'float',
        'tax_amount' => 'float',
        'grand_total' => 'float',
        'shipping_address_id' => 'integer',
        'billing_address_id' => 'integer',
        'applied_promos' => 'array',
        'placed_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(BankInstallment::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'order_id');
    }

    public function returns()
    {
        return $this->hasMany(ReturnModel::class, 'order_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'order_id');
    }

    public function voucherRedemptions()
    {
        return $this->hasMany(VoucherRedemption::class, 'order_id');
    }

}
