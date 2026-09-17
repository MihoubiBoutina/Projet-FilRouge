<?php

namespace App\Tests\Unit;

use App\Entity\Atelier;
use App\Entity\InscriptionAtelier;
use App\Entity\UserApprenant;
use PHPUnit\Framework\TestCase;

class AtelierCapacityTest extends TestCase
{
    public function testAtelierAcceptsInscriptionWhenCapacityIsNotReached(): void
    {
        $atelier = new Atelier();
        $atelier->setPlace(3);

        $this->assertTrue($atelier->canAcceptNewInscription());
    }

    public function testAtelierRejectsInscriptionWhenCapacityIsReached(): void
    {
        $atelier = new Atelier();
        $atelier->setPlace(1);

        $apprenant = new UserApprenant();
        $apprenant->setNom('Dupont');
        $apprenant->setPrenom('Alice');
        $apprenant->setEmail('alice@example.com');
        $apprenant->setPassword('secret');

        $inscription = new InscriptionAtelier();
        $inscription->setApprenant($apprenant);
        $inscription->setAtelier($atelier);

        $atelier->addInscriptionAtelier($inscription);

        $this->assertFalse($atelier->canAcceptNewInscription());
    }
}
