<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class FormateursController extends AbstractController
{
/**
 * Route qui permet d’accéder à l’espace formateur.
 * Elle vérifie si l’utilisateur est connecté
 * et s’il possède bien le rôle "formateur".
 * Méthode HTTP autorisée : GET.
 */
    #[Route('/formateur', name: 'app_formateur_home', methods: ['GET'])]
public function formateurHome(SessionInterface $session): Response
{
    /**
     * Récupération de l’utilisateur stocké en session.
     */
    $user = $session->get('user');

    /**
     * Si aucun utilisateur n’est trouvé en session,
     * on affiche un message d’erreur
     * et on redirige vers la page d’inscription.
     */
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté.');
        return $this->redirectToRoute('app_inscription');
    }

    /**
     * Vérification du rôle de l’utilisateur.
     * Si l’utilisateur n’est pas formateur,
     * l’accès est refusé et il est redirigé vers le catalogue.
     */
    if (($user['role'] ?? null) !== 'formateur') {
        $this->addFlash('error', 'Accès réservé aux formateurs.');
        return $this->redirectToRoute('app_ateliers_catalogue');
    }
    
    /**
     * Si l’utilisateur est bien connecté et formateur,
     * il est redirigé vers la page de gestion des ateliers.
     */
    return $this->redirectToRoute('app_ateliers_index');
}



    /**
 * Route qui affiche la liste des formateurs.
 * Accessible via l’URL /formateurs.
 */
    #[Route('/formateurs', name: 'app_formateurs')]
    public function index(): Response
    {
        /**
     * Tableau contenant les informations des formateurs.
     * Chaque formateur possède :
     * - un nom
     * - une spécialité
     * - une photo
     */
        $formateurs = [
            ['nom' => 'Luca Martin',   'specialite' => 'Mathématiques', 'photo' => 'prof1.jpg'],
            ['nom' => 'Guillaume Durand', 'specialite' => 'Informatique',  'photo' => 'prof7.jpg'],
            ['nom' => 'lucie Dupont',   'specialite' => 'Physique',      'photo' => 'prof13.jpg'],
            ['nom' => 'Nora Benali',   'specialite' => 'SVT',           'photo' => 'prof5.jpg'],
            ['nom' => 'Amina Diallo',   'specialite' => 'Histoire',      'photo' => 'prof6.jpg'],
            ['nom' => 'Karim Haddad',  'specialite' => 'Mathématiques',      'photo' => 'prof.jpeg'],
            ['nom' => 'Emma Laurent',  'specialite' => 'Informatique',       'photo' => 'prof9.jpg'],
            ['nom' => 'Luc Miquel',   'specialite' => 'Anglais',  'photo' => 'prof11.jpg'],
        ];

        /**
     * Envoi du tableau des formateurs à la vue Twig
     * formateurs/index.html.twig pour affichage.
     */
        return $this->render('formateurs/index.html.twig', [
            'formateurs' => $formateurs,
        ]);

    }
}
