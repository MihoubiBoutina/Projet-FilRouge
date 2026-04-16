<?php

namespace App\Tests\Service;

use App\Service\GeminiMatchingService;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GeminiMatchingServiceIntegrationTest extends KernelTestCase
{
    #[Test]
    public function testGetBestMatchesReturnsJsonFromApi(): void
    {
        // 1. Préparer la réponse simulée au format Google Gemini
        $contentData = [
            [
                'id' => 1,
                'score' => 95,
                'reason' => 'Profil idéal pour le PHP'
            ]
        ];
        
        $googleResponse = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode($contentData)]
                        ]
                    ]
                ]
            ]
        ];
        
        $mockResponse = new MockResponse(json_encode($googleResponse), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json']
        ]);

        // 2. Créer le client HTTP simulant l'appel
        $mockHttpClient = new MockHttpClient($mockResponse);

        // 3. Instancier le service avec le mock
        $service = new GeminiMatchingService($mockHttpClient, 'fake_api_key');

        // 4. Exécuter la méthode
        $workshops = [
            ['id' => 1, 'titre' => 'PHP', 'description' => 'Apprendre le PHP']
        ];
        $result = $service->getBestMatches('Je veux apprendre le web', $workshops);

        // 5. Vérifier que le résultat extrait correspond bien au JSON de l'IA
        $this->assertJson($result);
        $this->assertSame(json_encode($contentData), $result);
    }
}
