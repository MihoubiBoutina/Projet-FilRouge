# Revue de code et grille de contrôle qualité

## Objectif

Cette fiche formalise la méthode de revue de code utilisée pour garantir la qualité du projet MyProf. Elle couvre les critères les plus importants pour une revue constructive, rapide et reproductible : lisibilité, sécurité, typage PHP, tests et maintenabilité.

Cette revue s’applique notamment au TP 4, où la logique de qualité passe par une PR, une relecture par un pair et des retours d’amélioration. Le document doit être exploitable par la personne qui reprend le projet afin de comprendre la logique de validation avant fusion.

## Grille de critères de revue

| Critère        | Questions à se poser                                                    | Niveau attendu                   |
| -------------- | ----------------------------------------------------------------------- | -------------------------------- |
| Lisibilité     | Le code est-il clair, bien nommé, sans logique cachée ?                 | Oui, sans ambiguïté              |
| Typage PHP     | Les signatures de fonctions et les types sont-ils cohérents ?           | Strictement typé quand pertinent |
| Sécurité       | Les entrées sont-elles validées ? Les sorties sécurisées ?              | Sans vulnérabilité évidente      |
| Tests          | Le changement est-il couvert par des tests unitaires ou fonctionnels ?  | Oui, ou justification claire     |
| Maintenabilité | La logique est-elle isolée, réutilisable et documentée ?                | Facile à évoluer                 |
| Performance    | Le code introduit-il des boucles ou requêtes inutiles ?                 | Satisfaisant et contrôlé         |
| Cohérence      | Le code suit-il les conventions du projet et l’architecture existante ? | Conforme                         |

### Critères détaillés

#### 1. Lisibilité

- noms explicites de variables, méthodes et classes ;
- méthodes courtes et responsabilités claires ;
- absence de logique conditionnelle excessive ou de code dupliqué ;
- commentaires utiles, pas redondants.

#### 2. Typage PHP

- types explicites sur les paramètres et valeurs de retour quand cela est possible ;
- gestion correcte des types nullable et des exceptions ;
- cohérence des signatures entre services, contrôleurs et repositories.

#### 3. Sécurité

- validation des entrées utilisateur ;
- contrôle des permissions et droits d’accès ;
- absence de fuite d’informations sensibles ;
- protection contre les erreurs courantes : injection, accès non autorisé, défaut de validation.

#### 4. Tests

- ajout ou mise à jour des tests PHPUnit pertinents ;
- vérification des cas positifs et négatifs ;
- couverture des comportements impactés par la modification.

#### 5. Maintenabilité

- code suffisamment découplé pour faciliter l’évolution ;
- logique métier distincte des couches techniques ;
- documentation ajustée si le comportement change.

## Procédure de revue

1. Vérifier le contexte métier et technique du correctif.
2. Lire le diff avec une attention particulière sur la logique impactée.
3. Vérifier les points de sécurité et d’intégrité des données.
4. Contrôler la couverture de tests et la qualité de la validation.
5. Formuler des commentaires constructifs, concrets et actionnables.

## Exemple de commentaire de revue

- « Le contrôle d’accès est absent sur cette route ; il faut vérifier le rôle attendu. »
- « Le type de retour n’est pas explicite et la logique est difficile à lire ; il faudrait isoler cette transformation dans un service dédié. »
- « Le test couvre le cas nominal mais pas le cas d’erreur ; il manque la vérification de la gestion d’exception. »

## Preuve de revue croisée

Une revue croisée a été effectuée entre deux développeurs sur une modification du projet. La validation a porté sur la lisibilité du code, la sécurité, les tests et la cohérence fonctionnelle.

### Extrait de revue (exemple)

```text
Reviewer A: "La logique métier est claire, mais le contrôle d’accès sur la route doit être explicitement vérifié."
Reviewer B: "Le type de retour est cohérent, mais il manque un test sur le cas de validation négative."
Reviewer A: "Le code est propre et lisible ; il faut seulement compléter la documentation et sécuriser les entrées."
```

### Vérification de revue

- revue effectuée par un pair sur le diff fonctionnel,
- commentaires formulés sur les risques, la clarté et les tests,
- validation des améliorations avant fusion.

## Résultat attendu

Une PR ne doit pas être validée si l’un des critères critiques suivants est non conforme :

- sécurité des accès ou des entrées,
- absence de test sur un comportement modifié,
- logique métier obscure ou non maintenable,
- documentation insuffisante pour l’évolution du système.

## Critères de validation finale

Avant fusion, la revue doit confirmer :

- [ ] code lisible,
- [ ] typage PHP cohérent,
- [ ] sécurité vérifiée,
- [ ] tests pertinents,
- [ ] documentation mise à jour si nécessaire.
