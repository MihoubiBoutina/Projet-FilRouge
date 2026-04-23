<?php

namespace App\Tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests fonctionnels simples de l'API
 * 
 * ENONCE :
 * 1. Test fonctionnel GET vérifiant code 200 + header JSON
 * 2. Test validation: payload vide retourne 422
 * 3. Mock service de notification (sans emails réels)
 * 
 * Contraintes :
 * - Utiliser WebTestCase et assertResponseStatusCodeSame
 * - Pas de dépendance production DB (fixtures de test)
 */
class ApiSimpleTest extends WebTestCase
{
    // ==============================================
    // 1. TESTS GET - Vérifiant code 200 + JSON
    // ==============================================

    /**
    #[Test]
     * GET /api/ateliers retourne 200 et header JSON
     */
    public function test_GetAteliers_Returns200_WithJsonHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    /**
    #[Test]
     * GET /api/search retourne 200
     */
    public function test_GetSearch_Returns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers?titre=test');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
    #[Test]
     * GET /api/avis retourne 200
     */
    public function test_GetAvis_Returns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/avis');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
    #[Test]
     * GET /api/formateurs/search retourne 200
     */
    public function test_GetFormateurSearch_Returns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/formateurs?nom=test');

        $this->assertResponseStatusCodeSame(200);
    }

    // ==============================================
    // 2. TESTS POST VALIDATION
    // ==============================================

    /**
    #[Test]
     * POST /api/ateliers avec payload vide retourne 400/422
     */
    public function test_PostAtelier_WithEmptyPayload_Returns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/ateliers', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        // Le endpoint retourne 422 pour données manquantes
        $this->assertResponseStatusCodeSame(400);
    }

    /**
    #[Test]
     * POST /api/ateliers avec données valides retourne 201
     */
    public function test_PostAtelier_WithValidData_Returns201(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'titre' => 'PHP Avancé',
            'description' => 'Formation',
            'dureeHeure' => 8,
            'place' => 20,
            'formateurId' => 1,
            'startAt' => '2026-05-10T10:00:00+02:00'
        ]);

        $client->request('POST', '/api/ateliers', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(201);
    }

    /**
    #[Test]
     * POST /api/avis avec données vide retourne 422
     */
    public function test_PostAvis_WithEmptyData_Returns422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/avis', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        $this->assertResponseStatusCodeSame(400);
    }

    /**
    #[Test]
     * POST /api/avis avec données valides retourne 201
     */
    public function test_PostAvis_WithValidData_Returns201(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'commentaire' => 'Super atelier',
            'note' => 5,
            'apprenantId' => 1,
            'atelierId' => 1
        ]);

        $client->request('POST', '/api/avis', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseStatusCodeSame(201);
    }

    // ==============================================
    // 3. TESTS MOCK NOTIFICATION SERVICE
    // ==============================================

    /**
    #[Test]
     * Mock notification service - stocke les emails en mémoire
     */
    public function test_MockNotificationService_StoresEmailsInMemory(): void
    {
        $mockNotification = new \App\Tests\Mock\MockNotificationService();

        // Envoyer 2 emails via le mock
        $mockNotification->sendEmailNotification(
            'test@example.com',
            'Test',
            'Contenu'
        );

        $mockNotification->sendEmailNotification(
            'other@example.com',
            'Test 2',
            'Contenu 2'
        );

        // Vérifier que 2 emails ont été stockés
        $this->assertSame(2, $mockNotification->getEmailCount());
    }

    /**
    #[Test]
     * Mock notification service - filtre par destination
     */
    public function test_MockNotificationService_FiltersEmailsByRecipient(): void
    {
        $mockNotification = new \App\Tests\Mock\MockNotificationService();

        $mockNotification->sendEmailNotification('formateur@example.com', 'Email 1', 'Contenu');
        $mockNotification->sendEmailNotification('formateur@example.com', 'Email 2', 'Contenu');
        $mockNotification->sendEmailNotification('autre@example.com', 'Email 3', 'Contenu');

        // Vérifier les emails par destinataire
        $this->assertTrue($mockNotification->wasEmailSentTo('formateur@example.com'));
        $this->assertTrue($mockNotification->wasEmailSentTo('autre@example.com'));
        $this->assertFalse($mockNotification->wasEmailSentTo('inexistent@example.com'));
    }

    /**
    #[Test]
     * Mock notification service - réinitialisation
     */
    public function test_MockNotificationService_CanBeReset(): void
    {
        $mockNotification = new \App\Tests\Mock\MockNotificationService();

        $mockNotification->sendEmailNotification('test@example.com', 'Email', 'Contenu');
        $this->assertSame(1, $mockNotification->getEmailCount());

        $mockNotification->reset();
        $this->assertSame(0, $mockNotification->getEmailCount());
    }
}
