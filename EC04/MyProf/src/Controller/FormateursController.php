<?php

namespace App\Controller;

use App\Repository\UserFormateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AtelierRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\Avis;

final class FormateursController extends AbstractController
{
    /**
     * Méthode privée pour vérifier si l'utilisateur est un formateur connecté.
     * Retourne les données user ou null.
     */
    private function getAuthFormateur(SessionInterface $session): ?array
    {
        $user = $session->get('user');
        if (!$user || ($user['role'] ?? null) !== 'formateur') {
            return null;
        }
        return $user;
    }

    /**
     * Point d'entrée pour l'espace formateur.
     */
    #[Route('/formateur', name: 'app_formateur_home', methods: ['GET'])]
    public function home(SessionInterface $session): Response
    {
        if (!$this->getAuthFormateur($session)) {
            $this->addFlash('error', 'Accès réservé aux formateurs.');
            return $this->redirectToRoute('app_inscription');
        }

        // Redirige vers la liste des formateurs (ou une autre page de ton choix)
        return $this->redirectToRoute('app_formateur_index');
    }

    /**
     * Affiche la liste des formateurs.
     */
    #[Route('/formateurs/liste', name: 'app_formateur_index', methods: ['GET'])]
    public function index(UserFormateurRepository $repo): Response
    {
        return $this->render('formateurs/index.html.twig', [
            'formateurs' => $repo->findAll(),
        ]);
    }

    /**
     * 2) Mes ateliers (uniquement formateur)
     * -> Filtre BDD par formateur connecté
     */
    #[Route('/formateur/ateliers', name: 'app_ateliers_index', methods: ['GET'])]
    public function ateliers(
        SessionInterface $session,
        AtelierRepository $atelierRepo,
        DocumentManager $dm

    ): Response {
        $user = $this->getAuthFormateur($session);
        if (!$user) {
            $this->addFlash('error', 'Accès réservé aux formateurs.');
            return $this->redirectToRoute('app_inscription');
        }

        $mesAteliers = $atelierRepo->findBy(
            ['formateur' => $user['id']],
            ['id' => 'DESC']
        );

        // On récupère les avis pour que la variable existe
        $tousLesAvis = $dm->getRepository(Avis::class)->findAll();

        return $this->render('atelier/index.html.twig', [
            'ateliers' => $mesAteliers,
            'avis' => $tousLesAvis,
        ]);
    }


}