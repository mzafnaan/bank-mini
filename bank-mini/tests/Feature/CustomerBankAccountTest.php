<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\TellerService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerBankAccountTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerService $customerService;
    protected TellerService $tellerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = app(CustomerService::class);
        $this->tellerService = app(TellerService::class);
    }

    protected function createInternalUser(string $role, string $username): User
    {
        return User::create([
            'name' => ucfirst($role) . ' User',
            'username' => $username,
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    /**
     * Test format nomor rekening [KODE JURUSAN]-[NOMOR URUT 3 DIGIT] dan QR Code.
     */
    public function test_customer_onboarding_generates_correct_account_number_format_and_qr_code(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '2026001',
            'name' => 'Budi Santoso',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
        ]);

        $bankAccount = $customer->bankAccount;

        $this->assertNotNull($bankAccount);
        $this->assertMatchesRegularExpression('/^RPL-\d{3}$/', $bankAccount->account_number);
        $this->assertEquals('RPL-001', $bankAccount->account_number);
        $this->assertEquals($bankAccount->account_number, $bankAccount->qr_code);
        $this->assertEquals('active', $bankAccount->status);
        $this->assertEquals(0, $bankAccount->balance);
    }

    /**
     * Test nomor urut auto-increment 3 digit zero-padded dan nomor rekening bersifat UNIQUE.
     */
    public function test_account_number_auto_increment_sequence_and_uniqueness(): void
    {
        $c1 = $this->customerService->createCustomer([
            'nis' => '1001',
            'name' => 'Siswa RPL 1',
            'class' => 'X RPL',
        ]);

        $c2 = $this->customerService->createCustomer([
            'nis' => '1002',
            'name' => 'Siswa RPL 2',
            'class' => 'X RPL',
        ]);

        $c3 = $this->customerService->createCustomer([
            'nis' => '1003',
            'name' => 'Siswa DKV',
            'class' => 'X DKV',
        ]);

        $c4 = $this->customerService->createCustomer([
            'nis' => '1004',
            'name' => 'Siswa TKJ',
            'class' => 'X TKJ',
        ]);

        $this->assertEquals('RPL-001', $c1->bankAccount->account_number);
        $this->assertEquals('RPL-002', $c2->bankAccount->account_number);
        $this->assertEquals('DKV-003', $c3->bankAccount->account_number);
        $this->assertEquals('TKJ-004', $c4->bankAccount->account_number);

        // Pastikan seluruh nomor rekening unik
        $accounts = BankAccount::pluck('account_number')->toArray();
        $this->assertCount(4, $accounts);
        $this->assertCount(4, array_unique($accounts));
    }

    /**
     * Test rekening aktif dapat melakukan deposit dan withdrawal.
     */
    public function test_active_bank_account_allows_deposit_and_withdrawal(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_active');

        $customer = $this->customerService->createCustomer([
            'nis' => '1005',
            'name' => 'Ahmad Active',
            'class' => 'XII RPL 2',
        ]);

        $account = $customer->bankAccount;
        $this->assertEquals('active', $account->status);

        // 1. Test Deposit
        $depositTrx = $this->tellerService->processDeposit($teller->id, $account->account_number, 50000);
        $this->assertEquals(50000, $depositTrx->amount);
        $this->assertEquals(50000, $account->fresh()->balance);

        // 2. Test Withdrawal (PIN awal menggunakan plain_pin yang digenerate onboarding)
        $pin = $customer->plain_pin;
        $withdrawalTrx = $this->tellerService->processWithdrawal($teller->id, $account->account_number, 20000, $pin);
        $this->assertEquals(20000, $withdrawalTrx->amount);
        $this->assertEquals(30000, $account->fresh()->balance);
    }

    /**
     * Test rekening inactive menolak deposit dengan pesan error yang jelas.
     */
    public function test_inactive_bank_account_rejects_deposit_with_proper_error(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_deposit_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '1006',
            'name' => 'Doni Inactive',
            'class' => 'XI TKJ 2',
        ]);

        $account = $customer->bankAccount;
        $account->update(['status' => 'inactive']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rekening nasabah sedang tidak aktif dan tidak dapat digunakan untuk transaksi.');

        $this->tellerService->processDeposit($teller->id, $account->account_number, 50000);
    }

    /**
     * Test rekening inactive menolak penarikan dengan pesan error yang jelas.
     */
    public function test_inactive_bank_account_rejects_withdrawal_with_proper_error(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_withdraw_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '1007',
            'name' => 'Rina Inactive',
            'class' => 'XI AK 1',
        ]);

        $account = $customer->bankAccount;
        $account->update(['balance' => 100000, 'status' => 'inactive']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rekening nasabah sedang tidak aktif dan tidak dapat digunakan untuk transaksi.');

        $this->tellerService->processWithdrawal($teller->id, $account->account_number, 20000, $customer->plain_pin);
    }

    /**
     * Test rekening inactive: akun login customer tetap aktif dan riwayat transaksi tetap tersimpan.
     */
    public function test_customer_account_login_unaffected_when_bank_account_is_inactive(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '1008',
            'name' => 'Siti Login',
            'class' => 'XII MP 1',
        ]);

        $bankAccount = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        // Nonaktifkan rekening bank saja
        $bankAccount->update(['status' => 'inactive']);

        // Akun login customer tetap active
        $this->assertEquals('active', $customerAccount->fresh()->status);
        $this->assertEquals('inactive', $bankAccount->fresh()->status);

        // Data customer dan rekening tetap ada di database
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('bank_accounts', ['id' => $bankAccount->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('customer_accounts', ['id' => $customerAccount->id, 'status' => 'active']);

        // Login web customer berhasil
        $response = $this->post('/login', [
            'username' => $customerAccount->username,
            'password' => $customer->plain_password,
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($customerAccount, 'customer_web');
    }

    /**
     * Test otorisasi: hanya Administrator yang dapat mengubah status rekening.
     */
    public function test_only_administrator_can_toggle_bank_account_status(): void
    {
        $admin = $this->createInternalUser('administrator', 'admin_test');
        $teller = $this->createInternalUser('teller', 'teller_role_test');
        $supervisor = $this->createInternalUser('supervisor', 'spv_role_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '1009',
            'name' => 'Joko Test',
            'class' => 'X BR',
        ]);

        $account = $customer->bankAccount;
        $this->assertEquals('active', $account->status);

        // 1. Admin sukses menonaktifkan rekening
        $this->actingAs($admin, 'web')
            ->patch(route('admin.accounts.toggle-status', $account->id))
            ->assertRedirect(route('admin.accounts'))
            ->assertSessionHas('success');

        $this->assertEquals('inactive', $account->fresh()->status);

        // 2. Admin sukses mengaktifkan kembali rekening
        $this->actingAs($admin, 'web')
            ->patch(route('admin.accounts.toggle-status', $account->id))
            ->assertRedirect(route('admin.accounts'))
            ->assertSessionHas('success');

        $this->assertEquals('active', $account->fresh()->status);

        // 3. Teller ditolak (403 Forbidden)
        $this->actingAs($teller, 'web')
            ->patch(route('admin.accounts.toggle-status', $account->id))
            ->assertStatus(403);

        // 4. Supervisor ditolak (403 Forbidden)
        $this->actingAs($supervisor, 'web')
            ->patch(route('admin.accounts.toggle-status', $account->id))
            ->assertStatus(403);

        // 5. Customer login ditolak (403 Forbidden)
        $this->actingAs($customer->customerAccount, 'customer_web')
            ->patch(route('admin.accounts.toggle-status', $account->id))
            ->assertStatus(403);
    }
}
