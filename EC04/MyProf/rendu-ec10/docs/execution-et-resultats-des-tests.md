# 5. Exécution et résultats des tests

## Procédure locale

Depuis `EC04/MyProf` :

```bash
composer install
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:schema:create --env=test
php tools/seed-test-database.php
vendor/bin/phpunit
```

MongoDB doit être accessible sur `127.0.0.1:27017` pour les scénarios d'avis et de traçabilité.

## Procédure CI

Le workflow racine `.github/workflows/ci.yml` :

- installe PHP 8.2 et les extensions requises ;
- démarre MongoDB comme service ;
- crée le schéma SQLite de test ;
- initialise le formateur, l'apprenant et l'atelier de test ;
- lance `vendor/bin/phpunit`.

## Format de restitution

Un résultat doit indiquer la commande, l'environnement, le nombre de tests, le nombre d'assertions, les échecs et l'action corrective. Exemple :

```text
Commande : vendor/bin/phpunit
Environnement : PHP 8.2, APP_ENV=test, SQLite, MongoDB
Résultat : 70 tests, 126 assertions, 0 échec
Décision : validation
```

Un échec est conservé dans la PR avec son lien CI et n'est pas remplacé par une simple affirmation « ça marche ».
