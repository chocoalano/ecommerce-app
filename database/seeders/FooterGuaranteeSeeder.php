<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterGuaranteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guarantees = [
            ['label' => 'Garansi 7 Hari', 'icon' => null, 'order' => 1, 'is_active' => true],
            ['label' => 'COD / Transfer', 'icon' => null, 'order' => 2, 'is_active' => true],
            ['label' => 'Aman & Terpercaya', 'icon' => null, 'order' => 3, 'is_active' => true],
        ];
        foreach ($guarantees as $guarantee) {
            \App\Models\FooterGuarantee::create($guarantee);
        }
    }
}
