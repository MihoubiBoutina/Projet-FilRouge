# Architecture

## Vue d'ensemble

MyProf repose sur une architecture orientée services, avec :

- une application Symfony pour la logique métier,
- une base MySQL pour les données transactionnelles,
- une base MongoDB pour les données documentaires ou spécifiques,
- des conteneurs Docker pour l'exécution locale.

## Composants principaux

### Application Symfony

Le cœur applicatif est développé avec Symfony 7 et contient :

- les contrôleurs API,
- les entités et repositories,
- les services métier,
- les formulaires et vues Twig,
- les événements et listeners.

### Base de données relationnelle

La base MySQL est utilisée pour les structures fortement typées telles que :

- les ateliers,
- les apprenants,
- les formateurs,
- les inscriptions,
- les avis et références.

### Base de données documentaire

MongoDB permet de stocker des données complémentaires plus souples ou orientées document, selon les besoins d'évolution de la solution.

## Flux de fonctionnement

1. Un client ou une interface envoie une requête vers l'API.
2. Symfony valide la requête et les règles métiers.
3. Les services appliquent les traitements attendus.
4. Les données sont lus/écrites dans les bonnes sources de données.
5. La réponse est renvoyée au format JSON ou via l'interface web.

## Points d'attention

- sécuriser les endpoints avec contrôle d'accès,
- conserver des migrations de schéma explicites,
- surveiller la cohérence entre MySQL et MongoDB,
- documenter toute évolution majeure de l'API.
