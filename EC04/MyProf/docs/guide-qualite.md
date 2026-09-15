# Guide Qualité

## Objectif

Ce guide décrit les principes de qualité applicables à la plateforme MyProf. Il vise à garantir la fiabilité, la maintenabilité, la sécurité et la traçabilité des évolutions fonctionnelles et techniques du système.

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

## Références

- Documentation technique du projet,
- tests PHPUnit,
- CI GitHub Actions,
- scripts de build et de publication,
- fiches de dépannage et stratégie IAM / sécurité.
