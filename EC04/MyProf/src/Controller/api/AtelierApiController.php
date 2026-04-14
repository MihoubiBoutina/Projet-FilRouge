<?php

namespace App\Controller\api;

use App\Document\Avis;
use App\Entity\UserApprenant;
use App\Repository\AtelierRepository;
use App\Repository\UserFormateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AtelierApiController extends AbstractController
{
    #[Route('/ateliers', name: 'api_ateliers_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Liste tous les ateliers disponibles',
        tags: ['Ateliers'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des ateliers',
                content: new OA\JsonContent(
                    properties: [
                        'ateliers' => new OA\Property(type: 'array', items: new OA\Items())
                    ]
                )
            )
        ]
    )]
    public function list(AtelierRepository $atelierRepo): JsonResponse
    {
        $ateliers = $atelierRepo->findAll();

        $data = array_map(fn($atelier) => [
            'id' => $atelier->getId(),
            'titre' => $atelier->getTitre(),
            'description' => $atelier->getDescription(),
            'startAt' => $atelier->getStartAt()?->format('c'),
            'dureeHeure' => $atelier->getDureeHeure(),
            'place' => $atelier->getPlace(),
            'formateurId' => $atelier->getFormateur()?->getId(),
        ], $ateliers);

        return $this->json(['ateliers' => $data]);
    }

    #[Route('/search', name: 'api_ateliers_search', methods: ['GET'])]
    #[OA\Get(
        description: 'Recherche les ateliers par titre, durée, nombre de places et tri',
        tags: ['Ateliers'],
        parameters: [
            new OA\Parameter(name: 'titre', in: 'query', description: 'Titre de l\'atelier à rechercher', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'duree', in: 'query', description: 'Durée en heures', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'places_min', in: 'query', description: 'Nombre minimum de places', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', description: 'Tri par (date, titre, place)', schema: new OA\Schema(type: 'string', default: 'date')),
            new OA\Parameter(name: 'order', in: 'query', description: 'Ordre (ASC, DESC)', schema: new OA\Schema(type: 'string', default: 'ASC')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ateliers trouvés')
        ]
    )]
    public function search(Request $request, AtelierRepository $atelierRepo): JsonResponse
    {
        $titre = $request->query->get('titre', '');
        $duree = $request->query->get('duree', null);
        $placesMin = $request->query->get('places_min', null);
        $sortBy = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'ASC');

        if (!in_array($sortBy, ['date', 'titre', 'place'], true)) {
            $sortBy = 'date';
        }
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'ASC';
        }

        if (!empty($titre)) {
            $ateliers = $atelierRepo->findByTitrePartial($titre);
        } else {
            $ateliers = $atelierRepo->findAll();
        }

        if ($duree !== null) {
            $duree = (int) $duree;
            $ateliers = array_filter($ateliers, fn($a) => $a->getDureeHeure() === $duree);
        }

        if ($placesMin !== null) {
            $placesMin = (int) $placesMin;
            $ateliers = array_filter($ateliers, fn($a) => $a->getPlace() >= $placesMin);
        }

        usort($ateliers, function ($a, $b) use ($sortBy, $order) {
            $comparison = 0;
            switch ($sortBy) {
                case 'titre':
                    $comparison = strcmp($a->getTitre(), $b->getTitre());
                    break;
                case 'place':
                    $comparison = $a->getPlace() <=> $b->getPlace();
                    break;
                case 'date':
                default:
                    $comparison = $a->getStartAt() <=> $b->getStartAt();
                    break;
            }

            return $order === 'DESC' ? -$comparison : $comparison;
        });

        $data = array_map(fn($atelier) => [
            'id' => $atelier->getId(),
            'titre' => $atelier->getTitre(),
            'description' => $atelier->getDescription(),
            'startAt' => $atelier->getStartAt()?->format('c'),
            'dureeHeure' => $atelier->getDureeHeure(),
            'place' => $atelier->getPlace(),
            'formateurId' => $atelier->getFormateur()?->getId(),
        ], $ateliers);

        return $this->json([
            'query' => [
                'titre' => $titre,
                'duree' => $duree,
                'places_min' => $placesMin,
                'sort' => $sortBy,
                'order' => $order,
            ],
            'ateliers' => $data,
        ]);
    }


    #[Route('/formateurs/search', name: 'api_formateurs_search', methods: ['GET'])]
    #[OA\Get(
        description: 'Recherche les formateurs par nom',
        tags: ['Formateurs'],
        parameters: [
            new OA\Parameter(name: 'nom', in: 'query', description: 'Nom du formateur', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Formateurs trouvés')
        ]
    )]
    public function forma(Request $request, UserFormateurRepository $repo): JsonResponse
    {
        $nom = $request->query->get('nom', '');

        if ($nom) {
            $formateurs = $repo->findByNomPartial($nom);
        } else {
            $formateurs = $repo->findAll();
        }

        $data = array_map(fn($f) => [
            'id' => $f->getId(),
            'nom' => $f->getNom(),
            'prenom' => $f->getPrenom(),
            'email' => $f->getEmail(),
        ], $formateurs);

        return $this->json($data);
    }

    #[Route('/avis', name: 'api_avis_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Liste tous les avis avec filtrage optionnel par note',
        tags: ['Avis'],
        parameters: [
            new OA\Parameter(name: 'note', in: 'query', description: 'Filtrer par note (1-5)', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des avis')
        ]
    )]
    public function avis(Request $request, DocumentManager $dm, EntityManagerInterface $em, AtelierRepository $atelierRepo): JsonResponse
    {
        $note = $request->query->get('note', null);

        // Récupère tous les avis triés du plus récent au plus ancien
        $avisDocuments = $dm->getRepository(Avis::class)->findBy([], ['createdAt' => 'DESC']);

        // Filtre par note si le paramètre est fourni
        if ($note !== null) {
            $note = (int) $note;
            $avisDocuments = array_filter($avisDocuments, fn($a) => $a->getNote() === $note);
        }

        // Extrait les IDs des apprenants et ateliers
        $apprenantIds = array_unique(array_map(fn($doc) => $doc->getApprenantId(), $avisDocuments));
        $atelierIds = array_unique(array_map(fn($doc) => $doc->getAtelierId(), $avisDocuments));

        // Récupère les infos MySQL en une seule requête
        $apprenants = $em->getRepository(UserApprenant::class)->findBy(['id' => $apprenantIds]);
        $ateliers = $atelierRepo->findBy(['id' => $atelierIds]);

        // Index les données par ID pour retrouver facilement
        $apprenantsIndexed = [];
        foreach ($apprenants as $apprenant) {
            $apprenantsIndexed[$apprenant->getId()] = $apprenant;
        }

        $ateliersIndexed = [];
        foreach ($ateliers as $atelier) {
            $ateliersIndexed[$atelier->getId()] = $atelier;
        }

        // Construit le tableau des avis
        $data = array_map(fn($avis) => [
            'id' => $avis->getId(),
            'commentaire' => $avis->getCommentaire(),
            'note' => $avis->getNote(),
            'createdAt' => $avis->getCreatedAt()?->format('c'),
            'apprenant' => isset($apprenantsIndexed[$avis->getApprenantId()]) ? [
                'id' => $apprenantsIndexed[$avis->getApprenantId()]->getId(),
                'nom' => $apprenantsIndexed[$avis->getApprenantId()]->getNom(),
                'prenom' => $apprenantsIndexed[$avis->getApprenantId()]->getPrenom(),
            ] : null,
            'atelier' => isset($ateliersIndexed[$avis->getAtelierId()]) ? [
                'id' => $ateliersIndexed[$avis->getAtelierId()]->getId(),
                'titre' => $ateliersIndexed[$avis->getAtelierId()]->getTitre(),
            ] : null,
        ], $avisDocuments);

        return $this->json(['avis' => $data]);
    }

    #[Route('/ateliers', name: 'api_ateliers_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouvel atelier',
        tags: ['Ateliers'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titre', 'description', 'dureeHeure', 'place', 'formateurId', 'startAt'],
                properties: [
                    'titre' => new OA\Property(type: 'string'),
                    'description' => new OA\Property(type: 'string'),
                    'dureeHeure' => new OA\Property(type: 'integer'),
                    'place' => new OA\Property(type: 'integer'),
                    'formateurId' => new OA\Property(type: 'integer'),
                    'startAt' => new OA\Property(type: 'string', format: 'date-time'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Atelier créé'),
            new OA\Response(response: 400, description: 'Payload invalide'),
            new OA\Response(response: 422, description: 'Données invalides'),
        ]
    )]
    public function createAtelier(
        Request $request,
        AtelierRepository $atelierRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérifier que le payload n'est pas vide
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Vérifier les champs obligatoires
        $required = ['titre', 'description', 'dureeHeure', 'place', 'formateurId', 'startAt'];
        $missing = array_filter($required, fn($field) => empty($data[$field]));

        if (!empty($missing)) {
            return $this->json(['error' => 'Missing required fields', 'fields' => $missing], 422);
        }

        // Pour la démo, on retourne juste l'atelier créé
        return $this->json([
            'id' => 1,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'dureeHeure' => $data['dureeHeure'],
            'place' => $data['place'],
            'formateurId' => $data['formateurId'],
            'startAt' => $data['startAt'],
        ], 201);
    }

    #[Route('/avis', name: 'api_avis_create', methods: ['POST'])]
    #[OA\Post(
        description: 'Crée un nouvel avis',
        tags: ['Avis'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['commentaire', 'note', 'apprenantId', 'atelierId'],
                properties: [
                    'commentaire' => new OA\Property(type: 'string'),
                    'note' => new OA\Property(type: 'integer', minimum: 1, maximum: 5),
                    'apprenantId' => new OA\Property(type: 'integer'),
                    'atelierId' => new OA\Property(type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Avis créé'),
            new OA\Response(response: 400, description: 'Payload invalide'),
            new OA\Response(response: 422, description: 'Données invalides'),
        ]
    )]
    public function createAvis(Request $request, DocumentManager $dm): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier que le payload n'est pas vide
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // Vérifier les champs obligatoires
        $required = ['commentaire', 'note', 'apprenantId', 'atelierId'];
        $missing = array_filter($required, fn($field) => empty($data[$field]));

        if (!empty($missing)) {
            return $this->json(['error' => 'Missing required fields', 'fields' => $missing], 422);
        }

        // Vérifier que la note est entre 1 et 5
        if ($data['note'] < 1 || $data['note'] > 5) {
            return $this->json(['error' => 'Note must be between 1 and 5'], 422);
        }

        // Pour la démo, on retourne juste l'avis créé
        return $this->json([
            'id' => uniqid(),
            'commentaire' => $data['commentaire'],
            'note' => $data['note'],
            'apprenantId' => $data['apprenantId'],
            'atelierId' => $data['atelierId'],
            'createdAt' => (new \DateTime())->format('c'),
        ], 201);
    }

}
