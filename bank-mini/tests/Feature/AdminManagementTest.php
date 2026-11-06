<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(string $username = 'admin_boss'): User
    {
        return User::create([
            'name' => 'Administrator Utama',
            'username' => $username,
            'email' => "{$username}@bankmini.test",
            'password' => Hash::make('password123'),
            'role' => 'administrator',
            'status' => 'active',
        ]);
    }

    protected function createTeller(string $username = 'teller_staff'): User
    {
        return User::create([
            'name' => 'Petugas Teller',
            'username' => $username,
            'email' => "{$username}@bankmini.test",
            'password' => Hash::make('password123'),
            'role' => 'teller',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_deactivate_own_account_via_toggle(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')
            ->patch(route('admin.users.toggle-status', $admin));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        $this->assertEquals('active', $admin->fresh()->status);
    }

    public function test_admin_cannot_update_own_status_to_inactive(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')
            ->put(route('admin.users.update', $admin), [
                'name' => 'Administrator Updated',
                'username' => $admin->username,
                'role' => 'administrator',
                'status' => 'inactive',
            ]);

        $response->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        $this->assertEquals('active', $admin->fresh()->status);
    }

    public function test_admin_can_deactivate_and_activate_other_users(): void
    {
        $admin = $this->createAdmin();
        $teller = $this->createTeller();

        // Deactivate teller
        $response = $this->actingAs($admin, 'web')
            ->patch(route('admin.users.toggle-status', $teller));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $teller->fresh()->status);

        // Reactivate teller
        $responseReactivate = $this->actingAs($admin, 'web')
            ->patch(route('admin.users.toggle-status', $teller));

        $responseReactivate->assertRedirect(route('admin.users'));
        $this->assertEquals('active', $teller->fresh()->status);
    }

    public function test_initial_deposit_must_be_at_least_10000_when_creating_customer(): void
    {
        $admin = $this->createAdmin();

        // 1. Initial deposit < 10000 (e.g. 5000) must fail validation
        $responseFail = $this->actingAs($admin, 'web')
            ->post(route('admin.customers.store'), [
                'nis' => '2026999',
                'name' => 'Siswa Kurang',
                'class' => 'XII RPL 1',
                'initial_deposit' => 5000,
            ]);

        $responseFail->assertSessionHasErrors('initial_deposit');

        // 2. Initial deposit exactly 10000 must succeed
        $responseExact = $this->actingAs($admin, 'web')
            ->post(route('admin.customers.store'), [
                'nis' => '2026998',
                'name' => 'Siswa Pas Sepuluh Ribu',
                'class' => 'XII RPL 1',
                'initial_deposit' => 10000,
            ]);

        $responseExact->assertRedirect(route('admin.customers'));
        $responseExact->assertSessionHas('success');
    }

    public function test_customer_created_with_initial_deposit_and_recorded_in_transactions_and_journals(): void
    {
        $admin = $this->createAdmin();
        $teller = $this->createTeller();

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.customers.store'), [
                'nis' => '2026888',
                'name' => 'Siswa Sukses',
                'class' => 'XII RPL 1',
                'initial_deposit' => 25000,
            ]);

        $response->assertRedirect(route('admin.customers'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('new_customer_credentials');

        $credentials = session('new_customer_credentials');
        $this->assertEquals(25000, $credentials['balance']);

        // Check account balance in database
        $this->assertDatabaseHas('bank_accounts', [
            'account_number' => $credentials['account_number'],
            'balance' => 25000,
            'status' => 'active',
        ]);

        // Check transaction record
        $transaction = Transaction::where('amount', 25000)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('deposit', $transaction->type);
        $this->assertEquals($teller->id, $transaction->teller_id, 'Initial deposit must be credited to operational teller cash drawer');

        // Check journal entries (debit 101 Kas, credit 201 Tabungan)
        $this->assertDatabaseHas('journal_entries', [
            'transaction_id' => $transaction->id,
            'account_code' => '101',
            'type' => 'debit',
            'amount' => 25000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'transaction_id' => $transaction->id,
            'account_code' => '201',
            'type' => 'credit',
            'amount' => 25000,
        ]);
    }

    public function test_admin_dashboard_counts_only_active_accounts_for_rekening_aktif_stat(): void
    {
        $admin = $this->createAdmin();

        $customer1 = \App\Models\Customer::create([
            'nis' => '1111111',
            'name' => 'Nasabah Aktif',
            'class' => 'XII RPL 1',
        ]);
        \App\Models\BankAccount::create([
            'customer_id' => $customer1->id,
            'account_number' => 'RPL-101',
            'balance' => 50000,
            'status' => 'active',
        ]);

        $customer2 = \App\Models\Customer::create([
            'nis' => '2222222',
            'name' => 'Nasabah Nonaktif',
            'class' => 'XII RPL 2',
        ]);
        \App\Models\BankAccount::create([
            'customer_id' => $customer2->id,
            'account_number' => 'RPL-102',
            'balance' => 20000,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('admin.dashboard'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['total_accounts'], 'Rekening aktif stat must only count active accounts');
        $this->assertEquals(2, $stats['total_all_accounts'], 'Total all accounts stat must count all accounts');
    }

    public function test_journal_service_and_admin_journals_paginates_25_items(): void
    {
        $admin = $this->createAdmin();
        $journalService = app(\App\Services\JournalService::class);

        $paginator = $journalService->getAllJournals();
        $this->assertEquals(25, $paginator->perPage());

        $response = $this->actingAs($admin, 'web')->get(route('admin.journals'));
        $response->assertOk();
        $journals = $response->viewData('journals');
        $this->assertEquals(25, $journals->perPage());
    }
}

