# Guide Qualité

## Objectif

Ce guide décrit les principes de qualité applicables à la plateforme MyProf. Il vise à garantir la fiabilité, la maintenabilité, la sécurité et la traçabilité des évolutions fonctionnelles et techniques du système.

Il sert de point d'entrée à la démarche qualité du projet et oriente le lecteur vers les documents plus détaillés selon son besoin : revue de code, analyse de cause racine ou amélioration continue.

## La démarche qualité du projet

Le guide est “vivant” : il n’est pas un simple document de présentation. Il repose sur des éléments réels du projet :

- la logique métier autour des ateliers et des inscriptions,
- les tests automatisés sur la capacité des ateliers,
- la revue de code entre pairs,
- l’analyse de cause racine des défauts,
- la rétrospective et le plan d’amélioration continue.

Les éléments de référence sont :

- la classe Atelier du backend, qui centralise la règle de capacité,
- les tests unitaires de capacité des ateliers,
- le guide de revue de code,
- l’analyse de cause racine,
- la rétrospective du projet.

## Critères de qualité

### Fonctionnel

- conformité avec les besoins métiers,
- couverture des cas principaux de gestion des ateliers,
- validation des flux d'inscription et d'avis,
- prévention des erreurs de logique métier critiques comme la surcharge de capacité.

### Technique

- code lisible et maintenable,
- tests automatisés,
- intégration continue,
- documentation technique à jour,
- séparation claire des responsabilités par couche.

### Sécurité

- protection des endpoints,
- validation des entrées et sorties,
- gestion des secrets via variables d'environnement,
- contrôle des accès pour les rôles applicatifs,
- sécurisation des identités via Keycloak et MFA pour les comptes sensibles.

### Performance

- optimisation des requêtes,
- surveillance des services,
- gestion des volumes de données dans les bases de données,
- vigilance sur les coûts opérationnels liés aux doubles bases.

## Processus de contrôle qualité

1. Vérifier la couverture fonctionnelle via les tests.
2. Contrôler les erreurs de configuration dans les environnements.
3. Valider les intégrations avec MySQL et MongoDB.
4. Vérifier la qualité des documents de déploiement et de maintenance.
5. Publier la documentation générée dans l'environnement de référence.
6. Revoir les écarts de conception par rapport à la politique de sécurité et aux normes applicatives.

## Livrables attendus

- code versionné,
- tests automatisés,
- documentation technique,
- rapport de validation et suivi des anomalies,
- revue de code structurée et plan d'action associé.

## Fichiers de référence

## Assemblage et publication du guide

Le guide est assemblé avec MkDocs à partir du dossier `docs/` et de la navigation définie dans `mkdocs.yml`.

La vérification locale s'effectue avec :

```bash
mkdocs build --strict
```

La publication est automatisée par le workflow GitHub Actions `documentation.yml` :

1. le workflow s'exécute après une modification de la documentation sur `main` ou à la demande ;
2. les dépendances documentaires sont installées ;
3. MkDocs construit le site en mode strict ;
4. le dossier `site/` est envoyé comme artefact GitHub Pages ;
5. l'artefact est déployé sur l'environnement `github-pages`.

Pour activer la publication dans GitHub, le dépôt doit utiliser **Settings > Pages > Source: GitHub Actions**. Une fois la PR fusionnée dans `main`, l'adresse publiée est disponible dans le résumé du workflow ou dans la page **Settings > Pages**.

## Références

- Documentation technique du projet,
- tests PHPUnit,
- CI GitHub Actions,
- scripts de build et de publication,
- fiches de dépannage et stratégie IAM / sécurité.
