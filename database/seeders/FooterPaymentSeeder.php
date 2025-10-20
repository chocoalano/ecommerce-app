<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            ['name' => 'Bank Transfer', 'icon' => null, 'order' => 1, 'is_active' => true],
            ['name' => 'COD', 'icon' => null, 'order' => 2, 'is_active' => true],
            ['name' => 'E-Wallet', 'icon' => null, 'order' => 3, 'is_active' => true],
        ];
        foreach ($payments as $payment) {
            \App\Models\FooterPayment::create($payment);
        }
    }
}
