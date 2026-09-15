# Dépannage

## Problèmes fréquents

### Les dépendances Composer ne se installent pas

Vérifiez :

```bash
php -v
composer --version
composer install
```

Si l'installation échoue, nettoyez le cache Composer puis réessayez.

### Les conteneurs Docker ne démarrent pas

Contrôlez les logs :

```bash
docker compose ps
docker compose logs
```

Vérifiez que les ports requis sont libres : MySQL, MongoDB, et le port applicatif.

### Les tests PHPUnit échouent

Exécuter :

```bash
php bin/phpunit
```

Vérifiez les variables d'environnement liées aux bases de données et l'état des services.

## Bonnes pratiques

- garder les services Docker démarrés et sains,
- relancer la cache Symfony en cas d'anomalie de route ou de configuration,
- utiliser les logs et les messages d'erreur pour diagnostiquer rapidement,
- documenter les correctifs et les incidents majeurs.
