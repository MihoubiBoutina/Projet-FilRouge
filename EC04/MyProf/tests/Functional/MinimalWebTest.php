<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MinimalWebTest extends WebTestCase
{
    public function testCanCreateClient(): void
    {
        $client = static::createClient();
        // Juste vérifier que le client peut être créé
        $this->assertNotNull($client);
    }

    public function testHttpRequest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // Juste vérifier qu'on peut faire une requête
        $response = $client->getResponse();
        $this->assertNotNull($response);
    }
}
