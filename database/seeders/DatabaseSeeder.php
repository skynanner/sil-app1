<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            WorkUnitSeeder::class,
            EmployeeSeeder::class,
            UserSeeder::class,
            TreasuryOfficialSeeder::class,
            BudgetAllocationSeeder::class,
            StandardCostSeeder::class,
            DocumentTemplateSeeder::class,
            TravelRequestSeeder::class,
        ]);
    }
}
