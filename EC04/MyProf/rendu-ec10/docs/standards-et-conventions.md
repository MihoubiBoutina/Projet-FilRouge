# 2. Standards et conventions

## Règles vérifiables

| Règle                                        | Vérification                                             | Exemple                                                          | Contre-exemple                                            |
| -------------------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------- | --------------------------------------------------------- |
| Une méthode expose ses types utiles          | Lire la signature PHP                                    | `public function canAcceptNewInscription(int $number = 1): bool` | méthode sans type de paramètre ni retour                  |
| Une règle métier porte un nom explicite      | Rechercher les exceptions et messages métier             | `AtelierCompletException`                                        | `LogicException` pour un cas métier                       |
| Une API valide son JSON avant la persistance | Tester payload vide, type invalide et identifiant absent | réponses 400/422/404 structurées                                 | persister des données non validées                        |
| Les secrets restent hors du code             | Rechercher les valeurs sensibles dans PHP/YAML           | `%env(MONGODB_URI)%`                                             | mot de passe écrit dans un contrôleur                     |
| Les commandes sont reproductibles            | Exécuter depuis la documentation                         | `vendor/bin/phpunit`                                             | résultat obtenu par une manipulation manuelle non décrite |

## Outillage réellement utilisé

- PHP 8.2 et Symfony 7.4 ;
- PHPUnit 11 ;
- Doctrine ORM pour MySQL/SQLite de test et ODM pour MongoDB ;
- Docker Compose ;
- GitHub Actions ;
- MkDocs Material.

## Exemple réel de configuration CI

```yaml
- name: Exécuter les tests PHPUnit
  run: vendor/bin/phpunit
```

Ce contrôle est bloquant : une PR ne doit pas être fusionnée si la commande échoue.
