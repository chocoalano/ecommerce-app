<?php

namespace Database\Seeders;

use App\Models\Auth\Customer;
use App\Models\CartProduct\Cart;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Guest cart + 2 item
        Cart::factory()->withItems(2)->create();

        // Customer cart + 3 item
        $customer = Customer::factory()->create();
        Cart::factory()->forCustomer($customer)->withItems(3)->create();
    }
}
