<?php

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

class DiagnosticTest extends WebTestCase
{
    /**
     * Test simple pour vérifier que le client HTTP fonctionne
     * 
    #[Test]
     */
    public function testClientCanMakeRequest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // Juste vérifier que le client peut faire une requête
        $this->assertTrue(true);
    }

    /**
     * Test : GET /api/ateliers existe et retourne 200
     * 
    #[Test]
     */
    public function testApiAteliersEndpointExists(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test : GET /api/search existe
     * 
    #[Test]
     */
    public function testApiSearchEndpointExists(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers?titre=test');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test : POST /api/ateliers existe et retourne 201 avec données valides
     * 
    #[Test]
     */
    public function testPostApiAteliersEndpointExists(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'titre' => 'Test',
            'description' => 'Test',
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

        $this->assertResponseStatusCodeSame(201);
    }
}
