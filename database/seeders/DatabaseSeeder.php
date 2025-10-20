<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // UserRoleCustomerSeeder::class,
            // CategoryProoductSeeder::class,
            // InventorySeeder::class,
            // PromotionSeeder::class,
            // CartSeeder::class,
            // OrderSeeder::class,
            FooterSeeder::class,
            FooterSocialSeeder::class,
            FooterPaymentSeeder::class,
            FooterGuaranteeSeeder::class,
            NewsletterSubscriberSeeder::class,
        ]);
    }
}
