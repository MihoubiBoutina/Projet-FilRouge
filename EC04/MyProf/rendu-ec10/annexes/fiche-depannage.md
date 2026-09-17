# Fiche de dépannage

## 1. Port Docker déjà utilisé

**Symptôme :** un conteneur ne démarre pas car le port est occupé.

**Diagnostic :** `docker compose ps` puis identifier le processus qui utilise le port.

**Correction :** arrêter le service concurrent ou modifier le port dans la configuration locale, puis relancer `docker compose up -d`.

## 2. Migration Doctrine impossible

**Symptôme :** la migration échoue ou le schéma est absent.

**Diagnostic :** vérifier `DATABASE_URL`, puis `php bin/console doctrine:migrations:status`.

**Correction :** démarrer la base, corriger la variable d'environnement et relancer la migration.

## 3. Connexion Keycloak refusée

**Symptôme :** la redirection SSO échoue.

**Diagnostic :** vérifier le conteneur Keycloak, l'URL et les paramètres client.

**Correction :** redémarrer le service et contrôler les variables sans exposer de secret.

## 4. MongoDB indisponible

**Symptôme :** les avis ou la traçabilité échouent.

**Diagnostic :** `mongosh --eval "db.runCommand({ ping: 1 })"`.

**Correction :** démarrer MongoDB et vérifier `MONGODB_URI` et `MONGODB_DB`.
