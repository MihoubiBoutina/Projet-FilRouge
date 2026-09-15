# Keycloak SSO — Erreur de redirection ou token invalide

## Symptôme

L’utilisateur est redirigé vers une page d’erreur lors de la connexion SSO, ou bien un token est refusé alors que le login a été validé.

## Diagnostic

Vérifier les points suivants :

1. Le service Keycloak est bien démarré.
2. Le port d’écoute est bien celui attendu : 31415.
3. Les variables d’environnement applicatives pointent vers la bonne URL de Keycloak.
4. Le client OAuth/OIDC configuré dans Keycloak correspond à l’application.
5. Le token reçu est bien expiré ou non signé selon le bon realm.

Exemples de vérification :

```bash
docker compose ps
docker compose logs keycloak
```

Puis vérifier les variables du projet :

```env
KEYCLOAK_URL=http://127.0.0.1:31415
KEYCLOAK_REALM=myprof
KEYCLOAK_CLIENT_ID=myprof-client
```

## Solution

- Vérifier que l’URL de redirection est bien conforme et correspond au client configuré.
- Vérifier la validité du realm et du client ID.
- Recharger le realm ou régénérer le client si la configuration a été modifiée.
- Vérifier la date d’expiration du token et les paramètres de durée de session.
- Redémarrer le conteneur Keycloak si nécessaire.

```bash
docker compose restart keycloak
```

Si le problème persiste, tester manuellement l’URL de connexion et le flux OAuth/OIDC depuis le navigateur pour identifier précisément l’étape qui échoue.
