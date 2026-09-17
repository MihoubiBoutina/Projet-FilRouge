<?php

namespace App\Tests\Unit;

use App\Controller\api\AtelierApiController;
use App\Entity\Atelier;
use App\Entity\InscriptionAtelier;
use App\Entity\UserApprenant;
use App\Service\NotificationServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Atelier::class)]
#[CoversClass(InscriptionAtelier::class)]
class AtelierCapacityBusinessTest extends TestCase
{
    private function createAtelier(int $place): Atelier
    {
        $atelier = new Atelier();
        $atelier->setPlace($place);

        return $atelier;
    }

    private function createApprenant(string $email = 'apprenant@example.com'): UserApprenant
    {
        $apprenant = new UserApprenant();
        $apprenant->setNom('Martin');
        $apprenant->setPrenom('Alice');
        $apprenant->setEmail($email);
        $apprenant->setPassword('secret');

        return $apprenant;
    }

    private function createInscription(Atelier $atelier, string $email): InscriptionAtelier
    {
        $inscription = new InscriptionAtelier();
        $inscription->setAtelier($atelier);
        $inscription->setApprenant($this->createApprenant($email));

        return $inscription;
    }

    public function testNominalAcceptsInscriptionBelowCapacity(): void
    {
        $atelier = $this->createAtelier(12);

        $this->assertTrue($atelier->canAcceptNewInscription());
    }

    public function testNominalCountsExistingInscriptions(): void
    {
        $atelier = $this->createAtelier(12);

        for ($i = 0; $i < 3; $i++) {
            $atelier->addInscriptionAtelier($this->createInscription($atelier, 'user' . $i . '@example.com'));
        }

        $this->assertSame(3, $atelier->countInscriptions());
    }

    public function testNominalAllowsInscriptionWhenExactLimitMinusOneIsReached(): void
    {
        $atelier = $this->createAtelier(12);

        for ($i = 0; $i < 11; $i++) {
            $atelier->addInscriptionAtelier($this->createInscription($atelier, 'user' . $i . '@example.com'));
        }

        $this->assertTrue($atelier->canAcceptNewInscription());
    }

    public function testNominalAddsAnInscriptionWhenAllowed(): void
    {
        $atelier = $this->createAtelier(2);

        $atelier->addInscriptionAtelier($this->createInscription($atelier, 'first@example.com'));

        $this->assertSame(1, $atelier->countInscriptions());
        $this->assertTrue($atelier->canAcceptNewInscription());
    }

    public function testNominalDeclaresFullWhenCapacityIsReached(): void
    {
        $atelier = $this->createAtelier(1);

        $atelier->addInscriptionAtelier($this->createInscription($atelier, 'first@example.com'));

        $this->assertFalse($atelier->canAcceptNewInscription());
    }

    public function testLimitAtCapacityIsRejected(): void
    {
        $atelier = $this->createAtelier(12);

        for ($i = 0; $i < 12; $i++) {
            $atelier->addInscriptionAtelier($this->createInscription($atelier, 'user' . $i . '@example.com'));
        }

        $this->assertFalse($atelier->canAcceptNewInscription());
    }

    public function testLimitAtCapacityPlusOneRemainsRejected(): void
    {
        $atelier = $this->createAtelier(12);

        for ($i = 0; $i < 13; $i++) {
            try {
                $atelier->addInscriptionAtelier($this->createInscription($atelier, 'user' . $i . '@example.com'));
            } catch (\LogicException $exception) {
                $this->assertSame('Cet atelier est complet : aucune inscription supplémentaire ne peut être ajoutée.', $exception->getMessage());
                break;
            }
        }

        $this->assertSame(12, $atelier->countInscriptions());
    }

    public function testLimitWhenPlaceIsNullAcceptsInsertion(): void
    {
        $atelier = new Atelier();
        $property = new \ReflectionProperty(Atelier::class, 'place');
        $property->setAccessible(true);
        $property->setValue($atelier, null);

        $this->assertTrue($atelier->canAcceptNewInscription());
    }

    public function testErrorWhenAddingInscriptionPastCapacityThrowsLogicException(): void
    {
        $atelier = $this->createAtelier(1);
        $atelier->addInscriptionAtelier($this->createInscription($atelier, 'first@example.com'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cet atelier est complet : aucune inscription supplémentaire ne peut être ajoutée.');

        $atelier->addInscriptionAtelier($this->createInscription($atelier, 'second@example.com'));
    }

    public function testErrorWhenNotificationDependencyIsCalledWithExpectedPayload(): void
    {
        $mock = $this->createMock(NotificationServiceInterface::class);
        $mock
            ->expects($this->once())
            ->method('sendEmailNotification')
            ->with('formateur@example.com', 'Nouvel atelier', 'Un atelier a été créé');

        $mock->sendEmailNotification('formateur@example.com', 'Nouvel atelier', 'Un atelier a été créé');
    }
}
