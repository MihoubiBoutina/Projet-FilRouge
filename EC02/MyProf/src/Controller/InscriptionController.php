<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function inscription(
        Request $request,
        ValidatorInterface $validator,
        SessionInterface $session
    ): Response {
        $errors = [];
        $data = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'role' => 'apprenant', // apprenant|formateur
        ];

        if ($request->isMethod('POST')) {
            $data['nom'] = trim((string) $request->request->get('nom'));
            $data['prenom'] = trim((string) $request->request->get('prenom'));
            $data['email'] = trim((string) $request->request->get('email'));
            $data['role'] = (string) $request->request->get('role', 'apprenant');

            // Validation (sans entité)
    $constraints = new Assert\Collection([
    'fields' => [
        'nom' => [
            new Assert\NotBlank([
                'message' => 'Le nom est obligatoire',
            ]),
            new Assert\Length(null,
                2,
                60,
            ),
        ],

        'prenom' => [
            new Assert\NotBlank([
                'message' => 'Le prénom est obligatoire',
            ]),
            new Assert\Length(null, 2, 60,),
        ],

        'email' => [
            new Assert\NotBlank([
                'message' => 'L’email est obligatoire',
            ]),
            new Assert\Email([
                'message' => 'Email invalide',
            ]),
            new Assert\Length(null, 15),
        ],

        'role' => [
            new Assert\Choice([
                'choices' => ['apprenant', 'formateur'],
                'message' => 'Rôle invalide',
            ]),
        ],
    ],

    // (optionnel mais conseillé)
    'allowExtraFields' => true,
    'allowMissingFields' => false,
]);
            $violations = $validator->validate($data, $constraints);

            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[$v->getPropertyPath()][] = $v->getMessage();
                }
            } else {
                // Enregistrement en session (sans BDD)
                $inscriptions = $session->get('inscriptions', []);
                $inscriptions[] = [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ];
                $session->set('inscriptions', $inscriptions);

                $session->set('user', [
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ]);

                $this->addFlash('success', 'Inscription enregistrée.');
                return $this->redirectToRoute('app_landing');
            }
        }

        return $this->render('inscription/index.html.twig', [
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
        public function logout(SessionInterface $session): Response
        {
            // Supprime l'utilisateur de la session
            $session->remove('user');

            // Message flash
            $this->addFlash('success', 'Vous êtes maintenant déconnecté.');

            // Redirection vers la home
            return $this->redirectToRoute('app_landing');
        }

}
