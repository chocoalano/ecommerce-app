<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Footer::create([
            'company_name' => 'Sinergi Abadi',
            'company_logo' => 'images/logo-puranura-id.png',
            'company_description' => 'Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.',
            'whatsapp' => '6281234567890',
            'email' => 'support@domain.com',
            'operating_hours' => 'Senin–Sabtu 09:00–18:00 WIB',
        ]);
    }
}
