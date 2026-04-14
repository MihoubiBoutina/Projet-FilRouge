<?php

namespace App\Controller;

use App\Document\Avis;
use App\Entity\Atelier;
use App\Entity\InscriptionAtelier;
use App\Entity\UserApprenant;
use App\Repository\AtelierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Repository\UserFormateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AtelierController extends AbstractController
{
    private function requireConnected(SessionInterface $session): array
    {
        $user = $session->get('user');

        if (!is_array($user) || !isset($user['id'])) {
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

    private function getAuthFormateur(SessionInterface $session): ?array
    {
        $user = $session->get('user');

        if (!$user || ($user['role'] ?? null) !== 'formateur') {
            return null;
        }
        return $user;
    }

    /**
     * 1) Catalogue des ateliers (apprenant ET formateur)
     * -> Lecture BDD
     */
    #[Route('/ateliers', name: 'app_ateliers_catalogue', methods: ['GET'])]
    public function catalogue(
        AtelierRepository $atelierRepo,
        SessionInterface $session,
    ): Response {
        // On récupère juste la liste de tous les ateliers pour les afficher
        $ateliers = $atelierRepo->findAll();


        return $this->render('atelier/catalogue.html.twig', [
            'ateliers' => $ateliers,
            'user' => $session->get('user')
        ]);
    }


    /**
     * Gère l'inscription à UN atelier précis
     */
    #[Route('/ateliers/{id}/inscription', name: 'app_atelier_inscription', methods: ['POST'])]
    public function inscription(
        int $id,
        SessionInterface $session,
        Atelier $atelier,
        EntityManagerInterface $em
    ): Response {
        $userSession = $session->get('user');

        if (!$userSession) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        // 2. Récupérer l'objet Apprenant complet depuis MySQL
        $apprenant = $em->getRepository(UserApprenant::class)->find($userSession['id']);

        // 3. Créer l'inscription (Table de liaison)
        $inscription = new InscriptionAtelier();
        $inscription->setAtelier($atelier);
        $inscription->setApprenant($apprenant);// Date de l'inscription


        $em->persist($inscription);
        $em->flush();

        $this->addFlash('success', 'Inscription réussie à : ' . $atelier->gettitre());
        return $this->redirectToRoute('app_ateliers_catalogue');
    }

    #[Route('/atelier/{id}/avis', name: 'app_atelier_add_avis', methods: ['POST'])]
    public function addAvis(
        int $id,
        Request $request,
        DocumentManager $dm,
        SessionInterface $session
    ): Response {
        $userSession = $session->get('user');
        if (!$userSession)
            return $this->redirectToRoute('app_login');

        $commentaire = $request->request->get('commentaire');
        $note = $request->request->get('note');

        $avis = new Avis();
        $avis->setAtelierId($id);
        $avis->setApprenantId($userSession['id']);
        $avis->setCommentaire($commentaire);
        $avis->setNote((int) $note);
        $avis->setCreatedAt(new \DateTime());

        $dm->persist($avis);
        $dm->flush();

        $this->addFlash('success', 'Merci pour votre avis !');
        return $this->redirectToRoute('app_ateliers_catalogue');
    }



    /**
     * Recherche et filtrage des ateliers avec query parameters
     * Exemples d'utilisation:
     * GET /ateliers/search
     * GET /ateliers/search?titre=PHP
     * GET /ateliers/search?titre=PHP&duree=60
     * GET /ateliers/search?titre=PHP&duree=60&places_min=5
     */
    #[Route('/ateliers/search', name: 'app_ateliers_search', methods: ['GET'])]
    public function search(
        Request $request,
        AtelierRepository $atelierRepo,
        SessionInterface $session
    ): Response {
        // Récupération des query parameters
        $titre = $request->query->get('titre', '');
        $duree = $request->query->get('duree', null);
        $placesMin = $request->query->get('places_min', null);
        $sortBy = $request->query->get('sort', 'date'); // 'date' ou 'titre'
        $order = $request->query->get('order', 'ASC'); // 'ASC' ou 'DESC'

        // Valider les paramètres
        if (!in_array($sortBy, ['date', 'titre', 'place'])) {
            $sortBy = 'date';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        // Construire la requête avec filtres
        $criteria = [];

        if (!empty($titre)) {
            // Note: Vous devrez adapter si vous utilisez DQL ou QueryBuilder
            $ateliers = $atelierRepo->findByTitrePartial($titre);
        } else {
            $ateliers = $atelierRepo->findAll();
        }

        // Filtrer par durée si fourni
        if ($duree !== null) {
            $duree = (int) $duree;
            $ateliers = array_filter($ateliers, fn($a) => $a->getDureeHeure() === $duree);
        }

        // Filtrer par places minimales si fourni
        if ($placesMin !== null) {
            $placesMin = (int) $placesMin;
            $ateliers = array_filter($ateliers, fn($a) => $a->getPlace() >= $placesMin);
        }

        // Tri
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
            }

            return $order === 'DESC' ? -$comparison : $comparison;
        });

        return $this->render('atelier/search.html.twig', [
            'ateliers' => $ateliers,
            'params' => [
                'titre' => $titre,
                'duree' => $duree,
                'places_min' => $placesMin,
                'sort' => $sortBy,
                'order' => $order,
            ],
            'user' => $session->get('user')
        ]);
    }



    /**
     * Création d'un nouvel atelier.
     */
    #[Route('/ateliers', name: 'app_ateliers_new', methods: ['POST'])]
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
            'titre' => '',
            'description' => '',
            'date' => '',
            'heure' => '',
            'duree' => 60,
            'places' => 10,
        ];

        if ($request->isMethod('POST')) {
            // Récupération et nettoyage des données
            $data = [
                'titre' => trim((string) $request->request->get('titre')),
                'description' => trim((string) $request->request->get('description')),
                'date' => trim((string) $request->request->get('date')),
                'heure' => trim((string) $request->request->get('heure')),
                'duree' => (int) $request->request->get('duree', 60),
                'places' => (int) $request->request->get('places', 10),
            ];

            // Définition des contraintes de validation
            $constraints = new Assert\Collection([
                'fields' => [
                    'titre' => new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Length(min: 3, max: 80)
                    ]),
                    'description' => new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Length(min: 10, max: 500)
                    ]),
                    'date' => new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/')
                    ]),
                    'heure' => new Assert\All([
                        new Assert\NotBlank(),
                        new Assert\Regex(pattern: '/^\d{2}:\d{2}$/')
                    ]),
                    'duree' => new Assert\All([
                        new Assert\Positive(),
                        new Assert\LessThanOrEqual(value: 480)
                    ]),
                    'places' => new Assert\All([
                        new Assert\Positive(),
                        new Assert\LessThanOrEqual(value: 100)
                    ]),
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
            'data' => $data,
            'errors' => $errors,
        ]);
    }


    /**
     * Suppression d'un atelier.
     */
    #[Route('/ateliers/{id}', name: 'app_ateliers_delete', methods: ['POST'])]
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