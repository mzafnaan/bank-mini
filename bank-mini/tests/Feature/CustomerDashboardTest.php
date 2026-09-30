<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\CustomerService;
use App\Services\TellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
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
     * Test nasabah dapat mengakses dashboard dan melihat data rekening dan saldo (FR-039, FR-040).
     */
    public function test_customer_can_access_dashboard_with_all_account_info_and_balance(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '5001',
            'name' => 'Fauzi Nasabah',
            'class' => 'XII RPL 1',
            'phone' => '081298765432',
        ]);

        $account = $customer->bankAccount;
        $account->update(['balance' => 250000]);

        $customerAccount = $customer->customerAccount;

        $response = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Fauzi Nasabah');
        $response->assertSee('5001');
        $response->assertSee('XII RPL 1');
        $response->assertSee($account->account_number);
        $response->assertSee('250.000');
    }

    /**
     * Test pengguna belum login dialihkan ke halaman login saat mencoba mengakses dashboard.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('customer.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test staff internal (Admin, Teller, Supervisor) tidak dapat mengakses dashboard nasabah (RBAC).
     */
    public function test_internal_staff_forbidden_from_customer_dashboard(): void
    {
        $admin = $this->createInternalUser('administrator', 'admin_cust_test');
        $teller = $this->createInternalUser('teller', 'teller_cust_test');
        $supervisor = $this->createInternalUser('supervisor', 'spv_cust_test');

        $this->actingAs($admin, 'web')->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($teller, 'web')->get(route('customer.dashboard'))->assertStatus(403);
        $this->actingAs($supervisor, 'web')->get(route('customer.dashboard'))->assertStatus(403);
    }

    /**
     * Test nasabah dapat melihat riwayat transaksi dan filter jenis mutasi (FR-041).
     */
    public function test_customer_can_view_transaction_history_and_filter(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_trx_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '5002',
            'name' => 'Maya Riwayat',
            'class' => 'XI AK 2',
        ]);

        $account = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        // Setor 100.000
        $this->tellerService->processDeposit($teller->id, $account->account_number, 100000);

        // Tarik 30.000
        $this->tellerService->processWithdrawal($teller->id, $account->account_number, 30000, $customer->plain_pin);

        // Akses riwayat transaksi
        $response = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.transactions'));

        $response->assertStatus(200);
        $response->assertSee('100.000');
        $response->assertSee('30.000');
        $response->assertSee('Setoran');
        $response->assertSee('Penarikan');

        // Filter tipe deposit
        $filterDeposit = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.transactions', ['type' => 'deposit']));

        $filterDeposit->assertStatus(200);
        $filterDeposit->assertSee('100.000');

        // Filter tipe withdrawal
        $filterWithdrawal = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.transactions', ['type' => 'withdrawal']));

        $filterWithdrawal->assertStatus(200);
        $filterWithdrawal->assertSee('30.000');
    }

    /**
     * Test nasabah dapat melihat halaman QR Code rekening (FR-042).
     */
    public function test_customer_can_view_qr_code_page(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '5003',
            'name' => 'Andi QR',
            'class' => 'X DKV 1',
        ]);

        $customerAccount = $customer->customerAccount;
        $account = $customer->bankAccount;

        $response = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.qr-code'));

        $response->assertStatus(200);
        $response->assertSee($account->account_number);
        $response->assertSee('Andi QR');
        $response->assertSee('Kartu Digital Rekening');
    }

    /**
     * Test nasabah dapat mengganti password login (FR-004, FR-005, BR-023).
     */
    public function test_customer_can_update_password(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '5004',
            'name' => 'Citra Security',
            'class' => 'XII BR 1',
        ]);

        $customerAccount = $customer->customerAccount;
        $this->assertTrue((bool) $customerAccount->first_login);

        // Akses halaman keamanan
        $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.security'))
            ->assertStatus(200);

        // Update password
        $response = $this->actingAs($customerAccount, 'customer_web')
            ->post(route('customer.security.password'), [
                'current_password' => $customer->plain_password,
                'new_password' => 'NewPassword123!',
                'new_password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan password terupdate dan first_login menjadi false
        $this->assertTrue(Hash::check('NewPassword123!', $customerAccount->fresh()->password));
        $this->assertFalse((bool) $customerAccount->fresh()->first_login);
    }

    /**
     * Test nasabah dapat mengganti PIN otorisasi transaksi (FR-004, BR-023).
     */
    public function test_customer_can_update_pin(): void
    {
        $customer = $this->customerService->createCustomer([
            'nis' => '5005',
            'name' => 'Doni PIN',
            'class' => 'XI RPL 2',
        ]);

        $customerAccount = $customer->customerAccount;

        $response = $this->actingAs($customerAccount, 'customer_web')
            ->post(route('customer.security.pin'), [
                'current_pin' => $customer->plain_pin,
                'new_pin' => '654321',
                'new_pin_confirmation' => '654321',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan PIN terupdate
        $this->assertTrue(Hash::check('654321', $customerAccount->fresh()->pin));
    }

    /**
     * Test nasabah dapat mengotorisasi permintaan penarikan tunai menggunakan PIN (BR-039, BR-040, DD-006).
     */
    public function test_customer_can_authorize_pending_withdrawal_request(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_auth_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '5006',
            'name' => 'Eka Otorisasi',
            'class' => 'XII RPL 1',
        ]);

        $account = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        // Setor 100.000
        $this->tellerService->processDeposit($teller->id, $account->account_number, 100000);

        // Teller membuat withdrawal request 40.000
        $withdrawalReq = $this->tellerService->createWithdrawalRequest($teller->id, $account->account_number, 40000);
        $this->assertEquals('waiting', $withdrawalReq->status);

        // Nasabah melihat request di dashboard
        $dashResponse = $this->actingAs($customerAccount, 'customer_web')
            ->get(route('customer.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Permintaan Penarikan Tunai di Loket Teller');
        $dashResponse->assertSee('40.000');

        // Nasabah otorisasi dengan PIN yang benar
        $authResponse = $this->actingAs($customerAccount, 'customer_web')
            ->post(route('customer.withdrawal.authorize', $withdrawalReq->id), [
                'pin' => $customer->plain_pin,
            ]);

        $authResponse->assertRedirect();
        $authResponse->assertSessionHas('success');

        // Status berubah approved dan saldo terpotong 40.000 (100.000 - 40.000 = 60.000)
        $this->assertEquals('approved', $withdrawalReq->fresh()->status);
        $this->assertNotNull($withdrawalReq->fresh()->approved_at);
        $this->assertEquals(60000, $account->fresh()->balance);

        // Transaksi penarikan tercatat di database
        $this->assertDatabaseHas('transactions', [
            'bank_account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => 40000,
        ]);
    }

    /**
     * Test nasabah dapat menolak permintaan penarikan tunai yang tidak diinginkan.
     */
    public function test_customer_can_reject_pending_withdrawal_request(): void
    {
        $teller = $this->createInternalUser('teller', 'teller_reject_test');

        $customer = $this->customerService->createCustomer([
            'nis' => '5007',
            'name' => 'Galih Tolak',
            'class' => 'X TKJ 2',
        ]);

        $account = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        // Setor 50.000
        $this->tellerService->processDeposit($teller->id, $account->account_number, 50000);

        // Request 20.000
        $withdrawalReq = $this->tellerService->createWithdrawalRequest($teller->id, $account->account_number, 20000);

        // Tolak penarikan
        $rejectResponse = $this->actingAs($customerAccount, 'customer_web')
            ->post(route('customer.withdrawal.reject', $withdrawalReq->id));

        $rejectResponse->assertRedirect();
        $rejectResponse->assertSessionHas('success');

        $this->assertEquals('rejected', $withdrawalReq->fresh()->status);
        $this->assertEquals(50000, $account->fresh()->balance); // Saldo tetap utuh
    }
}
