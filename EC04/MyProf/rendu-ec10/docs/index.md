# Guide de bonnes pratiques SkillHub

## 1. Périmètre et mode d'emploi

Ce guide s'applique au développement, à la revue et à la maintenance de SkillHub, une plateforme Symfony de gestion d'ateliers, d'inscriptions et d'avis.

**Publics concernés :** développeurs, reviewers et personnes chargées de maintenir la documentation.

**Engagements vérifiables :**

- toute modification de code passe par une branche et une Pull Request ;
- toute règle métier modifiée possède un test positif et un test négatif ;
- toute PR attend un check CI vert et une approbation humaine ;
- toute modification de comportement met à jour la section documentaire concernée.

Le guide se lit dans l'ordre : la partie A formalise les pratiques, puis la partie B fournit la checklist et son application à une revue réelle.

## Organisation

- Sections 2 à 6 : règles de développement, revue, tests et documentation technique.
- Sections 7 et 8 : partage, maintenance et amélioration continue.
- Annexes : outils directement réutilisables sur une Pull Request.

## Preuves dans le projet

Les exemples utilisent les classes `Atelier`, `AtelierApiController`, `ApiExceptionSubscriber`, les tests PHPUnit et les workflows GitHub Actions réellement présents dans SkillHub.
