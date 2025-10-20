<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsletterSubscriberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\NewsletterSubscriber::create([
            'email' => 'demo@email.com',
            'subscribed_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);
    }
}
