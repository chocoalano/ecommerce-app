<?php

namespace Database\Factories\OrderProduct;

use App\Models\Auth\Customer;
use App\Models\Auth\CustomerAddress;
use App\Models\OrderProduct\Order;
use App\Models\OrderProduct\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'         => null,
            'currency'            => 'IDR',
            'status'              => Order::ST_PENDING,
            'subtotal_amount'     => 0,
            'discount_amount'     => 0,
            'shipping_amount'     => 0,
            'tax_amount'          => 0,
            'grand_total'         => 0,
            'shipping_address_id' => null,
            'billing_address_id'  => null,
            'applied_promos'      => null,
            'notes'               => null,
            'placed_at'           => now(),
        ];
    }

    public function forCustomer(?Customer $c = null): self
    {
        return $this->state(fn () => ['customer_id' => $c?->id ?? Customer::factory()]);
    }

    public function withAddresses(): self
    {
        return $this->afterCreating(function (Order $o) {
            $customer = $o->customer ?? Customer::factory()->create();
            $ship = CustomerAddress::factory()->for($customer)->default()->create();
            $bill = CustomerAddress::factory()->for($customer)->create();
            $o->update([
                'shipping_address_id' => $ship->id,
                'billing_address_id'  => $bill->id,
                'customer_id'         => $customer->id,
            ]);
        });
    }

    public function paid(): self     { return $this->state(fn () => ['status' => Order::ST_PAID]); }
    public function processing(): self{ return $this->state(fn () => ['status' => Order::ST_PROCESS]); }
    public function shipped(): self   { return $this->state(fn () => ['status' => Order::ST_SHIPPED]); }
    public function completed(): self { return $this->state(fn () => ['status' => Order::ST_COMPLETED]); }
    public function canceled(): self  { return $this->state(fn () => ['status' => Order::ST_CANCELED]); }

    /** Isi item & hitung total otomatis */
    public function withItems(int $count = 2): self
    {
        return $this->afterCreating(function (Order $o) use ($count) {
            OrderItem::factory()->count($count)->for($o)->create();
            $o->refresh();
            $o->recalcTotals();
        });
    }

}
