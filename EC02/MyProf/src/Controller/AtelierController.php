<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AtelierController extends AbstractController
{
    private function requireConnected(SessionInterface $session): array
    {
        $user = $session->get('user');

        if (!is_array($user)) {
        $this->addFlash('error', 'Vous devez être connecté.');
        return [];
    }

        return $user;
    }

    private function requireFormateur(SessionInterface $session): array
    {
        $user = $this->requireConnected($session);

        if (!$user) {
            throw $this->createAccessDeniedException('Non connecté');
        }

        if (($user['role'] ?? null) !== 'formateur') {
            $this->addFlash('error', 'Accès réservé aux formateurs.');
            throw $this->createAccessDeniedException('Pas formateur');
        }

        return $user;
    }

    private function getAteliers(SessionInterface $session): array
    {
        return $session->get('ateliers', []);
    }

    private function saveAteliers(SessionInterface $session, array $ateliers): void
    {
        $session->set('ateliers', $ateliers);
    }

    /**
     * 1) Catalogue des ateliers (apprenant ET formateur)
     */
    #[Route('/ateliers', name: 'app_ateliers_catalogue', methods: ['GET'])]
    public function catalogue(SessionInterface $session): Response
    {
        $user = $this->requireConnected($session);
        if (!$user) {
            return $this->redirectToRoute('app_inscription');
        }

        $ateliers = $this->getAteliers($session);

        return $this->render('atelier/catalogue.html.twig', [
            'ateliers' => $ateliers,
        ]);
    }

    /**
     * 2) Mes ateliers (uniquement formateur)
     */
    #[Route('/formateur/ateliers', name: 'app_ateliers_index')]
    public function index(SessionInterface $session): Response
    {
        $user = $this->requireFormateur($session);

        if (!is_array($user) || ($user['role'] ?? null) !== 'formateur') {
        $this->addFlash('error', 'Accès réservé aux formateurs.');
        return $this->redirectToRoute('app_ateliers_catalogue');
        }

        $ateliers = $this->getAteliers($session);

        $mesAteliers = array_filter($ateliers, fn($a) =>
            ($a['formateur_email'] ?? '') === $user['email']
        );

        return $this->render('atelier/index.html.twig', [
            'ateliers' => $mesAteliers,
        ]);
    }

    /**
     * 3) Créer un atelier (uniquement formateur)
     */
    #[Route('/formateur/ateliers/nouveau', name: 'app_ateliers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ValidatorInterface $validator, SessionInterface $session): Response
    {
        $user = $this->requireFormateur($session);

        $errors = [];
        $data = [
            'titre' => '',
            'description' => '',
            'date' => '',
            'heure' => '',
            'duree' => 60,
            'places' => 10,
        ];

        if ($request->isMethod('POST')) {
            $data['titre'] = trim((string) $request->request->get('titre'));
            $data['description'] = trim((string) $request->request->get('description'));
            $data['date'] = trim((string) $request->request->get('date'));
            $data['heure'] = trim((string) $request->request->get('heure'));
            $data['duree'] = (int) $request->request->get('duree', 60);
            $data['places'] = (int) $request->request->get('places', 10);

            $constraints = new Assert\Collection([
                'fields' => [
                    'titre' => [
                        new Assert\NotBlank(['message' => 'Le titre est obligatoire']),
                        new Assert\Length(null, 3, 80),
                    ],
                    'description' => [
                        new Assert\NotBlank(['message' => 'La description est obligatoire']),
                        new Assert\Length(null, 10, 500),
                    ],
                    'date' => [
                        new Assert\NotBlank(['message' => 'La date est obligatoire']),
                        new Assert\Regex([
                            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
                            'message' => 'Format attendu : AAAA-MM-JJ',
                        ]),
                    ],
                    'heure' => [
                        new Assert\NotBlank(['message' => 'L’heure est obligatoire']),
                        new Assert\Regex([
                            'pattern' => '/^\d{2}:\d{2}$/',
                            'message' => 'Format attendu : HH:MM',
                        ]),
                    ],
                    'duree' => [
                        new Assert\Positive(['message' => 'La durée doit être > 0']),
                        new Assert\LessThanOrEqual(['value' => 480, 'message' => 'Durée max : 480 min']),
                    ],
                    'places' => [
                        new Assert\Positive(['message' => 'Le nombre de places doit être > 0']),
                        new Assert\LessThanOrEqual(['value' => 100, 'message' => 'Places max : 100']),
                    ],
                ],
                'allowExtraFields' => true,
                'allowMissingFields' => false,
            ]);

            $violations = $validator->validate($data, $constraints);

            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }
            } else {
                $ateliers = $this->getAteliers($session);

                $id = bin2hex(random_bytes(8));

                $ateliers[] = [
                    'id' => $id,
                    'titre' => $data['titre'],
                    'description' => $data['description'],
                    'date' => $data['date'],
                    'heure' => $data['heure'],
                    'duree' => $data['duree'],
                    'places' => $data['places'],
                    'formateur_email' => $user['email'],
                    'formateur_nom' => $user['prenom'].' '.$user['nom'],
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ];

                $this->saveAteliers($session, $ateliers);

                $this->addFlash('success', 'Atelier créé.');
                return $this->redirectToRoute('app_ateliers_index');
            }
        }

        return $this->render('atelier/new.html.twig', [
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * 4) Supprimer un atelier (uniquement formateur, propriétaire)
     */
    #[Route('/formateur/ateliers/{id}/supprimer', name: 'app_ateliers_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, SessionInterface $session): Response
    {
        $user = $this->requireFormateur($session);

        if (!$this->isCsrfTokenValid('delete_atelier_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_ateliers_index');
        }

        $ateliers = $this->getAteliers($session);

        $before = count($ateliers);
        $ateliers = array_values(array_filter($ateliers, function ($a) use ($id, $user) {
            // On garde tout sauf l'atelier ciblé si c'est bien le propriétaire
            if (($a['id'] ?? '') !== $id) return true;
            return ($a['formateur_email'] ?? '') !== ($user['email'] ?? '');
        }));

        if (count($ateliers) === $before) {
            $this->addFlash('error', 'Atelier introuvable ou non autorisé.');
        } else {
            $this->saveAteliers($session, $ateliers);
            $this->addFlash('success', 'Atelier supprimé.');
        }

        return $this->redirectToRoute('app_ateliers_index');
    }
}
