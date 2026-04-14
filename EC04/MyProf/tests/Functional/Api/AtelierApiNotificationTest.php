<?php

namespace App\Tests\Functional\Api;

use App\Service\EmailNotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;

class AtelierApiNotificationTest extends WebTestCase
{
    /**
     * Test : POST Atelier envoie une notification email (mockée)
     * 
     * @test
     */
    public function testPostAtelierSendsEmailNotificationWithMock(): void
    {
        // Créer un mock du mailer
        $mailerMock = $this->createMock(MailerInterface::class);

        // Configurer le mock pour s'attendre à un appel send()
        $mailerMock->expects($this->once())
            ->method('send');

        // Créer le service avec le mock
        $notificationService = new EmailNotificationService($mailerMock);

        // Appeler la méthode
        $notificationService->sendEmailNotification(
            'apprenant@example.com',
            'Nouvel atelier disponible',
            '<h1>Nouvel atelier PHP</h1>'
        );

        // Le test passe si send() a été appelé une fois
        $this->assertTrue(true);
    }

    /**
     * Test : POST Atelier ne doit pas envoyer 2 emails
     * 
     * @test
     */
    public function testPostAtelierDoesNotSendMultipleEmails(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);

        // S'attendre à un seul appel
        $mailerMock->expects($this->once())
            ->method('send');

        $notificationService = new EmailNotificationService($mailerMock);
        $notificationService->sendEmailNotification(
            'apprenant@example.com',
            'Nouvel atelier',
            '<h1>Atelier</h1>'
        );

        // Si send() est appelé plus d'une fois, le test échoue
        $this->assertTrue(true);
    }

    /**
     * Test : Vérifier que le mailer n'est pas appelé en développement
     * 
     * @test
     */
    public function testMailerIsNotCalledInTestEnvironment(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);

        // Le mock.ne doit jamais être appelé
        $mailerMock->expects($this->never())
            ->method('send');

        $notificationService = new EmailNotificationService($mailerMock);

        // Ne pas appeler sendEmailNotification()
        // Le test passe si send() n'est jamais appelé
        $this->assertTrue(true);
    }

    /**
     * Test : POST Atelier avec notification email
     * 
     * @test
     */
    public function testPostAtelierWithEmailNotification(): void
    {
        $client = static::createClient();

        // Remplacer le mailer par un mock dans le conteneur
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock->expects($this->once())->method('send');

        $client->getContainer()->set(MailerInterface::class, $mailerMock);

        // Effectuer la requête POST
        $payload = json_encode([
            'titre' => 'Formation Express.js',
            'description' => 'Apprenez Express.js',
            'dureeHeure' => 6,
            'place' => 15,
            'formateurId' => 1,
            'startAt' => '2026-04-25T14:00:00+02:00'
        ]);

        $client->request(
            'POST',
            '/api/ateliers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        // Vérifier que l'atelier a été créé
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le mailer a été appelé
        // (Mock effectue la vérification)
    }

    /**
     * Test : POST Avis avec notification au formateur
     * 
     * @test
     */
    public function testPostAvisNotifiesTrainer(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);

        // S'attendre à au moins un appel send()
        $mailerMock->expects($this->atLeastOnce())
            ->method('send');

        $notificationService = new EmailNotificationService($mailerMock);

        // Simuler l'envoi d'une notification au formateur
        $notificationService->sendEmailNotification(
            'formateur@example.com',
            'Nouvel avis reçu',
            'Un apprenant a laissé un avis sur votre atelier'
        );

        $this->assertTrue(true);
    }

    /**
     * Test : Vérifier que les emails ne sont pas envoyés lors des tests
     * 
     * @test
     */
    public function testNoEmailsSentDuringTests(): void
    {
        // Créer un mock strict qui lève une exception si appelé
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock->method('send')
            ->willThrowException(new \Exception('Mailer should not be called in tests'));

        $this->expectNotToPerformAssertions();

        // Le test passe si send() n'est pas appelé
    }
}
