<?php

namespace App\Controller;

use App\Document\Avis;
use App\Entity\UserApprenant;
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

    #[Route('/apprenant/profil', name: 'app_apprenant_profil', methods: ['GET', 'POST'])]
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
}