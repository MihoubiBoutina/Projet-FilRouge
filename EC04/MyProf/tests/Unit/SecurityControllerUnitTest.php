<?php

namespace App\Tests\Unit;

use App\Controller\SecurityController;
use App\Entity\UserApprenant;
use App\Repository\UserApprenantRepository;
use App\Repository\UserFormateurRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SecurityControllerUnitTest extends TestCase
{
    #[Test]
    public function testLoginSuccessRedirectsToProfil(): void
    {
        // ==========================================
        // 1. ARRANGEMENT (Préparation du test)
        // ==========================================
        
        $user = new UserApprenant();
        $user->setEmail('test@myprof.com');
        // Un mot de passe hashé
        $user->setPassword(password_hash('superpassword', PASSWORD_DEFAULT));

        // On utilise une VRAIE session mais en mémoire "MockArray" (pas de fichier, pas de cookie)
        $session = new Session(new MockArraySessionStorage());

        $request = new Request([], ['email' => 'test@myprof.com', 'password' => 'superpassword']);
        $request->setMethod('POST');
        $request->setSession($session);

        $appRepoMock = $this->createMock(UserApprenantRepository::class);
        $formRepoMock = $this->createMock(UserFormateurRepository::class);
        $routerMock = $this->createMock(RouterInterface::class);

        $appRepoMock->method('findOneBy')->willReturn($user);
        $routerMock->method('generate')->willReturn('/mon-profil');

        $container = new Container();
        $container->set('router', $routerMock);
        
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        $controller = new SecurityController();
        $controller->setContainer($container);

        // ==========================================
        // 2. ACTION (Exécution de la méthode)
        // ==========================================
        $response = $controller->login($request, $session, $appRepoMock, $formRepoMock);

        // ==========================================
        // 3. ASSERTION (Vérification des résultats)
        // ==========================================
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/mon-profil', $response->getTargetUrl());
        
        // Vérification méticuleuse que "l'utilisateur" est bien dans la session !
        $this->assertTrue($session->has('user'));
        $this->assertEquals('test@myprof.com', $session->get('user')['email']);
        
        // Vérification qu'un message flash de succès a bien été enregistré
        $flashes = $session->getFlashBag()->get('success');
        $this->assertContains('Connexion réussie !', $flashes);
    }
}

