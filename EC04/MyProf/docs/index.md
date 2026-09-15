# MyProf

Bienvenue dans le portail documentaire MyProf.

Cette documentation regroupe :

- l'architecture applicative,
- les décisions d'architecture formalisées (ADR),
- la stratégie de sécurité cloud et des identités,
- les guides de qualité et de revue de code,
- les fiches de dépannage opérationnel.

## Objectif

MyProf est une application de gestion d'ateliers de formation destinée à centraliser les données des apprenants, formateurs, ateliers et avis, tout en exposant une API REST sécurisée, traçable et documentée.

## Accès rapide

- [Architecture](architecture.md)
- [Sécurité Cloud](security/iam-keycloak.md)
- [Guide Qualité](guide-qualite.md)
- [Dépannage](troubleshooting.md)
- [ADR](adr/0001-choix-architecture-persistance.md)

## Environnement de développement

Le projet est construit autour de Symfony, MySQL, MongoDB et Keycloak, orchestrés localement via Docker Compose.

## Commandes utiles

```bash
composer install
docker compose up -d --build
php bin/console doctrine:migrations:migrate
mkdocs serve
```
