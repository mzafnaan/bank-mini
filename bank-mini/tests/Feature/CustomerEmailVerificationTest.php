<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Notifications\VerifyCustomerEmailNotification;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerService $customerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerService = app(CustomerService::class);
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        $data = array_merge([
            'nis' => (string) random_int(10000, 99999),
            'name' => 'Nasabah Email Test',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
        ], $attributes);

        $customer = $this->customerService->createCustomer($data);
        return $customer->fresh(['customerAccount', 'bankAccount']);
    }

    /**
     * Test nasabah dapat memasukkan email baru dan sistem mengirimkan tautan verifikasi.
     */
    public function test_customer_can_request_email_verification(): void
    {
        Notification::fake();

        $customer = $this->createCustomer();
        $customerAccount = $customer->customerAccount;

        $response = $this->actingAs($customerAccount, 'customer_web')
            ->post(route('customer.security.email'), [
                'email' => 'nasabah.baru@gmail.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // pending_email tercatat, email aktif masih null
        $this->assertEquals('nasabah.baru@gmail.com', $customer->fresh()->pending_email);
        $this->assertNull($customer->fresh()->email);
        $this->assertNull($customer->fresh()->email_verified_at);

        // Notifikasi verifikasi terkirim
        Notification::assertSentOnDemand(
            VerifyCustomerEmailNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'nasabah.baru@gmail.com'
                    && str_contains($notification->verificationUrl, 'email/verify');
            }
        );
    }

    /**
     * Test nasabah membuka tautan verifikasi dan email berhasil aktif untuk reset password.
     */
    public function test_customer_can_verify_email_via_signed_link(): void
    {
        $customer = $this->createCustomer();
        $customer->update([
            'pending_email' => 'nasabah.aktif@gmail.com',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'customer.email.verify',
            now()->addMinutes(60),
            ['customer' => $customer->id, 'email' => 'nasabah.aktif@gmail.com']
        );

        $response = $this->actingAs($customer->customerAccount, 'customer_web')
            ->get($signedUrl);

        $response->assertRedirect(route('customer.security'));
        $response->assertSessionHas('success');

        $refreshedCustomer = $customer->fresh();
        $this->assertEquals('nasabah.aktif@gmail.com', $refreshedCustomer->email);
        $this->assertNotNull($refreshedCustomer->email_verified_at);
        $this->assertNull($refreshedCustomer->pending_email);

        // Email sekarang dapat digunakan untuk request reset password
        $forgotResponse = $this->post(route('password.email'), [
            'email' => 'nasabah.aktif@gmail.com',
        ]);
        $forgotResponse->assertSessionHas('status');

        $this->assertNotNull(
            DB::table('customer_password_reset_tokens')->where('email', 'nasabah.aktif@gmail.com')->first()
        );
    }

    /**
     * Test alur ganti email: email lama tetap menjadi email pemulihan selama email baru belum diverifikasi.
     */
    public function test_old_email_remains_active_recovery_email_while_new_email_is_pending(): void
    {
        Notification::fake();

        $customer = $this->createCustomer();
        $customer->update([
            'email' => 'lama@gmail.com',
            'email_verified_at' => now(),
        ]);

        // Nasabah meminta ganti email baru
        $this->actingAs($customer->customerAccount, 'customer_web')
            ->post(route('customer.security.email'), [
                'email' => 'baru@gmail.com',
            ]);

        $refreshedCustomer = $customer->fresh();
        $this->assertEquals('lama@gmail.com', $refreshedCustomer->email);
        $this->assertEquals('baru@gmail.com', $refreshedCustomer->pending_email);

        // 1. Reset password menggunakan email lama: HARUS BERHASIL
        $this->post(route('password.email'), [
            'email' => 'lama@gmail.com',
        ])->assertSessionHas('status');
        $this->assertNotNull(
            DB::table('customer_password_reset_tokens')->where('email', 'lama@gmail.com')->first()
        );

        // 2. Reset password menggunakan email baru (belum diverifikasi): TIDAK MENGELUARKAN TOKEN
        $this->post(route('password.email'), [
            'email' => 'baru@gmail.com',
        ]);
        $this->assertNull(
            DB::table('customer_password_reset_tokens')->where('email', 'baru@gmail.com')->first()
        );
    }

    /**
     * Test setelah email baru diverifikasi, email baru menggantikan email lama.
     */
    public function test_new_email_replaces_old_email_after_verification(): void
    {
        $customer = $this->createCustomer();
        $customer->update([
            'email' => 'lama@gmail.com',
            'email_verified_at' => now(),
            'pending_email' => 'baru.terverifikasi@gmail.com',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'customer.email.verify',
            now()->addMinutes(60),
            ['customer' => $customer->id, 'email' => 'baru.terverifikasi@gmail.com']
        );

        $this->actingAs($customer->customerAccount, 'customer_web')
            ->get($signedUrl)
            ->assertRedirect(route('customer.security'))
            ->assertSessionHas('success');

        $refreshedCustomer = $customer->fresh();
        $this->assertEquals('baru.terverifikasi@gmail.com', $refreshedCustomer->email);
        $this->assertNull($refreshedCustomer->pending_email);

        // Email baru sekarang dapat digunakan untuk reset password
        $this->post(route('password.email'), [
            'email' => 'baru.terverifikasi@gmail.com',
        ])->assertSessionHas('status');

        $this->assertNotNull(
            DB::table('customer_password_reset_tokens')->where('email', 'baru.terverifikasi@gmail.com')->first()
        );
    }

    /**
     * Test nasabah dapat mengirim ulang tautan verifikasi.
     */
    public function test_customer_can_resend_verification_email(): void
    {
        Notification::fake();

        $customer = $this->createCustomer();
        $customer->update([
            'pending_email' => 'pending.resend@gmail.com',
        ]);

        $response = $this->actingAs($customer->customerAccount, 'customer_web')
            ->post(route('customer.security.email.resend'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Notification::assertSentOnDemand(
            VerifyCustomerEmailNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'pending.resend@gmail.com';
            }
        );
    }

    /**
     * Test nasabah dapat membatalkan permintaan perubahan email tertunda.
     */
    public function test_customer_can_cancel_pending_email(): void
    {
        $customer = $this->createCustomer();
        $customer->update([
            'email' => 'asli@gmail.com',
            'email_verified_at' => now(),
            'pending_email' => 'dibatalkan@gmail.com',
        ]);

        $response = $this->actingAs($customer->customerAccount, 'customer_web')
            ->delete(route('customer.security.email.cancel'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNull($customer->fresh()->pending_email);
        $this->assertEquals('asli@gmail.com', $customer->fresh()->email);
    }

    /**
     * Test tautan verifikasi dengan signature palsu / tidak valid ditolak.
     */
    public function test_tampered_verification_link_is_rejected(): void
    {
        $customer = $this->createCustomer();
        $customer->update(['pending_email' => 'target@gmail.com']);

        // Akses URL tanpa signature yang sah
        $invalidUrl = route('customer.email.verify', [
            'customer' => $customer->id,
            'email' => 'target@gmail.com',
            'signature' => 'invalid_signature_hash',
        ]);

        $response = $this->actingAs($customer->customerAccount, 'customer_web')
            ->get($invalidUrl);

        $response->assertStatus(403);

        // Email tidak berubah
        $this->assertNull($customer->fresh()->email);
    }

    /**
     * Test validasi jika memasukkan email yang sama dengan email aktif saat ini.
     */
    public function test_submitting_same_active_email_is_rejected(): void
    {
        $customer = $this->createCustomer();
        $customer->update([
            'email' => 'sama@gmail.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($customer->customerAccount, 'customer_web')
            ->post(route('customer.security.email'), [
                'email' => 'sama@gmail.com',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test validasi jika memasukkan email yang sudah digunakan oleh nasabah lain.
     */
    public function test_submitting_email_used_by_another_customer_is_rejected(): void
    {
        $customerA = $this->createCustomer();
        $customerA->update([
            'email' => 'customerA@gmail.com',
            'email_verified_at' => now(),
        ]);

        $customerB = $this->createCustomer();

        $response = $this->actingAs($customerB->customerAccount, 'customer_web')
            ->post(route('customer.security.email'), [
                'email' => 'customerA@gmail.com',
            ]);

        $response->assertSessionHasErrors('email');
    }
}
