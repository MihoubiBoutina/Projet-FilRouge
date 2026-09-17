<?php

namespace App\Tests\Integration;

use App\Entity\Atelier;
use App\Entity\InscriptionAtelier;
use App\Entity\UserApprenant;
use App\Exception\AtelierCompletException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AtelierInscriptionCapacityIntegrationTest extends KernelTestCase
{
    private ManagerRegistry $doctrine;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->doctrine = static::getContainer()->get('doctrine');
    }

    public function testInscriptionDoesNotExceedCapacity(): void
    {
        $entityManager = $this->doctrine->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $formateur = new \App\Entity\UserFormateur();
        $formateur->setNom('Martin');
        $formateur->setPrenom('Claire');
        $formateur->setEmail('claire@example.com');
        $formateur->setPassword('secret');

        $atelier = new Atelier();
        $atelier->setTitre('Atelier PHP');
        $atelier->setDescription('Description');
        $atelier->setStartAt(new \DateTimeImmutable('2026-10-01 10:00:00'));
        $atelier->setDureeHeure(2);
        $atelier->setPlace(1);
        $atelier->setFormateur($formateur);

        $apprenant1 = new UserApprenant();
        $apprenant1->setNom('Durand');
        $apprenant1->setPrenom('Paul');
        $apprenant1->setEmail('paul@example.com');
        $apprenant1->setPassword('secret');

        $apprenant2 = new UserApprenant();
        $apprenant2->setNom('Fay');
        $apprenant2->setPrenom('Sophie');
        $apprenant2->setEmail('sophie@example.com');
        $apprenant2->setPassword('secret');

        $entityManager->persist($formateur);
        $entityManager->persist($atelier);
        $entityManager->persist($apprenant1);
        $entityManager->persist($apprenant2);
        $entityManager->flush();

        $inscription1 = new InscriptionAtelier();
        $inscription1->setAtelier($atelier);
        $inscription1->setApprenant($apprenant1);

        $atelier->addInscriptionAtelier($inscription1);
        $entityManager->persist($inscription1);
        $entityManager->flush();

        $this->assertSame(1, $atelier->countInscriptions());
        $this->assertFalse($atelier->canAcceptNewInscription());

        $inscription2 = new InscriptionAtelier();
        $inscription2->setAtelier($atelier);
        $inscription2->setApprenant($apprenant2);

        $this->expectException(AtelierCompletException::class);
        $atelier->addInscriptionAtelier($inscription2);
    }
}
