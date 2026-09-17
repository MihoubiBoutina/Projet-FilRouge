# 6. Documentation technique

## Installation

```bash
composer install
cp .env .env.local
# renseigner les variables locales sans les committer
docker compose up -d --build
php bin/console doctrine:migrations:migrate
```

## Configuration

Les variables `DATABASE_URL`, `MONGODB_URI`, `MONGODB_DB`, `MAILER_DSN` et les clés JWT sont fournies par l'environnement. Aucun secret ne doit être ajouté à un fichier versionné.

## Architecture

Symfony expose les contrôleurs API. Les entités Doctrine portent les données transactionnelles ; les documents MongoDB portent les avis et la traçabilité. Les repositories isolent l'accès aux données et les services portent les intégrations.

## Utilisation

- ouvrir l'application depuis l'URL locale fournie par Docker ;
- consulter `/api/ateliers` pour le catalogue ;
- utiliser l'interface d'inscription pour réserver un atelier ;
- consulter la documentation OpenAPI depuis l'endpoint configuré.

## Dépannage

Les fiches opérationnelles couvrent le conflit de ports Docker, les migrations Doctrine, Keycloak et MongoDB. Toute nouvelle panne reproduite doit obtenir une fiche avec symptôme, cause, commande de diagnostic et correction.

## Schéma d'architecture

Le schéma de flux de contribution est fourni dans `schemas/flux-contribution.svg` et référencé par la configuration MkDocs.
