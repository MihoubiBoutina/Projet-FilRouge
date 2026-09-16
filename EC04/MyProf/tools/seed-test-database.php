<?php

declare(strict_types=1);

use App\Entity\Atelier;
use App\Entity\UserApprenant;
use App\Entity\UserFormateur;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER['APP_ENV'] = 'test';
$_ENV['APP_ENV'] = 'test';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new Kernel('test', true);
$kernel->boot();

$entityManager = $kernel->getContainer()->get('doctrine')->getManager();
$formateurRepository = $entityManager->getRepository(UserFormateur::class);
$apprenantRepository = $entityManager->getRepository(UserApprenant::class);
$atelierRepository = $entityManager->getRepository(Atelier::class);

$formateur = $formateurRepository->findOneBy(['email' => 'ci.formateur@example.com']);

if (!$formateur) {
    $formateur = (new UserFormateur())
        ->setNom('CI')
        ->setPrenom('Formateur')
        ->setEmail('ci.formateur@example.com')
        ->setPassword('test-password')
        ->setSpecialite('Tests automatises');

    $entityManager->persist($formateur);
    $entityManager->flush();
}

$apprenant = $apprenantRepository->findOneBy(['email' => 'ci.apprenant@example.com']);

if (!$apprenant) {
    $apprenant = (new UserApprenant())
        ->setNom('CI')
        ->setPrenom('Apprenant')
        ->setEmail('ci.apprenant@example.com')
        ->setPassword('test-password');

    $entityManager->persist($apprenant);
    $entityManager->flush();
}

$atelier = $atelierRepository->findOneBy(['titre' => 'Atelier de test CI']);

if (!$atelier) {
    $atelier = (new Atelier())
        ->setTitre('Atelier de test CI')
        ->setDescription('Atelier créé pour les tests automatisés.')
        ->setDureeHeure(2)
        ->setPlace(20)
        ->setStartAt(new \DateTimeImmutable('+1 day'))
        ->setFormateur($formateur);

    $entityManager->persist($atelier);
    $entityManager->flush();
}

printf(
    "Données de test disponibles : formateur=%d, apprenant=%d, atelier=%d.%s",
    $formateur->getId(),
    $apprenant->getId(),
    $atelier->getId(),
    PHP_EOL
);
