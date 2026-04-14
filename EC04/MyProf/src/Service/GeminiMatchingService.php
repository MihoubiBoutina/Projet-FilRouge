<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiMatchingService
{
    private string $apiKey;
    private string $apiUrl = 'https://router.huggingface.co/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        string $geminiApiKey // Injecté via services.yaml
    ) {
        $this->apiKey = $geminiApiKey;
    }

    public function getBestMatches(string $learnerProfile, array $workshops): string
    {
        // On prépare une liste textuelle des ateliers
        $workshopsList = "";
        foreach ($workshops as $w) {
            $workshopsList .= "ID: {$w['id']}, Titre: {$w['titre']}, Description: {$w['description']}\n";
        }

        // Le Prompt : on demande un format JSON pour faciliter la lecture en PHP
        $prompt = "Voici le profil d'un apprenant : '$learnerProfile'.
                Voici une liste d'ateliers :
                $workshopsList
                
                Analyse la compatibilité. Retourne UNIQUEMENT un objet JSON contenant les 3 meilleurs ateliers sous cette forme : 
                [{\"id\": 1, \"score\": 95, \"reason\": \"explication courte\"}]";

        $response = $this->httpClient->request('POST', $this->apiUrl . '?key=' . $this->apiKey, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                // On force l'IA à répondre en JSON pur
                'generationConfig' => [
                    'response_mime_type' => 'application/json'
                ]
            ]
        ]);

        return $response->getContent();
    }
}