<?php

namespace App\Controller;

use App\Repository\UserApprenantRepository;
use App\Repository\UserFormateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request, 
        SessionInterface $session, 
        UserApprenantRepository $appRepo, 
        UserFormateurRepository $formRepo
    ): Response {
        // Si déjà connecté, on redirige vers le profil
        if ($session->has('user')) {
            return $this->redirectToRoute('app_apprenant_profil'); 
        }

        $error = null;
        $lastUsername = '';

        if ($request->isMethod('POST')) {
            $lastUsername = $request->request->get('email');
            $password = $request->request->get('password');

            // Recherche manuelle
            $user = $appRepo->findOneBy(['email' => $lastUsername]) 
                    ?? $formRepo->findOneBy(['email' => $lastUsername]);

            if ($user && password_verify($password, $user->getPassword())) {
                // Création de la session manuelle
                $session->set('user', [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => ($user instanceof \App\Entity\UserFormateur) ? 'formateur' : 'apprenant',
                ]);

                $this->addFlash('success', 'Connexion réussie !');
                return $this->redirectToRoute('app_apprenant_profil');
            }

            $error = "Email ou mot de passe incorrect.";
        }

        return $this->render('security/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        // On nettoie la session
        $session->remove('user');
        
        $this->addFlash('success', 'Vous avez été déconnecté.');
        return $this->redirectToRoute('app_login');
    }
}