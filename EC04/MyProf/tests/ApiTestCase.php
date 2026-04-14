<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Classe de base pour les tests API
 * 
 * Fournit des utilitaires pour les tests fonctionnels
 */
class ApiTestCase extends WebTestCase
{
    /**
     * Effectuer une requête POST JSON
     */
    protected function postJson(string $path, array $data = []): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            $path,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Effectuer une requête GET
     */
    protected function getJson(string $path, array $params = []): void
    {
        $client = static::createClient();
        $client->request('GET', $path, $params);
    }
}
