<?php

namespace App\Controller\api;

use App\Document\Avis;
use App\Document\LogVisite;
use App\Repository\AtelierRepository;
use App\Repository\UserApprenantRepository;
use App\Repository\UserFormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenApi\Attributes as OA;
use App\Service\NotificationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
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
            $ateliers = array_filter($ateliers, fn($a) => $a->getDureeHeure() === (int) $duree);
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
    public function createAtelier(
        Request $request,
        NotificationServiceInterface $notificationService,
        EntityManagerInterface $entityManager,
        UserFormateurRepository $formateurRepository
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        $required = ['titre', 'description', 'dureeHeure', 'place', 'formateurId', 'startAt'];
        $missing = array_values(array_filter(
            $required,
            fn(string $field): bool => !array_key_exists($field, $data)
                || $data[$field] === null
                || (is_string($data[$field]) && trim($data[$field]) === '')
        ));

        if (!empty($missing)) {
            return $this->json([
                'error' => 'Validation Failed',
                'fields' => array_values($missing)
            ], 422);
        }

        if (
            !is_int($data['dureeHeure']) || $data['dureeHeure'] < 1
            || !is_int($data['place']) || $data['place'] < 1
            || !is_int($data['formateurId'])
        ) {
            return $this->json(['error' => 'Validation Failed', 'fields' => ['dureeHeure', 'place', 'formateurId']], 422);
        }

        if (!is_string($data['startAt'])) {
            return $this->json(['error' => 'Validation Failed', 'fields' => ['startAt']], 422);
        }

        try {
            $startAt = new \DateTimeImmutable($data['startAt']);
        } catch (\Exception) {
            return $this->json(['error' => 'Validation Failed', 'fields' => ['startAt']], 422);
        }

        $formateur = $formateurRepository->find($data['formateurId']);
        if (!$formateur) {
            return $this->json(['error' => 'Formateur introuvable'], 404);
        }

        $atelier = (new \App\Entity\Atelier())
            ->setTitre(trim($data['titre']))
            ->setDescription(trim($data['description']))
            ->setDureeHeure($data['dureeHeure'])
            ->setPlace($data['place'])
            ->setStartAt($startAt)
            ->setFormateur($formateur);

        $entityManager->persist($atelier);
        $entityManager->flush();

        $notificationService->sendEmailNotification(
            (string) $formateur->getEmail(),
            'Nouvel atelier',
            'Un atelier a été créé'
        );

        return $this->json(['message' => 'Atelier created', 'id' => $atelier->getId()], 201);
    }

    #[Route('/avis', name: 'avis_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouvel avis',
        tags: ['Avis']
    )]
    public function createAvis(
        Request $request,
        DocumentManager $documentManager,
        AtelierRepository $atelierRepository,
        UserApprenantRepository $apprenantRepository
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON payload'], 400);
        }

        $required = ['commentaire', 'note', 'apprenantId', 'atelierId'];
        $missing = array_values(array_filter(
            $required,
            fn(string $field): bool => !array_key_exists($field, $data)
                || $data[$field] === null
                || (is_string($data[$field]) && trim($data[$field]) === '')
        ));

        if (!empty($missing)) {
            return $this->json(['error' => 'Validation Failed', 'fields' => $missing], 422);
        }

        if (
            !is_int($data['note']) || $data['note'] < 1 || $data['note'] > 5
            || !is_int($data['apprenantId']) || !is_int($data['atelierId'])
        ) {
            return $this->json(['error' => 'Note invalid'], 422);
        }

        if (!$apprenantRepository->find($data['apprenantId'])) {
            return $this->json(['error' => 'Apprenant introuvable'], 404);
        }

        if (!$atelierRepository->find($data['atelierId'])) {
            return $this->json(['error' => 'Atelier introuvable'], 404);
        }

        $avis = (new Avis())
            ->setCommentaire(trim($data['commentaire']))
            ->setNote($data['note'])
            ->setApprenantId($data['apprenantId'])
            ->setAtelierId($data['atelierId']);

        $documentManager->persist($avis);
        $documentManager->flush();

        return $this->json(['message' => 'Avis created', 'id' => $avis->getId()], 201);
    }
}
