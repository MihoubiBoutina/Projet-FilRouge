<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApprenantsController extends AbstractController
{
    /**
 * Cette route correspond à la page des apprenants.
 * Elle est accessible via l’URL /apprenants
 * et porte le nom app_apprenants.
 */
    #[Route('/apprenants', name: 'app_apprenants')]
    public function index(): Response
    {
         /**
     * Tableau contenant les avis des apprenants.
     * Chaque avis est représenté par un tableau associatif
     * avec le nom, le message, la photo et la note.
     */
        $avis = [
                [
                    'nom' => 'Lina Martin',
                    'message' => "Super plateforme, j'ai trouvé un prof rapidement.",
                    'photo' => 'image/eleve1.jpeg',
                    'note' => 5,
                ],
                [
                    'nom' => 'Sarah Martin',
                    'message' => "Cours clairs et très pédagogues.",
                    'photo' => 'image/eleve2.jpeg',
                    'note' => 4,
                ],
                [
                    'nom' => 'Yanis Tombari',
                    'message' => "J’ai appris du vocabulaire utile pour la vie quotidienne.",
                    'photo' => 'image/eleve3.jpg',
                    'note' => 4,
                ],
                [
                    'nom' => 'Inès Rahmani',
                    'message' => "Bonne séance, mais le groupe était un peu nombreux pour poser des questions.",
                    'photo' => 'image/eleve4.jpeg',
                    'note' => 3,
                ],
                [
                    'nom' => 'Rania Haddad',
                    'message' => "La prononciation a été bien travaillée, avec des corrections gentilles",
                    'photo' => 'image/eleve5.jpg',
                    'note' => 4,
                ],
                [
                    'nom' => 'Clara Rousseau',
                    'message' => "Cours clairs et très pédagogues.",
                    'photo' => 'image/eleve6.jpg',
                    'note' => 4,
                ],
                [
                    'nom' => 'Lucas Moreau',
                    'message' => "J’ai apprécié les corrections : précises mais encourageantes",
                    'photo' => 'image/eleve7.jpg',
                    'note' => 4,
                ],
                [
                    'nom' => 'Sophie Laurent',
                    'message' => "J’aurais aimé un peu plus de temps sur les problèmes.",
                    'photo' => 'image/eleve1.jpeg',
                    'note' => 3,
                ],
            ];

        /**
     * Rendu du template Twig apprenants/index.html.twig
     * avec le tableau des avis envoyé à la vue.
     */
        return $this->render('apprenants/index.html.twig', [
            'avis' => $avis,
        ]);
    }
}
