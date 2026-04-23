<?php

namespace App\EventSubscriber;

use App\Entity\UserApprenant;
use App\Entity\UserFormateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();

        // Récupération de l'ID de session classique PHP/Symfony
        $sessionId = $request->hasSession() ? $request->getSession()->getId() : null;

        if ($sessionId && ($user instanceof UserApprenant || $user instanceof UserFormateur)) {
            // Vérifier si la méthode existe (bien qu'elle ait été ajoutée récemment)
            if (method_exists($user, 'setLastSessionId')) {
                $user->setLastSessionId($sessionId);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }
    }
}
