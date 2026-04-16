<?php

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

class AtelierApiFunctionalTest extends WebTestCase
{
    /**
     * Test 1 : GET /api/ateliers retourne 200 et header JSON
     * 
    #[Test]
     */
    public function testGetAteliersReturns200WithJsonHeader(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers');

        // Vérifier le code 200
        $this->assertResponseStatusCodeSame(200);

        // Vérifier le header JSON
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    /**
     * Test 2 : GET /api/ateliers retourne un JSON valide
     * 
    #[Test]
     */
    public function testGetAteliersReturnsValidJsonStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ateliers');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('ateliers', $responseData);
    }

    /**
     * Test 3 : GET /api/search avec paramètres valides
     * 
    #[Test]
     */
    public function testGetSearchWithValidParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', [
            'titre' => 'PHP',
            'duree' => '8',
            'sort' => 'titre',
            'order' => 'DESC'
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('query', $responseData);
        $this->assertEquals('PHP', $responseData['query']['titre']);
    }

    /**
     * Test 4 : GET /api/search avec paramètres invalides
     * 
    #[Test]
     */
    public function testGetSearchWithInvalidSortParameter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/search', [
            'sort' => 'invalid_field'
        ]);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Le tri invalide doit être remplacé par 'date'
        $this->assertEquals('date', $responseData['query']['sort']);
    }

    /**
     * Test 5 : GET /api/avis retourne 200
     * 
    #[Test]
     */
    public function testGetAvisReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/avis');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    /**
     * Test 6 : GET /api/avis avec filtrage par note
     * 
    #[Test]
     */
    public function testGetAvisFilterByNote(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/avis', ['note' => '5']);

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('avis', $responseData);
        $this->assertIsArray($responseData['avis']);
    }
}
