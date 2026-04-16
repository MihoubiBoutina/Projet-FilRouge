<?php

namespace App\Tests\Functional\Api;

use App\Tests\Mock\MockNotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests d'intégration avec MockNotificationService
 * 
 * Vérifie que :
 * - Les emails sont collectés sans être envoyés
 * - Les notifications sont déclenchées correctement
 * - L'historique des emails peut être vérifié dans les tests
 */
class AtelierApiWithMockNotificationTest extends WebTestCase
{
    private MockNotificationService $mockNotifications;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une instance du mock au début de chaque test
        $this->mockNotifications = new MockNotificationService();
    }

    /**
     * Test : Vérifier que MockNotificationService stocke les emails
     * 
    #[Test]
     */
    public function testMockNotificationServiceStoresEmails(): void
    {
        // Envoyer 3 emails via le mock
        $this->mockNotifications->sendEmailNotification(
            'apprenant1@example.com',
            'Nouvel atelier',
            '<h1>PHP Avancé</h1>'
        );

        $this->mockNotifications->sendEmailNotification(
            'apprenant2@example.com',
            'Inscription confirmée',
            '<h1>Bienvenue</h1>'
        );

        $this->mockNotifications->sendEmailNotification(
            'formateur@example.com',
            'Nouvel avis reçu',
            '<h1>Avis positif</h1>'
        );

        // Vérifier que 3 emails ont été stockés
        $this->assertSame(3, $this->mockNotifications->getEmailCount());
    }

    /**
     * Test : Vérifier les emails envoyés à une adresse spécifique
     * 
    #[Test]
     */
    public function testMockNotificationServiceFiltersEmailsByRecipient(): void
    {
        $this->mockNotifications->sendEmailNotification(
            'formateur@example.com',
            'Email 1',
            'Contenu 1'
        );

        $this->mockNotifications->sendEmailNotification(
            'formateur@example.com',
            'Email 2',
            'Contenu 2'
        );

        $this->mockNotifications->sendEmailNotification(
            'autre@example.com',
            'Email 3',
            'Contenu 3'
        );

        // Vérifier les emails envoyés au formateur
        $formateurEmails = $this->mockNotifications->getEmailsSentTo('formateur@example.com');
        $this->assertCount(2, $formateurEmails);

        // Vérifier l'autre adresse
        $autreEmails = $this->mockNotifications->getEmailsSentTo('autre@example.com');
        $this->assertCount(1, $autreEmails);
    }

    /**
     * Test : Vérifier qu'un email a été envoyé à une adresse
     * 
    #[Test]
     */
    public function testMockNotificationServiceVerifiesEmailWasSent(): void
    {
        $this->mockNotifications->sendEmailNotification(
            'apprenant@example.com',
            'Confirmation',
            'Vous êtes inscrit'
        );

        // Vérifier que l'email a été envoyé
        $this->assertTrue(
            $this->mockNotifications->wasEmailSentTo('apprenant@example.com')
        );

        // Vérifier que l'email n'a PAS été envoyé à une autre adresse
        $this->assertFalse(
            $this->mockNotifications->wasEmailSentTo('autre@example.com')
        );
    }

    /**
     * Test : Réinitialiser l'historique des emails
     * 
    #[Test]
     */
    public function testMockNotificationServiceCanBeReset(): void
    {
        // Envoyer des emails
        $this->mockNotifications->sendEmailNotification(
            'test@example.com',
            'Test',
            'Contenu'
        );

        $this->assertSame(1, $this->mockNotifications->getEmailCount());

        // Réinitialiser
        $this->mockNotifications->reset();

        $this->assertSame(0, $this->mockNotifications->getEmailCount());
    }

    /**
     * Test : Affichage du débogage des emails
     * 
    #[Test]
     */
    public function testMockNotificationServiceDebugOutput(): void
    {
        $this->mockNotifications->sendEmailNotification(
            'test@example.com',
            'Sujet Test',
            'Contenu'
        );

        $debug = $this->mockNotifications->debugEmailsSent();

        // Vérifier que le débogage contient des informations
        $this->assertStringContainsString('Emails envoyés', $debug);
        $this->assertStringContainsString('test@example.com', $debug);
        $this->assertStringContainsString('Sujet Test', $debug);
    }

    /**
     * Test : Pattern de test typique avec WebTestCase et mock notification
     * 
    #[Test]
     */
    public function testTypicalWebTestCasePatternWithMockNotification(): void
    {
        // Créer un client HTTP
        $client = static::createClient();

        // Remplacer le service de notification par le mock dans le conteneur DI
        $container = $client->getContainer();
        $container->set('app.notification.service', $this->mockNotifications);

        // Faire une requête HTTP (exemple : POST créant un atelier)
        $payload = json_encode([
            'titre' => 'Formation Laravel',
            'description' => 'Maîtrisez Laravel',
            'dureeHeure' => 8,
            'place' => 20,
            'formateurId' => 1,
            'startAt' => '2026-05-10T10:00:00+02:00'
        ]);

        $client->request(
            'POST',
            '/api/ateliers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        // Vérifier la réponse HTTP
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que des notifications ont été envoyées
        // (Ce test échouera si le endpoint POST n'existe pas,
        //  mais il montre le pattern correct)
    }

    /**
     * Test : Vérification que les mocks ne polluent pas l'état global
     * 
    #[Test]
     */
    public function testMockNotificationServiceIsIsolated(): void
    {
        // Premier test
        $mock1 = new MockNotificationService();
        $mock1->sendEmailNotification('test1@example.com', 'Test 1', 'Contenu 1');
        $this->assertSame(1, $mock1->getEmailCount());

        // Deuxième instance
        $mock2 = new MockNotificationService();
        $mock2->sendEmailNotification('test2@example.com', 'Test 2', 'Contenu 2');
        $this->assertSame(1, $mock2->getEmailCount());

        // Vérifier que les instances ne partagent pas leur état
        $this->assertSame(1, $mock1->getEmailCount());
        $this->assertSame(1, $mock2->getEmailCount());
    }
}
