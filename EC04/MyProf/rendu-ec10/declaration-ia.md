# Déclaration d'utilisation de l'intelligence artificielle

## Outil et plateforme

GitHub Copilot a été utilisé dans VS Code comme assistant de rédaction et d'analyse. L'auteur reste responsable des choix, des vérifications et du contenu remis.

## Périmètre d'utilisation

L'assistance a porté sur :

- la structuration du guide à partir du sujet EC10 ;
- la reformulation de procédures et de critères vérifiables ;
- l'identification de causes possibles dans les échecs PHPUnit ;
- la proposition de corrections de workflow GitHub Actions ;
- la création de gabarits documentaires et d'un schéma de contribution.

Aucun secret, token ou donnée personnelle n'a été fourni dans les prompts.

## Démarche de contextualisation

Les demandes ont été accompagnées du contexte réel : Symfony 7.4, PHP 8.2, PHPUnit, chemins du projet, messages CI et contraintes de l'épreuve. Les réponses ont été limitées aux outils déjà présents dans le dépôt.

## Audit humain

Chaque proposition a été confrontée aux fichiers réels et à une commande de validation :

- les noms de fichiers et les routes ont été recherchés dans le dépôt ;
- les exemples de tests ont été comparés aux tests existants ;
- `mkdocs build --strict` a été utilisé pour la documentation ;
- `vendor/bin/phpunit` a été exécuté pour les comportements concernés ;
- les workflows ont été vérifiés dans GitHub Actions.

Les propositions ont été comparées au code avant d'être intégrées. L'exception métier dédiée `AtelierCompletException` a ensuite été implémentée dans `src/Exception`, utilisée par `Atelier` et mappée en HTTP 409 par le subscriber API, puis vérifiée par le test d'intégration de capacité.

## Responsabilité

L'IA a accéléré la recherche et la rédaction ; elle n'a pas remplacé la lecture du code, la revue par un pair, les tests ni la décision finale.
