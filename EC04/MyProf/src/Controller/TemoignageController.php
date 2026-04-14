<?php

namespace App\Controller;

use App\Document\Avis;
use App\Entity\Atelier;
use App\Entity\UserApprenant;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TemoignageController extends AbstractController
{
    #[Route('/temoignage', name: 'app_temoignages', methods: ['GET'])]
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
}
