<?php

namespace App\Controller;

use App\Entity\Atelier;
use App\Repository\AtelierRepository;
use App\Repository\UserFormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * Création d'un nouvel atelier.
     */
    #[Route('/formateur/ateliers/nouveau', name: 'app_ateliers_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ValidatorInterface $validator,
        SessionInterface $session,
        UserFormateurRepository $formateurRepo,
        EntityManagerInterface $em
    ): Response {
        $userData = $this->getAuthFormateur($session);

        if (!$userData) {
            $this->addFlash('error', 'Vous devez être connecté en tant que formateur.');
            return $this->redirectToRoute('app_inscription');
        }

        $errors = [];
        $data = [
            'titre' => '', 'description' => '', 'date' => '',
            'heure' => '', 'duree' => 60, 'places' => 10,
        ];

        if ($request->isMethod('POST')) {
            // Récupération et nettoyage des données
            $data = [
                'titre'       => trim((string) $request->request->get('titre')),
                'description' => trim((string) $request->request->get('description')),
                'date'        => trim((string) $request->request->get('date')),
                'heure'       => trim((string) $request->request->get('heure')),
                'duree'       => (int) $request->request->get('duree', 60),
                'places'      => (int) $request->request->get('places', 10),
            ];

            // Définition des contraintes de validation
            $constraints = new Assert\Collection([
                'fields' => [
                    'titre'       => [new Assert\NotBlank(), new Assert\Length(['min' => 3, 'max' => 80])],
                    'description' => [new Assert\NotBlank(), new Assert\Length(['min' => 10, 'max' => 500])],
                    'date'        => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^\d{4}-\d{2}-\d{2}$/'])],
                    'heure'       => [new Assert\NotBlank(), new Assert\Regex(['pattern' => '/^\d{2}:\d{2}$/'])],
                    'duree'       => [new Assert\Positive(), new Assert\LessThanOrEqual(480)],
                    'places'      => [new Assert\Positive(), new Assert\LessThanOrEqual(100)],
                ],
                'allowExtraFields' => true,
            ]);

            $violations = $validator->validate($data, $constraints);

            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[str_replace(['[', ']'], '', $v->getPropertyPath())] = $v->getMessage();
                }
            } else {
                $formateur = $formateurRepo->find($userData['id']);
                $startAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['heure']);

                if (!$formateur) {
                    $this->addFlash('error', 'Compte formateur introuvable.');
                    return $this->redirectToRoute('app_inscription');
                }

                if (!$startAt) {
                    $errors['date'] = 'Le format de la date ou de l’heure est invalide.';
                } else {
                    $atelier = new Atelier();
                    $atelier->setTitre($data['titre'])
                            ->setDescription($data['description'])
                            ->setStartAt($startAt)
                            ->setDureeHeure($data['duree'])
                            ->setPlace($data['places'])
                            ->setFormateur($formateur);

                    $em->persist($atelier);
                    $em->flush();

                    $this->addFlash('success', 'L’atelier a été créé avec succès.');
                    return $this->redirectToRoute('app_formateur_index'); // Ou vers la liste des ateliers
                }
            }
        }

        return $this->render('atelier/new.html.twig', [
            'data'   => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * Suppression d'un atelier.
     */
    #[Route('/formateur/ateliers/{id}/supprimer', name: 'app_ateliers_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        SessionInterface $session,
        AtelierRepository $atelierRepo,
        EntityManagerInterface $em
    ): Response {
        $userData = $this->getAuthFormateur($session);

        if (!$userData) {
            return $this->redirectToRoute('app_inscription');
        }

        if (!$this->isCsrfTokenValid('delete_atelier_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée (Token CSRF invalide).');
            return $this->redirectToRoute('app_formateur_index');
        }

        $atelier = $atelierRepo->find($id);
        if (!$atelier || $atelier->getFormateur()->getId() !== (int) $userData['id']) {
            $this->addFlash('error', 'Atelier introuvable ou vous n’êtes pas le propriétaire.');
            return $this->redirectToRoute('app_formateur_index');
        }

        $em->remove($atelier);
        $em->flush();

        $this->addFlash('success', 'Atelier supprimé.');
        return $this->redirectToRoute('app_formateur_index');
    }
}