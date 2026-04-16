<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AtelierControllerTest extends WebTestCase
{
    #[Test]
    public function testCataloguePageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ateliers');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.ateliers-page'); 
    }

    #[Test]
    public function testSearchFunctionality(): void
    {
        $client = static::createClient();
        
        // Test search with parameters
        $client->request('GET', '/ateliers/search', [
            'titre' => 'PHP',
            'sort' => 'titre'
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Rechercher');
    }

    #[Test]
    public function testRedirectToLoginWhenCreatingWithoutSession(): void
    {
        $client = static::createClient();
        $client->request('POST', '/ateliers');

        // Should redirect to login or show error if not logged in as formateur
        $this->assertResponseRedirects('/inscription');
    }
}
