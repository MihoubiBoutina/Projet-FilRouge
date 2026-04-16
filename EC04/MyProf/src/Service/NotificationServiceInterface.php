<?php

namespace App\Service;

interface NotificationServiceInterface
{
    public function sendEmailNotification(string $email, string $subject, string $body): void;
}
