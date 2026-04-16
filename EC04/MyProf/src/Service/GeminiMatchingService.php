<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiMatchingService
{
    private string $apiKey;
    // URL v1beta avec le modèle confirmé par le diagnostic : gemini-2.0-flash
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct(
        private HttpClientInterface $httpClient,
        string $geminiApiKey // Récupéré depuis services.yaml
    ) {
        $this->apiKey = $geminiApiKey;
    }

    public function getBestMatches(string $learnerProfile, array $workshops): string
    {
        // 1. Préparation de la liste des ateliers
        $workshopsList = "";
        foreach ($workshops as $w) {
            $workshopsList .= "ID: " . ($w['id'] ?? '?') . ", Titre: " . ($w['titre'] ?? 'Sans titre') . ", Description: " . ($w['description'] ?? 'Sans description') . "\n";
        }

        // 2. Le Prompt
        $prompt = "Voici le profil d'un apprenant : '$learnerProfile'.
                Voici une liste d'ateliers :
                $workshopsList
                
                Analyse la compatibilité. Retourne UNIQUEMENT un objet JSON contenant les 3 meilleurs ateliers sous cette forme : 
                [{\"id\": 1, \"score\": 95, \"reason\": \"explication courte\"}]";

        try {
            // 3. Appel à l'API Google Gemini native (v1beta + gemini-2.0-flash)
            $response = $this->httpClient->request('POST', $this->apiUrl . '?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ],
                'timeout' => 15
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Erreur API " . $response->getStatusCode() . " : " . $response->getContent(false));
            }

            // 4. Extraction du contenu
            $responseData = $response->toArray();
            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? "[]";

        } catch (\Exception $e) {
            // 5. MODE SIMULATION : Fallback propre en cas d'erreur
            $fallbackResults = [];
            foreach (array_slice($workshops, 0, 3) as $index => $workshop) {
                $fallbackResults[] = [
                    'id' => $workshop['id'] ?? $index,
                    'score' => 60 - ($index * 10),
                    'reason' => "[SIMULATION] " . ($workshop['titre'] ?? 'Atelier') . " correspond à vos intérêts."
                ];
            }
            return json_encode($fallbackResults);
        }
    }
}