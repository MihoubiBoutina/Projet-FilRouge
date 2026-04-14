<?php

namespace App\Controller;

use App\Document\Avis;
use App\Entity\Atelier;
use App\Entity\UserApprenant;
use App\Repository\AtelierRepository;
use App\Service\GeminiMatchingService;
use App\Form\UserApprenantProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Documents\CustomRepository\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ApprenantsController extends AbstractController
{
    /**
     * Cette route correspond à la page des apprenants.
     * Elle est accessible via l’URL /apprenants
     * et porte le nom app_apprenants.
     */
    #[Route('/apprenants', name: 'app_apprenants')]
    public function index(DocumentManager $dm, EntityManagerInterface $em): Response
    {
        // 1. On récupère les avis dynamiques depuis MongoDB
        $avisDocuments = $dm->getRepository(Avis::class)->findAll();

        $avisDynamiques = [];

        foreach ($avisDocuments as $doc) {
            // 2. On récupère les infos de l'apprenant dans MySQL
            $apprenant = $em->getRepository(UserApprenant::class)->find($doc->getApprenantId());

            if ($apprenant) {
                $avisDynamiques[] = [
                    'nom' => $apprenant->getNom(),
                    'message' => $doc->getCommentaire(), // On mappe 'commentaire' vers 'message'
                    'photo' => 'image/eleve7.jpg',
                    'note' => $doc->getNote(),
                    'date' => $doc->getCreatedAt()
                ];
            }
        }

        //pour chaque id apprenant recurper tout les avis sur mangodb

        // 3. Optionnel : Fusionner avec vos anciens avis "en dur" ou les supprimer
        // Ici, on ne garde que les avis de la base de données
        return $this->render('apprenants/index.html.twig', [
            'avis' => $avisDynamiques,
        ]);
    }

    #[Route('/apprenant/profil', name: 'app_apprenant_profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        $userSession = $session->get('user');
        if (!$userSession) {
            return $this->redirectToRoute('app_login');
        }

        /** @var UserApprenant|null $apprenant */
        $apprenant = $em->getRepository(UserApprenant::class)->find($userSession['id']);
        if (!$apprenant) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserApprenantProfilType::class, $apprenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apprenant->setProfilComplet(true);
            $em->flush();

            $this->addFlash('success', 'Profil complété avec succès !');
            return $this->redirectToRoute('app_ateliers_catalogue');
        }

        return $this->render('apprenants/profil.html.twig', [
            'form' => $form->createView(),
            'user' => $userSession,
        ]);
    }

    #[Route('/atelier/avis/{id}', name: 'app_atelier_add_avis', methods: ['POST'])]
    public function addAvis(
        int $id,
        Request $request,
        DocumentManager $dm,
        SessionInterface $session
    ): Response {
        $userSession = $session->get('user');
        if (!$userSession)
            return $this->redirectToRoute('app_login');

        $commentaire = $request->request->get('commentaire');
        $note = $request->request->get('note');

        $avis = new Avis();
        $avis->setAtelierId($id);
        $avis->setApprenantId($userSession['id']);
        $avis->setCommentaire($commentaire);
        $avis->setNote((int) $note);
        $avis->setCreatedAt(new \DateTime());

        $dm->persist($avis);
        $dm->flush();

        $this->addFlash('success', 'Merci pour votre avis !');
        return $this->redirectToRoute('app_ateliers_catalogue');
    }

    #[Route('/ateliers/{id}/temoignages', name: 'app_temoignages', methods: ['GET'])]
    public function temoignages(DocumentManager $dm, EntityManagerInterface $em, SessionInterface $session): Response
    {
        // 1. Récupérer tous les avis (MongoDB)
        $avisDocuments = $dm->getRepository(Avis::class)->findAll();

        // 2. Collecter les IDs uniques (Apprenants et Ateliers) pour MySQL
        $apprenantIds = array_filter(array_unique(array_map(fn($a) => $a->getApprenantId(), $avisDocuments)));
        $atelierIds = array_filter(array_unique(array_map(fn($a) => $a->getAtelierId(), $avisDocuments)));

        // 3. Récupérer les données MySQL en seulement 2 requêtes groupées (WHERE IN)
        $apprenants = $em->getRepository(UserApprenant::class)->findBy(['id' => $apprenantIds]);
        $ateliers = $em->getRepository(Atelier::class)->findBy(['id' => $atelierIds]);

        // 4. Indexer les résultats par ID pour un accès ultra-rapide ($dict[id])
        $apprenantsDict = [];
        foreach ($apprenants as $app) {
            $apprenantsDict[$app->getId()] = $app;
        }

        $ateliersDict = [];
        foreach ($ateliers as $at) {
            $ateliersDict[$at->getId()] = $at;
        }

        // 5. Assemblage final pour la vue Twig
        $viewData = [];
        foreach ($avisDocuments as $avis) {
            $apprenant = $apprenantsDict[$avis->getApprenantId()] ?? null;
            $atelier = $ateliersDict[$avis->getAtelierId()] ?? null;

            $viewData[] = [
                'id' => $avis->getId(),
                'note' => $avis->getNote(),
                'message' => $avis->getCommentaire(),
                'date' => $avis->getCreatedAt(),
                // Données de l'apprenant
                'apprenantNom' => $apprenant ? $apprenant->getNom() . ' ' . $apprenant->getPrenom() : 'Anonyme',
                // Données de l'atelier
                'atelierNom' => $atelier ? $atelier->getTitre() : 'Atelier supprimé',
                'atelierDesc' => $atelier ? $atelier->getDescription() : ''
            ];
        }

        return $this->render('atelier/index.html.twig', [
            'temoignages' => $viewData,
            'user' => $session->get('user')
        ]);
    }




    // src/Controller/AtelierController.php

    #[Route(path: '/ateliers', name: 'app_ateliers')]
    public function iaMatching(AtelierRepository $repo): Response
    {
            $apiToken = $_ENV['API_HUGGING'] ?? '';
            // Utilisation d'une URL de modèle valide
            $url = "https://router.huggingface.co/v1/chat/completions";

            // 1. RÉCUPÉRATION DES DONNÉES (Exemple : tous les ateliers)
        $ateliers = $repo->findAll();
        $listeAteliersText = "";
        
        foreach ($ateliers as $atelier) {
            $listeAteliersText .= "ID" . $atelier->getId() . " - Titre: " . $atelier->getTitre() . " - Description: " . $atelier->getDescription() . ")\n";
        }

        // 2. CONSTRUCTION DU PROMPT DYNAMIQUE
        $systemPrompt = "Tu es un conseiller pédagogique. Tu reçois une liste d'ateliers disponibles. Ton but est de faire une synthèse motivante de deux phrases maximum pour encourager l'apprenant à s'inscrire.";
        $userPrompt = "Voici les ateliers disponibles :\n" . $listeAteliersText . "\nDonne-moi un retour global et motivant.";

        $data = json_encode([
            "model" => "meta-llama/Llama-3.2-3B-Instruct",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userPrompt]
            ],
            "parameters" => [
                "max_new_tokens" => 150,
                "temperature" => 0.7
            ]
        ]);

        // 3. ENVOI DE LA REQUÊTE (CURL)
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiToken",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $messageIA = "L'inspiration est en chemin...";

        if ($httpCode === 200) {
            $response = json_decode($result, true);
            if (isset($response['choices'][0]['message']['content'])) {
                $messageIA = trim($response['choices'][0]['message']['content']);
            }
        } else {
            $errorData = json_decode($result, true);
            $messageIA = "IA indisponible (Code $httpCode) : " . ($errorData['error'] ?? 'Erreur de connexion');
        }

        return $this->render('matching/index.html.twig', [
            'message_ia' => $messageIA,
            'ateliers' => $ateliers // Vous pouvez aussi passer la liste à la vue
        ]);
    }
}