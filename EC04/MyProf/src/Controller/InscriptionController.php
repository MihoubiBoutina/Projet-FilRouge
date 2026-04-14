<?php

namespace App\Controller;

use App\Entity\UserApprenant;
use App\Entity\UserFormateur;
use App\Repository\UserApprenantRepository;
use App\Repository\UserFormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        SessionInterface $session,
        EntityManagerInterface $em,
        UserApprenantRepository $apprenantRepo,
        UserFormateurRepository $formateurRepo,
    ): Response {
        $errors = [];
        $data = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'role' => 'apprenant', // apprenant|formateur
            'password' => '',
            'specialite' => '', // optionnel si formateur
        ];

        if ($request->isMethod('POST')) {
            $data['nom'] = trim((string) $request->request->get('nom'));
            $data['prenom'] = trim((string) $request->request->get('prenom'));
            $data['email'] = trim((string) $request->request->get('email'));
            $data['role'] = (string) $request->request->get('role', 'apprenant');
            $data['password'] = (string) $request->request->get('password', '');
            $data['specialite'] = trim((string) $request->request->get('specialite', ''));

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
        'password' => [
            new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire']),
            new Assert\Length(min: 6, max: 255, minMessage: 'Minimum 6 caractères'),
        ],
        'specialite' => [
            // autorisé vide, mais si rempli on limite
            new Assert\Length(max: 255),
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
            } 

            // ✅ Vérif email déjà utilisé (dans les 2 tables)
            if (!$errors) {
                $emailExists =
                    $apprenantRepo->findOneBy(['email' => $data['email']]) ||
                    $formateurRepo->findOneBy(['email' => $data['email']]);

                if ($emailExists) {
                    $errors['[email]'][] = 'Cet email est déjà utilisé.';
                }
            }

            if (!$errors) {
                $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

                if ($data['role'] === 'formateur') {
                    $user = new UserFormateur();
                    $user->setNom($data['nom']);
                    $user->setPrenom($data['prenom']);
                    $user->setEmail($data['email']);
                    $user->setPassword($hashed);

                    // specialite facultative
                    $user->setSpecialite($data['specialite'] !== '' ? $data['specialite'] : null);
                } else {
                    $user = new UserApprenant();
                    $user->setNom($data['nom']);
                    $user->setPrenom($data['prenom']);
                    $user->setEmail($data['email']);
                    $user->setPassword($hashed);
                }

                $em->persist($user);
                $em->flush();

                // ✅ Session (comme avant)
                $session->set('user', [
                    'id' => $user->getId(),
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ]);

                $this->addFlash('success', 'Inscription enregistrée en base de données.');
                return $this->redirectToRoute('app_apprenant_profil');
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


        #[Route('/profil/modifier', name: 'app_profil_edit', methods: ['GET', 'POST'])]
public function editProfil(
    Request $request, 
    SessionInterface $session, 
    EntityManagerInterface $em,
    UserApprenantRepository $apprenantRepo,
    UserFormateurRepository $formateurRepo
): Response {
    $userSess = $session->get('user');
    if (!$userSess) return $this->redirectToRoute('app_inscription');

    // 1. Récupérer l'entité complète en BDD
    $user = ($userSess['role'] === 'formateur') 
        ? $formateurRepo->find($userSess['id']) 
        : $apprenantRepo->find($userSess['id']);

    if ($request->isMethod('POST')) {
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        
        if ($user instanceof UserFormateur) {
            $user->setSpecialite($request->request->get('specialite'));
        }

        $em->flush();

        // 2. Mettre à jour la session pour que l'affichage change partout
        $session->set('user', array_merge($session->get('user'), [
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom()
        ]));

        $this->addFlash('success', 'Profil mis à jour !');
    }

    return $this->render('profil/edit.html.twig', ['user' => $user]);
}


#[Route('/profil/password', name: 'app_profil_password', methods: ['GET', 'POST'])]
public function changePassword(Request $request, SessionInterface $session, EntityManagerInterface $em, UserApprenantRepository $appRepo, UserFormateurRepository $formRepo): Response 
{
    $userSess = $session->get('user');
    if (!$userSess) return $this->redirectToRoute('app_inscription');

    if ($request->isMethod('POST')) {
        $oldPwd = $request->request->get('old_password');
        $newPwd = $request->request->get('new_password');

        $user = ($userSess['role'] === 'formateur') ? $formRepo->find($userSess['id']) : $appRepo->find($userSess['id']);

        // Vérifier si l'ancien mot de passe est correct
        if (password_verify($oldPwd, $user->getPassword())) {
            $user->setPassword(password_hash($newPwd, PASSWORD_BCRYPT));
            $em->flush();
            $this->addFlash('success', 'Mot de passe modifié.');
        } else {
            $this->addFlash('error', 'Ancien mot de passe incorrect.');
        }
    }
    return $this->render('profil/password.html.twig');
}


#[Route('/mot-de-passe-oublie', name: 'app_password_forgot', methods: ['GET', 'POST'])]
public function forgotPassword(
    Request $request, 
    UserApprenantRepository $apprenantRepo, 
    UserFormateurRepository $formateurRepo
): Response {
    if ($request->isMethod('POST')) {
        $email = $request->request->get('email');
        
        // Chercher l'utilisateur dans les deux tables
        $user = $apprenantRepo->findOneBy(['email' => $email]) ?? $formateurRepo->findOneBy(['email' => $email]);

        if ($user) {
            // En temps normal, on enverrait un mail ici.
            // Pour ton projet, on redirige vers le formulaire de reset avec l'ID (en simulation)
            return $this->redirectToRoute('app_password_reset', [
                'id' => $user->getId(),
                'role' => ($user instanceof UserFormateur) ? 'formateur' : 'apprenant'
            ]);
        }
        $this->addFlash('error', 'Cet email n\'existe pas.');
    }
    return $this->render('inscription/forgot_password.html.twig');
}

#[Route('/reinitialiser-mot-de-passe/{role}/{id}', name: 'app_password_reset', methods: ['GET', 'POST'])]
public function resetPassword(
    string $role, 
    int $id, 
    Request $request, 
    EntityManagerInterface $em,
    UserApprenantRepository $apprenantRepo, 
    UserFormateurRepository $formateurRepo
): Response {
    // Récupérer l'utilisateur selon son rôle et son ID
    $user = ($role === 'formateur') ? $formateurRepo->find($id) : $apprenantRepo->find($id);

    if (!$user) {
        return $this->redirectToRoute('app_inscription');
    }

    if ($request->isMethod('POST')) {
        $newPwd = $request->request->get('password');
        
        if (strlen($newPwd) < 6) {
            $this->addFlash('error', 'Le mot de passe doit faire au moins 6 caractères.');
        } else {
            // On hache le nouveau mot de passe
            $user->setPassword(password_hash($newPwd, PASSWORD_BCRYPT));
            $em->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé ! Connectez-vous.');
            return $this->redirectToRoute('app_inscription'); // Redirige vers login/inscription
        }
    }

    return $this->render('inscription/reset_password.html.twig', ['user' => $user]);
}

}
