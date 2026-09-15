# Doctrine / Migration — Base MySQL désynchronisée lors du déploiement

## Symptôme

L’application démarre partiellement ou affiche des erreurs de base de données, notamment des tables manquantes, des colonnes absentes, ou des migrations non appliquées.

## Diagnostic

Vérifier if :

1. La base MySQL n’a pas été créée ou est vide.
2. Les migrations Doctrine n’ont pas été appliquées après un déploiement.
3. Un changement de schéma n’a pas été propagé correctement entre environnements.
4. La configuration de connexion pointe vers la bonne base.

Commandes de contrôle :

```bash
php bin/console doctrine:database:status
php bin/console doctrine:migrations:status
php bin/console doctrine:schema:validate
```

## Solution

Appliquer les migrations de manière explicite :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```

Si l’environnement est fortement désynchronisé, vérifier la base cible puis réappliquer l’état attendu :

```bash
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:migrate -n
```

Dans le cas où le schéma a été modifié manuellement, valider les différences avant un déploiement et corriger la migration correspondante.

## Prévention

- toujours valider les migrations avant mise en production,
- ne pas modifier directement le schéma sans migration,
- vérifier le statut dans chaque environnement avant le déploiement,
- réaliser des tests de cohérence sur les données critiques.
