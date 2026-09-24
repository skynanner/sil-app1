<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('code', Role::ADMIN)->first();
        $ppkRole = Role::where('code', Role::PPK_VERIFIER)->first();
        $ppspmRole = Role::where('code', Role::PPSPM_VERIFIER)->first();
        $userRole = Role::where('code', Role::USER)->first();

        $empAdmin = Employee::where('nip', '198501152010121001')->first();
        $empPpk = Employee::where('nip', '197805202002121002')->first();
        $empPpspm = Employee::where('nip', '198003102005012003')->first();
        $empBudi = Employee::where('nip', '198204182006041005')->first();
        $empRizky = Employee::where('nip', '198609252009121006')->first();

        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'name' => 'Ahmad Fauzi (Admin)',
                'email' => 'admin@bakamla.go.id',
                'username' => 'admin',
                'password' => $defaultPassword,
                'role_id' => $adminRole?->id,
                'employee_id' => $empAdmin?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Hendra Wijaya (PPK)',
                'email' => 'ppk@bakamla.go.id',
                'username' => 'ppk_verifier',
                'password' => $defaultPassword,
                'role_id' => $ppkRole?->id,
                'employee_id' => $empPpk?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Siti Aminah (PPSPM)',
                'email' => 'ppspm@bakamla.go.id',
                'username' => 'ppspm_verifier',
                'password' => $defaultPassword,
                'role_id' => $ppspmRole?->id,
                'employee_id' => $empPpspm?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@bakamla.go.id',
                'username' => 'user_budi',
                'password' => $defaultPassword,
                'role_id' => $userRole?->id,
                'employee_id' => $empBudi?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Rizky Pratama',
                'email' => 'rizky.pratama@bakamla.go.id',
                'username' => 'user_rizky',
                'password' => $defaultPassword,
                'role_id' => $userRole?->id,
                'employee_id' => $empRizky?->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Update existing developer/admin user if exists
        $veggaUser = User::where('name', 'vegga_admin')->first();
        if ($veggaUser) {
            $veggaUser->update([
                'role_id' => $adminRole?->id,
                'username' => 'vegga_admin',
                'is_active' => true,
            ]);
        }
    }
}
