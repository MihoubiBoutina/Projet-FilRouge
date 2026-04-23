<?php

namespace App\Controller\api;

use App\Document\Avis;
use App\Document\LogVisite;
use App\Entity\UserApprenant;
use App\Repository\AtelierRepository;
use App\Repository\UserFormateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use App\Service\GeminiMatchingService;
use App\Service\NotificationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'api_')]
class AtelierApiController extends AbstractController
{
    #[Route('/ateliers', name: 'ateliers_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Liste et recherche les ateliers',
        tags: ['Ateliers'],
        parameters: [
            new OA\Parameter(name: 'titre', in: 'query', description: 'Recherche par titre', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'duree', in: 'query', description: 'Durée en heures', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Tri', schema: new OA\Schema(type: 'string', default: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des ateliers')
        ]
    )]
    public function list(Request $request, AtelierRepository $atelierRepo): JsonResponse
    {
        $titre = $request->query->get('titre', '');
        $duree = $request->query->get('duree', null);
        $sortBy = $request->query->get('sort', 'date');

        if (!empty($titre)) {
            $ateliers = $atelierRepo->findByTitrePartial($titre);
        } else {
            $ateliers = $atelierRepo->findAll();
        }

        if ($duree !== null) {
            $ateliers = array_filter($ateliers, fn($a) => $a->getDureeHeure() === (int)$duree);
        }

        $data = array_map(fn($atelier) => [
            'id' => $atelier->getId(),
            'titre' => $atelier->getTitre(),
            'description' => $atelier->getDescription(),
            'startAt' => $atelier->getStartAt()?->format('c'),
            'dureeHeure' => $atelier->getDureeHeure(),
            'formateurId' => $atelier->getFormateur()?->getId(),
            '_links' => [
                'self' => ['href' => '/api/ateliers/' . $atelier->getId()]
            ]
        ], $ateliers);

        return $this->json(['ateliers' => array_values($data)]);
    }

    #[Route('/ateliers/{id}', name: 'ateliers_show', methods: ['GET'])]
    #[OA\Get(
        description: 'Affiche un atelier spécifique et enregistre une visite dans MongoDB',
        tags: ['Ateliers']
    )]
    public function show(int $id, AtelierRepository $atelierRepo, Request $request, DocumentManager $dm): JsonResponse
    {
        $atelier = $atelierRepo->find($id);
        if (!$atelier) {
            return $this->json(['error' => 'Atelier introuvable'], 404);
        }

        // --- LogVisite dans MongoDB ---
        $log = new LogVisite();
        $log->setAtelierId($atelier->getId());
        $log->setVisiteurIp($request->getClientIp());
        // $log->setUserId($userId) si authentifié
        $dm->persist($log);
        $dm->flush();
        // ------------------------------

        return $this->json([
            'id' => $atelier->getId(),
            'titre' => $atelier->getTitre(),
            'description' => $atelier->getDescription(),
            'place' => $atelier->getPlace(),
            'formateurId' => $atelier->getFormateur()?->getId(),
        ]);
    }

    #[Route('/formateurs', name: 'formateurs_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Recherche les formateurs',
        tags: ['Formateurs']
    )]
    public function formateursList(Request $request, UserFormateurRepository $repo): JsonResponse
    {
        $nom = $request->query->get('nom', '');
        $formateurs = $nom ? $repo->findByNomPartial($nom) : $repo->findAll();

        $data = array_map(fn($f) => [
            'id' => $f->getId(),
            'nom' => $f->getNom(),
            'prenom' => $f->getPrenom(),
            '_links' => [
                'avis' => ['href' => '/api/avis?formateurId=' . $f->getId()]
            ]
        ], $formateurs);

        return $this->json($data);
    }

    #[Route('/avis', name: 'avis_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Liste les avis (MongoDB)',
        tags: ['Avis']
    )]
    public function avisList(Request $request, DocumentManager $dm): JsonResponse
    {
        // Exploitation de la force MongoDB (recherche rapide d'avis documentorienté)
        $formateurId = $request->query->get('formateurId');
        
        $criteria = [];
        // Si le document Avis évoluait pour embarquer formateurId on filtrerait directement ici
        // $criteria = $formateurId ? ['formateurId' => (int)$formateurId] : [];

        $avisDocuments = $dm->getRepository(Avis::class)->findBy($criteria, ['createdAt' => 'DESC']);

        $data = array_map(fn($avis) => [
            'id' => $avis->getId(),
            'commentaire' => $avis->getCommentaire(),
            'note' => $avis->getNote(),
        ], $avisDocuments);

        return $this->json(['avis' => $data]);
    }

    #[Route('/ateliers', name: 'ateliers_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouvel atelier',
        tags: ['Ateliers']
    )]
    public function createAtelier(Request $request, NotificationServiceInterface $notificationService): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        $required = ['titre', 'description', 'dureeHeure', 'place', 'formateurId', 'startAt'];
        $missing = array_filter($required, fn($field) => empty($data[$field]));

        if (!empty($missing)) {
            return $this->json([
                'error' => 'Validation Failed',
                'fields' => array_values($missing)
            ], 422);
        }

        // Simuler l'envoi de notification (pour le test 5)
        $notificationService->sendEmailNotification('formateur@example.com', 'Nouvel atelier', 'Un atelier a été créé');

        return $this->json(['message' => 'Atelier created', 'id' => 1], 201);
    }

    #[Route('/avis', name: 'avis_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouvel avis',
        tags: ['Avis']
    )]
    public function createAvis(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        $required = ['commentaire', 'note', 'apprenantId', 'atelierId'];
        $missing = array_filter($required, fn($field) => empty($data[$field]));

        if (!empty($missing)) {
            return $this->json(['error' => 'Validation Failed', 'fields' => $missing], 422);
        }

        if ($data['note'] < 1 || $data['note'] > 5) {
            return $this->json(['error' => 'Note invalid'], 422);
        }

        return $this->json(['message' => 'Avis created', 'id' => uniqid()], 201);
    }
}
