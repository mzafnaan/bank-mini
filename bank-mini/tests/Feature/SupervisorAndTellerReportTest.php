<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupervisorAndTellerReportTest extends TestCase
{
    use RefreshDatabase;

    protected function createTeller(): User
    {
        return User::create([
            'name' => 'Teller Test',
            'username' => 'teller_test',
            'email' => 'teller_test@bankmini.test',
            'password' => Hash::make('password123'),
            'role' => 'teller',
            'status' => 'active',
        ]);
    }

    protected function createSupervisor(): User
    {
        return User::create([
            'name' => 'Supervisor Test',
            'username' => 'spv_test',
            'email' => 'spv_test@bankmini.test',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'status' => 'active',
        ]);
    }

    protected function createCustomerAccount(string $email = 'student@bankmini.test'): CustomerAccount
    {
        $customer = Customer::create([
            'nis' => '12345',
            'name' => 'Siswa Test',
            'class' => 'XII RPL 1',
            'email' => $email,
            'email_verified_at' => now(),
        ]);

        $account = BankAccount::create([
            'customer_id' => $customer->id,
            'account_number' => 'RPL-001',
            'balance' => 50000,
            'status' => 'active',
            'qr_code' => 'RPL-001',
        ]);

        return CustomerAccount::create([
            'customer_id' => $customer->id,
            'username' => '12345',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('123456'),
            'first_login' => false,
            'status' => 'active',
        ]);
    }

    public function test_identification_page_redirects_to_teller_dashboard(): void
    {
        $teller = $this->createTeller();

        $response = $this->actingAs($teller, 'web')
            ->get(route('teller.identification', ['search' => 'RPL-001']));

        $response->assertRedirect(route('teller.dashboard', ['search' => 'RPL-001']));
    }

    public function test_supervisor_can_access_transactions_report(): void
    {
        $supervisor = $this->createSupervisor();
        $teller = $this->createTeller();
        $custAccount = $this->createCustomerAccount();
        $bankAccount = $custAccount->customer->bankAccount;

        // Create dummy transactions
        Transaction::create([
            'bank_account_id' => $bankAccount->id,
            'teller_id' => $teller->id,
            'type' => 'deposit',
            'amount' => 50000,
            'created_at' => now(),
        ]);

        Transaction::create([
            'bank_account_id' => $bankAccount->id,
            'teller_id' => $teller->id,
            'type' => 'withdrawal',
            'amount' => 20000,
            'created_at' => now(),
        ]);

        // Daily
        $responseDaily = $this->actingAs($supervisor, 'web')
            ->get(route('supervisor.transactions.report', ['period' => 'daily', 'date' => now()->toDateString()]));
        $responseDaily->assertOk();
        $responseDaily->assertViewHas('total_deposit', 50000);
        $responseDaily->assertViewHas('total_withdrawal', 20000);
        $responseDaily->assertViewHas('net_cash', 30000);

        // Weekly
        $responseWeekly = $this->actingAs($supervisor, 'web')
            ->get(route('supervisor.transactions.report', ['period' => 'weekly']));
        $responseWeekly->assertOk();

        // Monthly
        $responseMonthly = $this->actingAs($supervisor, 'web')
            ->get(route('supervisor.transactions.report', ['period' => 'monthly', 'month' => now()->month, 'year' => now()->year]));
        $responseMonthly->assertOk();

        // Print view
        $responsePrint = $this->actingAs($supervisor, 'web')
            ->get(route('supervisor.transactions.report.print', ['period' => 'daily']));
        $responsePrint->assertOk();
        $responsePrint->assertSee('LAPORAN TRANSAKSI MUTASI KAS');
    }

    public function test_mobile_forgot_password_api_sends_reset_email(): void
    {
        $this->createCustomerAccount('student.verified@bankmini.test');

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'student.verified@bankmini.test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Assert reset token created in customer_password_reset_tokens
        $this->assertNotNull(
            DB::table('customer_password_reset_tokens')->where('email', 'student.verified@bankmini.test')->first()
        );
    }

    public function test_mobile_forgot_password_rejects_internal_user_email(): void
    {
        $this->createTeller();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'teller_test@bankmini.test',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Reset password akun staf internal hanya dapat dilakukan oleh Administrator.',
        ]);
    }

    public function test_supervisor_report_clamps_future_dates_to_today(): void
    {
        $supervisor = $this->createSupervisor();
        $supervisorService = app(\App\Services\SupervisorService::class);

        // Daily with future date
        $reportDaily = $supervisorService->getTransactionReport(period: 'daily', date: '2100-12-09');
        $this->assertEquals(now()->toDateString(), $reportDaily['date']);

        // Weekly with future date
        $reportWeekly = $supervisorService->getTransactionReport(period: 'weekly', startDate: '2100-01-01', endDate: '2100-01-07');
        $this->assertEquals(now()->toDateString(), $reportWeekly['end_date']);
        $this->assertEquals(now()->toDateString(), $reportWeekly['start_date']);

        // Web view request with future date
        $response = $this->actingAs($supervisor, 'web')->get(route('supervisor.transactions.report', [
            'period' => 'daily',
            'date' => '2100-12-09',
        ]));
        $response->assertOk();
        $this->assertEquals(now()->toDateString(), $response->viewData('date'));
    }

    public function test_customer_can_reset_pin_using_account_password(): void
    {
        $custAccount = $this->createCustomerAccount();
        \Laravel\Sanctum\Sanctum::actingAs($custAccount, ['*']);

        $response = $this->postJson('/api/v1/auth/reset-pin-with-password', [
            'password' => 'secret123',
            'new_pin' => '654321',
            'new_pin_confirmation' => '654321',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'PIN transaksi berhasil diperbarui menggunakan verifikasi password akun.',
        ]);

        $custAccount->refresh();
        $this->assertTrue(Hash::check('654321', $custAccount->pin));
    }

    public function test_customer_cannot_reset_pin_with_wrong_account_password(): void
    {
        $custAccount = $this->createCustomerAccount();
        \Laravel\Sanctum\Sanctum::actingAs($custAccount, ['*']);

        $response = $this->postJson('/api/v1/auth/reset-pin-with-password', [
            'password' => 'wrongpass',
            'new_pin' => '654321',
            'new_pin_confirmation' => '654321',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Password akun yang Anda masukkan tidak sesuai.',
        ]);

        $custAccount->refresh();
        $this->assertTrue(Hash::check('123456', $custAccount->pin));
    }
}

