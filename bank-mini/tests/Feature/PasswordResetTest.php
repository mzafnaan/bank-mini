<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerService $customerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = app(CustomerService::class);
    }

    protected function createInternalUser(string $role, string $username, ?string $email = null, bool $verified = true, string $status = 'active'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' User',
            'username' => $username,
            'email' => $email ?? "{$username}@bankmini.test",
            'email_verified_at' => $verified ? now() : null,
            'password' => Hash::make('password123'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    protected function createCustomerWithAccount(?string $email = null, bool $verified = true, string $status = 'active'): Customer
    {
        $customer = $this->customerService->createCustomer([
            'nis' => (string) random_int(10000, 99999),
            'name' => 'Nasabah Test',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
            'email' => $email,
        ]);

        if ($email && ! $verified) {
            $customer->update(['email_verified_at' => null]);
        }

        if ($status !== 'active') {
            $customer->customerAccount->update(['status' => $status]);
        }

        return $customer->fresh(['customerAccount', 'bankAccount']);
    }

    /**
     * Skenario 1: Reset password akun internal melalui email.
     */
    public function test_internal_user_can_request_and_reset_password_via_email(): void
    {
        $user = $this->createInternalUser('teller', 'teller_reset', 'teller_reset@bankmini.test', true);

        // 1. Request reset link
        $response = $this->post(route('password.email'), [
            'email' => 'teller_reset@bankmini.test',
        ]);

        $response->assertSessionHas('status');

        // Verify token created in password_reset_tokens
        $tokenRecord = DB::table('password_reset_tokens')->where('email', 'teller_reset@bankmini.test')->first();
        $this->assertNotNull($tokenRecord);

        // Generate broker token for resetting
        $token = Password::broker('users')->createToken($user);

        // 2. Submit new password
        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'teller_reset@bankmini.test',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        // Verify user password updated
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->fresh()->password));

        // Verify can login with new password
        $loginResponse = $this->post(route('login'), [
            'username' => 'teller_reset',
            'password' => 'NewSecurePassword123!',
        ]);
        $loginResponse->assertRedirect(route('teller.dashboard'));
    }

    /**
     * Skenario 2: Reset password akun nasabah melalui email terverifikasi.
     */
    public function test_customer_can_request_and_reset_password_via_verified_email(): void
    {
        $customer = $this->createCustomerWithAccount('nasabah@bankmini.test', true);
        $customerAccount = $customer->customerAccount;

        // 1. Request reset link
        $response = $this->post(route('password.email'), [
            'email' => 'nasabah@bankmini.test',
        ]);

        $response->assertSessionHas('status');

        // Token should be created in customer_password_reset_tokens table (isolated from users)
        $tokenRecord = DB::table('customer_password_reset_tokens')->where('email', 'nasabah@bankmini.test')->first();
        $this->assertNotNull($tokenRecord);

        // Use broker token to simulate clicking reset link
        $token = Password::broker('customer_accounts')->createToken($customerAccount);

        // 2. Submit new password
        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'nasabah@bankmini.test',
            'password' => 'NasabahNewPass123!',
            'password_confirmation' => 'NasabahNewPass123!',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        // Verify password updated and first_login set to false
        $this->assertTrue(Hash::check('NasabahNewPass123!', $customerAccount->fresh()->password));
        $this->assertFalse((bool) $customerAccount->fresh()->first_login);
    }

    /**
     * Skenario 3: Nasabah tanpa email menggunakan prosedur Admin.
     */
    public function test_customer_without_email_can_be_reset_by_admin(): void
    {
        $admin = $this->createInternalUser('administrator', 'admin_boss');
        $customer = $this->createCustomerWithAccount(null, false); // no email
        $customerAccount = $customer->customerAccount;

        // Admin resets customer password
        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.customers.reset-password', $customer));

        $response->assertRedirect(route('admin.customers'));
        $response->assertSessionHas('reset_password_credentials');

        $credentials = session('reset_password_credentials');
        $this->assertEquals($customer->name, $credentials['name']);
        $this->assertEquals($customerAccount->username, $credentials['username']);
        $this->assertNotEmpty($credentials['password']);

        // Verify password in DB matches generated temporary password
        $this->assertTrue(Hash::check($credentials['password'], $customerAccount->fresh()->password));

        // first_login must be set to true
        $this->assertTrue((bool) $customerAccount->fresh()->first_login);

        // Customer can log in with temporary password
        $loginResponse = $this->post(route('login'), [
            'username' => $customerAccount->username,
            'password' => $credentials['password'],
        ]);
        $loginResponse->assertRedirect();
    }

    /**
     * Skenario 4: Email yang tidak valid atau tidak terdaftar.
     */
    public function test_unregistered_email_shows_generic_response_without_leaking(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'tidak.terdaftar@bankmini.test',
        ]);

        // Generic response to prevent email enumeration
        $response->assertSessionHas('status');

        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'tidak.terdaftar@bankmini.test')->first());
        $this->assertNull(DB::table('customer_password_reset_tokens')->where('email', 'tidak.terdaftar@bankmini.test')->first());
    }

    /**
     * Skenario 5: Email yang belum diverifikasi.
     */
    public function test_unverified_email_does_not_receive_reset_token(): void
    {
        $this->createInternalUser('teller', 'unverified_teller', 'unverified@bankmini.test', false);

        $response = $this->post(route('password.email'), [
            'email' => 'unverified@bankmini.test',
        ]);

        $response->assertSessionHas('status');

        // No token created for unverified email
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'unverified@bankmini.test')->first());
    }

    /**
     * Skenario 6: Token reset kedaluwarsa.
     */
    public function test_expired_reset_token_is_rejected(): void
    {
        $user = $this->createInternalUser('supervisor', 'spv_expire', 'spv_expire@bankmini.test', true);

        // Insert token created 65 minutes ago (expire is 60 minutes)
        DB::table('password_reset_tokens')->insert([
            'email' => 'spv_expire@bankmini.test',
            'token' => Hash::make('expired_token_123'),
            'created_at' => now()->subMinutes(65),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'expired_token_123',
            'email' => 'spv_expire@bankmini.test',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Skenario 7: Token reset digunakan kembali.
     */
    public function test_reset_token_cannot_be_reused(): void
    {
        $user = $this->createInternalUser('teller', 'teller_reuse', 'reuse@bankmini.test', true);
        $token = Password::broker('users')->createToken($user);

        // 1st reset - succeeds
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reuse@bankmini.test',
            'password' => 'FirstResetPass123!',
            'password_confirmation' => 'FirstResetPass123!',
        ])->assertSessionHas('success');

        // 2nd reset with same token - must fail
        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reuse@bankmini.test',
            'password' => 'SecondResetPass123!',
            'password_confirmation' => 'SecondResetPass123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Skenario 8: Password baru tidak memenuhi ketentuan.
     */
    public function test_invalid_new_password_is_rejected(): void
    {
        $user = $this->createInternalUser('teller', 'teller_val', 'val@bankmini.test', true);
        $token = Password::broker('users')->createToken($user);

        // Less than 6 characters
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'val@bankmini.test',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors('password');

        // Confirmation mismatch
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'val@bankmini.test',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ])->assertSessionHasErrors('password');
    }

    /**
     * Skenario 9: Rate limiting pada permintaan reset password.
     */
    public function test_rate_limiting_protects_forgot_password(): void
    {
        // 5 requests per minute allowed by throttle:5,1
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => 'test@bankmini.test']);
        }

        // 6th request should hit 429 Too Many Requests
        $response = $this->post(route('password.email'), ['email' => 'test@bankmini.test']);
        $response->assertStatus(429);
    }

    /**
     * Skenario 10: Akun tidak aktif tidak dapat melakukan reset password.
     */
    public function test_inactive_account_cannot_reset_password(): void
    {
        $this->createInternalUser('teller', 'inactive_teller', 'inactive@bankmini.test', true, 'inactive');

        $this->post(route('password.email'), [
            'email' => 'inactive@bankmini.test',
        ]);

        // No token issued for inactive user
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'inactive@bankmini.test')->first());
    }

    /**
     * Skenario 11: Password baru dapat digunakan untuk login web dan API.
     */
    public function test_new_password_can_be_used_for_web_and_mobile_api_login(): void
    {
        $customer = $this->createCustomerWithAccount('customer_api@bankmini.test', true);
        $customerAccount = $customer->customerAccount;

        $token = Password::broker('customer_accounts')->createToken($customerAccount);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'customer_api@bankmini.test',
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        // 1. Web Login
        $webLogin = $this->post(route('login'), [
            'username' => $customerAccount->username,
            'password' => 'BrandNewPassword123!',
        ]);
        $webLogin->assertRedirect(route('customer.dashboard'));

        // 2. Mobile API Login (Sanctum)
        $apiLogin = $this->postJson('/api/v1/auth/login', [
            'username' => $customerAccount->username,
            'password' => 'BrandNewPassword123!',
        ]);

        $apiLogin->assertStatus(200);
        $apiLogin->assertJsonStructure(['success', 'message', 'data' => ['access_token', 'token_type', 'account']]);
    }

    /**
     * Skenario 12: Token Sanctum lama tidak dapat digunakan setelah password berhasil diubah.
     */
    public function test_sanctum_tokens_are_revoked_after_password_reset(): void
    {
        $customer = $this->createCustomerWithAccount('sanctum_rev@bankmini.test', true);
        $customerAccount = $customer->customerAccount;

        // Create Sanctum personal access token
        $sanctumToken = $customerAccount->createToken('test-mobile-token')->plainTextToken;

        $this->assertCount(1, $customerAccount->fresh()->tokens);

        // Perform password reset via Password Broker
        $token = Password::broker('customer_accounts')->createToken($customerAccount);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'sanctum_rev@bankmini.test',
            'password' => 'UpdatedPassword123!',
            'password_confirmation' => 'UpdatedPassword123!',
        ]);

        // Old Sanctum token must be revoked (deleted)
        $this->assertCount(0, $customerAccount->fresh()->tokens);

        // Request using old Sanctum token must be rejected (401 Unauthorized)
        $response = $this->withHeader('Authorization', "Bearer {$sanctumToken}")
            ->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    /**
     * Skenario 13: Nasabah tidak dapat mengubah password akun milik nasabah lain.
     */
    public function test_customer_cannot_update_another_customer_password(): void
    {
        $customerA = $this->createCustomerWithAccount('customerA@bankmini.test', true);
        $customerB = $this->createCustomerWithAccount('customerB@bankmini.test', true);

        // Log in as Customer A
        $this->actingAs($customerA->customerAccount, 'customer_web');

        // Customer A updates password via customer.security.password
        $this->post(route('customer.security.password'), [
            'current_password' => 'Bk' . $customerA->nis . '!',
            'new_password' => 'CustomerANewPass123!',
            'new_password_confirmation' => 'CustomerANewPass123!',
        ]);

        // Verify Customer A's password changed, but Customer B's password remains unchanged
        $this->assertTrue(Hash::check('CustomerANewPass123!', $customerA->customerAccount->fresh()->password));
        $this->assertTrue(Hash::check('Bk' . $customerB->nis . '!', $customerB->customerAccount->fresh()->password));
    }

    /**
     * Skenario 14: Pengguna tanpa izin tidak dapat melakukan reset password melalui endpoint Admin.
     */
    public function test_unauthorized_users_cannot_access_admin_reset_endpoint(): void
    {
        $customer = $this->createCustomerWithAccount();
        $teller = $this->createInternalUser('teller', 'teller_unauth');
        $supervisor = $this->createInternalUser('supervisor', 'spv_unauth');

        // 1. Guest
        $this->post(route('admin.customers.reset-password', $customer))
            ->assertRedirect(route('login'));

        // 2. Teller
        $this->actingAs($teller, 'web')
            ->post(route('admin.customers.reset-password', $customer))
            ->assertStatus(403);

        // 3. Supervisor
        $this->actingAs($supervisor, 'web')
            ->post(route('admin.customers.reset-password', $customer))
            ->assertStatus(403);

        // 4. Customer
        $this->actingAs($customer->customerAccount, 'customer_web')
            ->post(route('admin.customers.reset-password', $customer))
            ->assertStatus(403);
    }
}
