# 8. Amélioration continue

## Indicateurs

- taux de PR fusionnées avec CI verte au premier passage ;
- nombre de défauts détectés après fusion ;
- couverture PHPUnit et couverture des règles métier critiques ;
- délai moyen de mise à jour d'une fiche après changement technique.

## Rituels

Après une revue importante, l'équipe utilise une rétrospective 4L : aimé, appris, manqué, souhaité. Une cause racine est documentée pour un défaut récurrent ou à fort impact.

## Plan d'action priorisé

| Priorité | Action                                         | Responsable              | Échéance        | Critère de réussite                    |
| -------- | ---------------------------------------------- | ------------------------ | --------------- | -------------------------------------- |
| P1       | Ajouter un test à chaque nouvelle règle métier | Auteur de la PR          | Chaque PR       | cas nominal, limite et erreur présents |
| P1       | Maintenir le seuil de couverture à 70 %        | Mainteneur CI            | À chaque fusion | gate CI vert                           |
| P2       | Revoir les fiches de dépannage                 | Reviewer désigné         | Mensuel         | quatre incidents vérifiés              |
| P2       | Vérifier les liens et le build documentaire    | Mainteneur documentation | Mensuel         | `mkdocs build --strict` sans warning   |
| P3       | Rejouer la checklist de revue                  | Toute l'équipe           | Trimestriel     | checklist validée sur une PR           |

## Boucle de suivi

Chaque action est discutée en rétrospective, associée à une preuve et clôturée uniquement lorsque le critère de réussite est vérifié.
