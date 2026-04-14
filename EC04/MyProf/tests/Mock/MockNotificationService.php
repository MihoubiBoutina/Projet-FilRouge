<?php

namespace App\Tests\Mock;

use App\Service\NotificationServiceInterface;

/**
 * Mock du service de notification pour les tests
 * 
 * Cet implémentation stocke les emails en mémoire
 * sans les envoyer réellement
 */
class MockNotificationService implements NotificationServiceInterface
{
    /**
     * @var array<array<string, string>> Historique des emails envoyés
     */
    private array $sentEmails = [];

    /**
     * Envoyer une notification (stockée en mémoire pendant les tests)
     * 
     * @param string $to Email du destinataire
     * @param string $subject Sujet du message
     * @param string $html Contenu HTML
     */
    public function sendEmailNotification(string $to, string $subject, string $html): void
    {
        $this->sentEmails[] = [
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * Récupérer tous les emails envoyés pendant le test
     * 
     * @return array<array<string, string|\DateTime>>
     */
    public function getSentEmails(): array
    {
        return $this->sentEmails;
    }

    /**
     * Récupérer le nombre d'emails envoyés
     * 
     * @return int
     */
    public function getEmailCount(): int
    {
        return \count($this->sentEmails);
    }

    /**
     * Récupérer les emails envoyés à une adresse spécifique
     * 
     * @param string $to Adresse email
     * 
     * @return array<array<string, string|\DateTime>>
     */
    public function getEmailsSentTo(string $to): array
    {
        return array_filter($this->sentEmails, static function (array $email) use ($to) {
            return $email['to'] === $to;
        });
    }

    /**
     * Vérifier si un email a été envoyé à une adresse
     * 
     * @param string $to Adresse email
     * 
     * @return bool
     */
    public function wasEmailSentTo(string $to): bool
    {
        return \count($this->getEmailsSentTo($to)) > 0;
    }

    /**
     * Réinitialiser l'historique des emails (pour nettoyer entre les tests)
     * 
     * @return void
     */
    public function reset(): void
    {
        $this->sentEmails = [];
    }

    /**
     * Afficher l'historique des emails pour le débogage
     * 
     * @return string
     */
    public function debugEmailsSent(): string
    {
        if (0 === \count($this->sentEmails)) {
            return "Aucun email envoyé";
        }

        $output = "Emails envoyés: " . \count($this->sentEmails) . "\n";
        foreach ($this->sentEmails as $index => $email) {
            $output .= sprintf(
                "[%d] À: %s | Sujet: %s | Timestamp: %s\n",
                $index + 1,
                $email['to'],
                $email['subject'],
                $email['timestamp']->format('Y-m-d H:i:s')
            );
        }

        return $output;
    }
}
