# Fiche de cas de test renseignée

## Cas TC-ATELIER-001

| Champ            | Valeur                                                                     |
| ---------------- | -------------------------------------------------------------------------- |
| Fonctionnalité   | Inscription à un atelier à capacité limitée                                |
| Précondition     | Atelier créé avec `place = 1`                                              |
| Données          | Un premier apprenant inscrit, un second apprenant disponible               |
| Étape 1          | Ajouter la première inscription                                            |
| Étape 2          | Vérifier `countInscriptions() = 1`                                         |
| Étape 3          | Tenter une seconde inscription                                             |
| Résultat attendu | `canAcceptNewInscription()` retourne `false` et `LogicException` est levée |
| Fichier          | `tests/Integration/AtelierInscriptionCapacityIntegrationTest.php`          |
| Résultat         | Réussi                                                                     |

Ce cas protège une règle métier et non une simple ligne d'implémentation.
