<?php

namespace App\Tests\Controller;

use App\Entity\UserApprenant;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    #[Test]
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Bon retour');
    }

    #[Test]
    public function testLoginFailure(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connexion');
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);
        
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-error', 'Email ou mot de passe incorrect');
    }

    #[Test]
    public function testLogout(): void
    {
        $client = static::createClient();
        
        // Simuler une connexion via la session
        $container = static::getContainer();
        $session = $container->get('session.factory')->createSession();
        $session->set('user', [
            'id' => 1,
            'role' => 'apprenant',
            'email' => 'test@example.com'
        ]);
        $session->save();
        
        // On force le cookie de session sur le client
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId()));

        $client->request('GET', '/deconnexion');
        $this->assertResponseRedirects('/connexion');
        
        $client->followRedirect();
        $this->assertSelectorExists('.flash-success');
    }
}
