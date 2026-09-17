# Rétrospective d'équipe

## Objectif

Cette rétropective formalise la synthèse de l’équipe après une itération de travail. Elle vise à identifier ce qui a bien fonctionné, ce qui a été difficile, et ce qu’il faut améliorer pour la prochaine période.

Elle complète la revue de code et la cause racine en donnant un cadre d’amélioration continue. Son rôle est de transformer les retours de validation en actions concrètes, claires et suivies.

## Format 4L

### Liked

- Les tâches ont été bien réparties lors de la mise en place de la documentation qualité.
- Les échanges sur les besoins et les validations ont permis de clarifier les attentes métier.
- Les points de vigilance sur la sécurité et la traçabilité ont été rapidement identifiés.

### Learned

- Il faut documenter plus tôt les décisions d’architecture pour éviter les ambiguïtés.
- La revue croisée a permis de détecter des points de sécurité et de maintenance avant le déploiement.
- Les tests automatisés doivent être associés à chaque correctif pour sécuriser les évolutions.

### Lacked

- La communication sur les dépendances de configuration Docker et Keycloak a été insuffisante.
- Certaines tâches de documentation ont été réalisées en fin de sprint, ce qui a ralenti la validation.
- Le suivi des risques techniques n’a pas toujours été assez explicite.

### Longed for

- Gagner en rigueur sur la planification des tâches documentaires et de validation.
- Mettre en place des points de contrôle plus fréquents sur les intégrations externes.
- Renforcer la culture de test avant validation finale.

## Plan d’action

| Priorité | Action                                                               | Responsable            | Échéance           |
| -------- | -------------------------------------------------------------------- | ---------------------- | ------------------ |
| Haute    | Ajouter un point de contrôle qualité dans chaque sprint              | Équipe                 | À chaque itération |
| Haute    | Documenter les dépendances et variables d’environnement dès le début | Tech lead              | 1 semaine          |
| Moyenne  | Ajouter une validation de tests sur chaque correctif fonctionnel     | Développeurs           | 2 semaines         |
| Moyenne  | Formaliser les revues de code et les retours de pair-programming     | Équipe                 | 1 sprint           |
| Faible   | Prévoir un temps de revue documentaire à la fin de chaque cycle      | Product owner / équipe | Chaque sprint      |

## Conclusion

La rétrospective montre que l’équipe a progressé sur la clarté des rôles, la qualité de la revue et la gestion des risques. Les principaux axes d’amélioration concernent la documentation, la revue systématique et le renforcement des tests automatisés.
