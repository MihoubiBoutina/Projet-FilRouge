<?php

namespace App\Controller;

use App\Document\Avis;
use App\Entity\UserApprenant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(DocumentManager $dm, EntityManagerInterface $em): Response
    {
        // 1. On récupère les avis dans MongoDB (triés du plus récent au plus ancien)
        $avisDocuments = $dm->getRepository(Avis::class)->findBy([], ['createdAt' => 'DESC']);

        // 2. On extrait tous les IDs d'apprenants présents dans les avis
        $apprenantIds = array_unique(array_map(fn($doc) => $doc->getApprenantId(), $avisDocuments));

        // 3. On récupère les infos MySQL en UNE SEULE requête
        $apprenants = $em->getRepository(UserApprenant::class)->findBy(['id' => $apprenantIds]);



        // On indexe les apprenants par ID pour les retrouver facilement
        $apprenantsIndexed = [];
        foreach ($apprenants as $apprenant) {
            $apprenantsIndexed[$apprenant->getId()] = $apprenant;
        }


        // 4. Construction du tableau final pour Twig
        $avisDynamiques = [];
        foreach ($avisDocuments as $doc) {
            $apprenant = $apprenantsIndexed[$doc->getApprenantId()] ?? null;

            if ($apprenant) {
                $avisDynamiques[] = [
                    'nom' => $apprenant->getNom(),
                    'message' => $doc->getCommentaire(),
                    //'photo' => $apprenant->getPhoto('image/eleve7.jpg'),
                    'note' => $doc->getNote(),
                    'date' => $doc->getCreatedAt()
                ];
            }
        }

        return $this->render('avis/index.html.twig', [
            'avis' => $avisDynamiques
        ]);
    }
}
