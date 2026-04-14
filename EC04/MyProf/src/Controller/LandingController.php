<?php
namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;


class LandingController extends AbstractController
{
    /**
 * Route correspondant à la page d’accueil du site (landing page).
 * Elle est accessible via l’URL "/" et permet de présenter
 * les formateurs ainsi que les avis des apprenants.
 */
    #[Route('/', 'app_landing')]
    public function index(): Response
    {
        // null si pas connecté
        /**
     * Tableau contenant une sélection de formateurs mis en avant
     * sur la page d’accueil.
     * Chaque formateur est défini par :
     * - son nom
     * - sa spécialité
     * - sa photo
     */

        $formateurs = [
            ['nom' => 'Karim Haddad',   'specialite' => 'Mathématiques', 'photo' => 'prof.jpeg'],
            ['nom' => 'Guillaume Durand', 'specialite' => 'Informatique',  'photo' => 'prof7.jpg'],
            ['nom' => 'Lucie Dupont',   'specialite' => 'Physique',      'photo' => 'prof13.jpg'],
            ['nom' => 'Luc Miquel',  'specialite' => 'Anglais',       'photo' => 'prof11.jpg'],
            ['nom' => 'Nora Benali',   'specialite' => 'SVT',      'photo' => 'prof5.jpg'],
            ['nom' => 'Amina Diallo',  'specialite' => 'Histoire',      'photo' => 'prof6.jpg'],
        ];

        /**
     * Tableau contenant les avis des apprenants.
     * Chaque avis comprend :
     * - le nom de l’apprenant
     * - sa photo
     * - son message
     * - une note sur 5
     */
        $avis = [
            ['nom' => 'Lina Martin', 'photo' => 'eleve1.jpeg', 'message' => 'Super plateforme, j\'ai trouvé un prof rapidement.', 'note' => 5],
            ['nom' => 'Sarah Martin', 'photo' => 'eleve2.jpeg', 'message' => 'Plateforme simple et efficace, cours clairs et très pédagogues.', 'note' => 4],
            ['nom' => 'Yanis Tombari', 'photo' => 'eleve3.jpg', 'message' => 'J’ai appris du vocabulaire utile pour la vie quotidienne.', 'note' => 4],
            ['nom' => 'Inès Rahmani', 'photo' => 'eleve4.jpeg', 'message' => 'Bonne séance, mais le groupe était un peu nombreux pour poser des questions.', 'note' => 3],
        ];

        /**
     * Rendu du template Twig landing/index.html.twig.
     * Les tableaux "formateurs" et "avis" sont envoyés à la vue
     * pour être affichés dynamiquement sur la page d’accueil.
     */
        return $this->render('landing/index.html.twig', [
            'formateurs' => $formateurs,
            'avis' => $avis,
        ]);
    }
};


