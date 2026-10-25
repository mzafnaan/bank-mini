<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyCustomerEmailNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $verificationUrl,
        public string $customerName
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Alamat Email - Bank Mini')
            ->view('emails.verify-customer-email', [
                'verificationUrl' => $this->verificationUrl,
                'customerName' => $this->customerName,
            ]);
    }
}
