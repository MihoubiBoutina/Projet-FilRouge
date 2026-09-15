# MongoDB — Échec de connexion au service de traçabilité des avis

## Symptôme

Les avis ou les logs de traçabilité ne sont pas enregistrés, et l’application signale une erreur de connexion au service MongoDB.

## Diagnostic

Vérifier :

1. Le conteneur MongoDB est bien démarré.
2. Les identifiants et la chaîne de connexion sont corrects.
3. La base cible existe bien.
4. Les services applicatifs utilisent la bonne URL de connexion.

Commandes utiles :

```bash
docker compose ps
docker compose logs mongodb
php bin/console debug:config doctrine_mongodb
```

Vérifier la configuration :

```env
MONGODB_URL="mongodb://root:root@mongodb:27017/myprof?authSource=admin"
```

## Solution

- Vérifier que MongoDB est fonctionnel et accessible sur le port 27017.
- Corriger la chaîne de connexion si elle ne correspond pas au service local ou au conteneur.
- Créer ou vérifier la base utilisée par l’application.
- Redémarrer le service si nécessaire :

```bash
docker compose restart mongodb
```

- Vérifier l’état des collections liées aux avis et à la traçabilité :

```bash
mongosh --eval "db.adminCommand('listDatabases')"
```

Si la connexion continue d’échouer, vérifier le réseau Docker, les variables d’environnement et les droits d’accès au service MongoDB.
