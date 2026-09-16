<?php

declare(strict_types=1);

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
$repository = $entityManager->getRepository(UserFormateur::class);

$formateur = $repository->findOneBy(['email' => 'ci.formateur@example.com']);

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

printf("Formateur de test disponible avec l'identifiant %d.%s", $formateur->getId(), PHP_EOL);
