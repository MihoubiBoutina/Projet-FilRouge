<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class NotificationService implements NotificationServiceInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendEmailNotification(string $email, string $subject, string $body): void
    {
        $emailMessage = (new Email())
            ->from('noreply@myprof.local')
            ->to($email)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($emailMessage);
    }
}
