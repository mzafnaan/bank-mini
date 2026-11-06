<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\CustomerService;
use App\Services\TellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileWithdrawalAuthorizationTest extends TestCase
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

    protected function createTeller(): User
    {
        return User::create([
            'name' => 'Teller Satu',
            'username' => 'teller1',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'status' => 'active',
        ]);
    }

    public function test_mobile_pending_withdrawal_authorization_flow(): void
    {
        $teller = $this->createTeller();

        $customer = $this->customerService->createCustomer([
            'nis' => '12345',
            'name' => 'Ahmad Mobile',
            'class' => 'XII RPL 2',
            'phone' => '081234567890',
        ]);

        $account = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        // Initial deposit
        $this->tellerService->processDeposit($teller->id, $account->account_number, 50000);

        // Teller initiates withdrawal request (Option 1)
        $withdrawalReq = $this->tellerService->createWithdrawalRequest($teller->id, $account->account_number, 20000);

        // Authenticate as customer via Sanctum
        Sanctum::actingAs($customerAccount);

        // 1. Customer checks pending requests
        $response = $this->getJson('/api/v1/nasabah/withdrawal-requests/pending');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $withdrawalReq->id,
                    'amount' => 20000,
                    'status' => 'waiting',
                ],
            ]);

        // 2. Customer authorizes with wrong PIN
        $wrongPinResponse = $this->postJson("/api/v1/nasabah/withdrawal-requests/{$withdrawalReq->id}/authorize", [
            'pin' => '999999',
        ]);
        $wrongPinResponse->assertStatus(422)
            ->assertJson(['success' => false]);

        // Request should still be waiting
        $this->assertEquals('waiting', $withdrawalReq->fresh()->status);

        // 3. Customer authorizes with correct PIN (default plain_pin is set by customerService)
        $correctPin = $customer->plain_pin;
        $authResponse = $this->postJson("/api/v1/nasabah/withdrawal-requests/{$withdrawalReq->id}/authorize", [
            'pin' => $correctPin,
        ]);

        $authResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'amount' => 20000,
                    'balance_after' => 30000,
                ],
            ]);

        // Verify withdrawal request status is approved
        $this->assertEquals('approved', $withdrawalReq->fresh()->status);
        $this->assertEquals(30000, $account->fresh()->balance);

        // 4. Teller checks status endpoint
        $this->actingAs($teller, 'web');
        $statusResponse = $this->getJson("/teller/withdrawal-requests/{$withdrawalReq->id}/status");
        $statusResponse->assertStatus(200)
            ->assertJson([
                'id' => $withdrawalReq->id,
                'status' => 'approved',
            ]);
        $this->assertNotNull($statusResponse->json('receipt_url'));
    }

    public function test_mobile_pending_withdrawal_rejection(): void
    {
        $teller = $this->createTeller();

        $customer = $this->customerService->createCustomer([
            'nis' => '12346',
            'name' => 'Siti Tolak',
            'class' => 'XII RPL 2',
            'phone' => '081234567891',
        ]);

        $account = $customer->bankAccount;
        $customerAccount = $customer->customerAccount;

        $this->tellerService->processDeposit($teller->id, $account->account_number, 50000);
        $withdrawalReq = $this->tellerService->createWithdrawalRequest($teller->id, $account->account_number, 15000);

        Sanctum::actingAs($customerAccount);

        $rejectResponse = $this->postJson("/api/v1/nasabah/withdrawal-requests/{$withdrawalReq->id}/reject");
        $rejectResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals('rejected', $withdrawalReq->fresh()->status);
        $this->assertEquals(50000, $account->fresh()->balance);
    }
}

