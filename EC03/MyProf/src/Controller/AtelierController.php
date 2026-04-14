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
    #[Route('/ateliers/inscription/{id}', name: 'app_atelier_inscription')]
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


    /**
     * 2) Mes ateliers (uniquement formateur)
     * -> Filtre BDD par formateur connecté
     */
    #[Route('/formateur/ateliers', name: 'app_ateliers_index', methods: ['GET'])]
    public function index(
        SessionInterface $session,
        AtelierRepository $atelierRepo,
        DocumentManager $dm

    ): Response {
        $user = $this->requireFormateur($session);

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


    #[Route('/atelier/avis/{id}', name: 'app_atelier_add_avis', methods: ['POST'])]
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