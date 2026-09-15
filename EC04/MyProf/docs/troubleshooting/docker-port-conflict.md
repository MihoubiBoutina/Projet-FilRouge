# Docker — Conflit de ports ou conteneur MySQL/MongoDB qui ne démarre pas

## Symptôme

Le projet ne démarre pas correctement : conteneur MySQL ou MongoDB en échec, message de port déjà utilisé, ou service inaccessible.

## Diagnostic

Vérifier les points suivants :

1. Les ports du projet ne sont pas déjà occupés par d’autres services.
2. Le fichier de configuration Docker est correctement défini.
3. Les volumes ou données précédentes ne bloquent pas le démarrage.
4. Les logs du conteneur montrent une erreur de démarrage ou de configuration.

Commandes utiles :

```bash
docker compose ps
docker compose logs mysql
docker compose logs mongodb
```

Vérifier aussi les ports :

```bash
netstat -ano | findstr :3306
netstat -ano | findstr :27017
```

## Solution

- Identifier et libérer le service qui occupe le port.
- Relancer les conteneurs après vérification :

```bash
docker compose down
Docker compose up -d --build
```

- Si un conteneur est bloqué par des données locales, supprimer uniquement le volume concerné après sauvegarde :

```bash
docker volume ls
docker volume rm <nom_du_volume>
```

- Vérifier la configuration des ports dans les fichiers Docker Compose :

```yaml
ports:
    - "3306:3306"
    - "27017:27017"
```

En cas de conflit persistant, vérifier les services système déjà en cours et arrêter ceux qui utilisent des ports réservés.
