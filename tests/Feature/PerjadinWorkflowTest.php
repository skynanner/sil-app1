<?php

namespace Tests\Feature;

use App\Models\BudgetAllocation;
use App\Models\Employee;
use App\Models\EmployeeBlock;
use App\Models\Role;
use App\Models\TravelMonitoring;
use App\Models\TravelReport;
use App\Models\TravelRequest;
use App\Models\TravelRequestPersonnel;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\BudgetService;
use App\Services\TravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerjadinWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected WorkUnit $workUnit;
    protected BudgetAllocation $budget;
    protected Employee $employee1;
    protected Employee $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['code' => Role::ADMIN],
            ['name' => 'Administrator', 'description' => 'Admin Sistem']
        );

        $userRole = Role::firstOrCreate(
            ['code' => Role::USER],
            ['name' => 'User / Staf', 'description' => 'Pengguna Biasa']
        );

        $this->workUnit = WorkUnit::firstOrCreate(
            ['code' => 'SATKER-01'],
            ['name' => 'Direktorat Operasi Laut', 'is_active' => true]
        );

        $this->employee1 = Employee::firstOrCreate(
            ['nip' => '198501012010011001'],
            [
                'nik' => '3201010101850001',
                'full_name' => 'Budi Santoso',
                'rank_grade' => 'Penata Muda / III/a',
                'position' => 'Analis Intelijen Maritim',
                'work_unit_id' => $this->workUnit->id,
                'is_active' => true,
            ]
        );

        $this->employee2 = Employee::firstOrCreate(
            ['nip' => '198802022012012002'],
            [
                'nik' => '3201010202880002',
                'full_name' => 'Siti Rahmawati',
                'rank_grade' => 'Penata / III/c',
                'position' => 'Perwira Navigasi',
                'work_unit_id' => $this->workUnit->id,
                'is_active' => true,
            ]
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin.test@bakamla.go.id'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        $this->regularUser = User::firstOrCreate(
            ['email' => 'user.test@bakamla.go.id'],
            [
                'name' => 'User Test',
                'password' => bcrypt('password'),
                'role_id' => $userRole->id,
                'employee_id' => $this->employee1->id,
                'is_active' => true,
            ]
        );

        $this->budget = BudgetAllocation::firstOrCreate(
            [
                'work_unit_id' => $this->workUnit->id,
                'account_code' => '524111',
                'fiscal_year' => (int) date('Y'),
            ],
            [
                'description' => 'Belanja Perjalanan Dinas Biasa',
                'total_amount' => 100000000,
                'committed_amount' => 0,
                'realized_amount' => 0,
            ]
        );
    }

    public function test_user_can_access_filament_admin_panel_when_active(): void
    {
        $this->actingAs($this->adminUser);
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $this->regularUser->update(['is_active' => false]);
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    public function test_employee_eligibility_and_overlap_validation(): void
    {
        $service = app(TravelRequestService::class);

        // Awalnya pegawai harus eligible
        $error = $service->checkEmployeeEligibility($this->employee1->id, '2026-10-01', '2026-10-05');
        $this->assertNull($error);

        // Buat pengajuan SPRINT aktif yang mencakup tanggal tsb
        $request = TravelRequest::create([
            'sprint_number' => 'SPRIN/TEST/001/' . time(),
            'sprint_date' => '2026-09-25',
            'sprint_file_path' => 'sprints/test.pdf',
            'activity_name' => 'Patroli Bersama Wilayah Barat',
            'activity_location' => 'Batam',
            'activity_start_date' => '2026-10-01',
            'activity_end_date' => '2026-10-05',
            'budget_allocation_id' => $this->budget->id,
            'submitted_by' => $this->regularUser->id,
            'status' => TravelRequest::STATUS_WAITING_VERIFICATION,
        ]);

        TravelRequestPersonnel::create([
            'travel_request_id' => $request->id,
            'employee_id' => $this->employee1->id,
            'activity_start_date' => '2026-10-01',
            'activity_end_date' => '2026-10-05',
        ]);

        // Cek overlap: seharusnya ditolak karena tanggal bertubrukan
        $overlapError = $service->checkEmployeeEligibility($this->employee1->id, '2026-10-03', '2026-10-07');
        $this->assertNotNull($overlapError);
        $this->assertStringContainsString('sudah memiliki penugasan SPRINT lain', $overlapError);

        // Pegawai lain pada tanggal yang sama harus tetap eligible
        $errorOther = $service->checkEmployeeEligibility($this->employee2->id, '2026-10-03', '2026-10-07');
        $this->assertNull($errorOther);
    }

    public function test_budget_commitment_and_realization_lifecycle(): void
    {
        $budgetService = app(BudgetService::class);

        $request = TravelRequest::create([
            'sprint_number' => 'SPRIN/LIFECYCLE/' . time(),
            'sprint_date' => '2026-09-25',
            'sprint_file_path' => 'sprints/lifecycle.pdf',
            'activity_name' => 'Rakor Keamanan Laut',
            'activity_location' => 'Jakarta',
            'activity_start_date' => '2026-10-10',
            'activity_end_date' => '2026-10-12',
            'budget_allocation_id' => $this->budget->id,
            'submitted_by' => $this->regularUser->id,
            'status' => TravelRequest::STATUS_WAITING_VERIFICATION,
        ]);

        $initialRemaining = (float) $this->budget->fresh()->remaining_balance;

        // 1. PPK Komitmen Rp 15.000.000
        $commitAmount = 15000000;
        $budgetService->commitBudget($request, $commitAmount);

        $this->assertEquals($commitAmount, (float) $this->budget->fresh()->committed_amount);
        $this->assertEquals($initialRemaining - $commitAmount, (float) $this->budget->fresh()->remaining_balance);

        // 2. PPSPM Realisasi Bayar Rp 15.000.000
        $budgetService->realizeBudget($request, $commitAmount);

        $freshBudget = $this->budget->fresh();
        $this->assertEquals(0, (float) $freshBudget->committed_amount);
        $this->assertEquals($commitAmount, (float) $freshBudget->realized_amount);
        $this->assertEquals($initialRemaining - $commitAmount, (float) $freshBudget->remaining_balance);
    }

    public function test_overdue_reports_trigger_automatic_employee_block(): void
    {
        // Buat pengajuan SPRINT yang selesai di masa lalu
        $pastRequest = TravelRequest::create([
            'sprint_number' => 'SPRIN/OVERDUE/' . time(),
            'sprint_date' => '2026-08-01',
            'sprint_file_path' => 'sprints/overdue.pdf',
            'activity_name' => 'Survei Alur Pelayaran',
            'activity_location' => 'Natuna',
            'activity_start_date' => '2026-08-05',
            'activity_end_date' => '2026-08-10',
            'budget_allocation_id' => $this->budget->id,
            'submitted_by' => $this->regularUser->id,
            'status' => TravelRequest::STATUS_SP2D,
        ]);

        TravelRequestPersonnel::create([
            'travel_request_id' => $pastRequest->id,
            'employee_id' => $this->employee1->id,
            'activity_start_date' => '2026-08-05',
            'activity_end_date' => '2026-08-10',
        ]);

        // Buat laporan LPJ berstatus PENDING dengan due_date lampau (overdue)
        $report = TravelReport::create([
            'travel_request_id' => $pastRequest->id,
            'status' => TravelReport::STATUS_PENDING,
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        // Jalankan console command perjadin:check-overdue
        $this->artisan('perjadin:check-overdue')
            ->assertSuccessful();

        // Verifikasi status report menjadi OVERDUE
        $this->assertEquals(TravelReport::STATUS_OVERDUE, $report->fresh()->status);

        // Verifikasi EmployeeBlock otomatis dibuat untuk employee1
        $block = EmployeeBlock::where('employee_id', $this->employee1->id)
            ->where('travel_request_id', $pastRequest->id)
            ->first();

        $this->assertNotNull($block);
        $this->assertEquals(EmployeeBlock::STATUS_ACTIVE, $block->status);

        // Verifikasi employee1 sekarang ditolak saat hendak ditugaskan SPRINT baru
        $service = app(TravelRequestService::class);
        $blockedError = $service->checkEmployeeEligibility($this->employee1->id, '2026-11-01', '2026-11-05');
        $this->assertNotNull($blockedError);
        $this->assertStringContainsString('DIBLOKIR', $blockedError);
    }
}
