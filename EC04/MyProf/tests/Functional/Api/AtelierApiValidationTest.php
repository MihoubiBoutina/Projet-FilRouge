<?php

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

class AtelierApiValidationTest extends WebTestCase
{
    /**
     * Test : POST avec payload vide retourne 400
     * 
    #[Test]
     */
    public function testPostWithEmptyPayloadReturns400(): void
    {
        $client = static::createClient();

        // Envoyer une requête POST sans données
        $client->request(
            'POST',
            '/api/ateliers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}' // Payload vide (JSON valide mais sans les champs requis)
        );

        // Vérifier l'erreur 400 ou 422
        $response = $client->getResponse()->getStatusCode();
        $this->assertContains($response, [400, 422]);
    }

    /**
     * Test : POST avec données invalides retourne 422
     * 
    #[Test]
     */
    public function testPostWithInvalidDataReturns422(): void
    {
        $client = static::createClient();

        // Envoyer un payload JSON invalide (données manquantes)
        $payload = json_encode([
            'titre' => '', // Titre vide
            // Description manquante
            // Durée manquante
        ]);

        $client->request(
            'POST',
            '/api/ateliers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        // Vérifier le code 422 (Unprocessable Entity)
        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test : POST avec données valides retourne 201
     * 
    #[Test]
     */
    public function testPostWithValidDataReturns201(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'titre' => 'PHP Avancé',
            'description' => 'Formation PHP avanzée',
            'dureeHeure' => 8,
            'place' => 20,
            'formateurId' => 1,
            'startAt' => '2026-04-20T10:00:00+02:00'
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

    /**
     * Test : POST Avis avec données manquantes retourne 422
     * 
    #[Test]
     */
    public function testPostAvisWithMissingDataReturns422(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'commentaire' => 'Super atelier',
            // Note manquante
            // apprenantId manquant
            // atelierId manquant
        ]);

        $client->request(
            'POST',
            '/api/avis',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test : POST Avis avec note invalide retourne 422
     * 
    #[Test]
     */
    public function testPostAvisWithInvalidNoteReturns422(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'commentaire' => 'Super atelier',
            'note' => 10, // Note invalide (>5)
            'apprenantId' => 1,
            'atelierId' => 1
        ]);

        $client->request(
            'POST',
            '/api/avis',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test : Vérifier les messages d'erreur
     * 
    #[Test]
     */
    public function testErrorResponseHasDetailedMessages(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'titre' => '',
            'dureeHeure' => -5, // Valeur négative invalide
        ]);

        $client->request(
            'POST',
            '/api/ateliers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $this->assertResponseStatusCodeSame(422);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Vérifier que la réponse contient les détails des erreurs
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('fields', $responseData);
    }
}
