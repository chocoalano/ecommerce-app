<?php

namespace Database\Seeders;

use App\Models\Auth\Customer;
use App\Models\Auth\CustomerAddress;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;

class UserRoleCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin   = Role::factory()->state(['code' => 'ADMIN', 'name' => 'Admin'])->create();
        $cs      = Role::factory()->state(['code' => 'CS', 'name' => 'Customer Service'])->create();

        // Users + attach roles
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $user->roles()->attach([$admin->id, $cs->id]);

        // Customers + addresses
        $cust = Customer::factory()->create(['email' => 'customer@example.com']);
        CustomerAddress::factory()->for($cust)->default()->create();
        CustomerAddress::factory()->for($cust)->create();
    }
}
