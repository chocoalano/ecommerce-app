<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FooterSocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socials = [
            ['platform' => 'Instagram', 'url' => 'https://instagram.com/yourbrand', 'icon' => null, 'order' => 1, 'is_active' => true],
            ['platform' => 'Facebook', 'url' => 'https://facebook.com/yourbrand', 'icon' => null, 'order' => 2, 'is_active' => true],
            ['platform' => 'TikTok', 'url' => 'https://tiktok.com/@yourbrand', 'icon' => null, 'order' => 3, 'is_active' => true],
            ['platform' => 'YouTube', 'url' => 'https://youtube.com/yourbrand', 'icon' => null, 'order' => 4, 'is_active' => true],
        ];
        foreach ($socials as $social) {
            \App\Models\FooterSocial::create($social);
        }
    }
}
