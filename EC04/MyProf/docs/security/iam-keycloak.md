# 🛡️ Stratégie IAM & Gestion des Identités (Keycloak)

> Module BC04-FM04 — Chapitre 2 : Contrôles d’accès et gestion des identités dans le Cloud  
> Compétence C25 : Gérer les identités et les accès à l’aide d’outils IAM et appliquer le principe du moindre privilège.

---

## 1. Vue d’ensemble de la stack IAM MyProf

Dans l’architecture MyProf, la gestion des identités est centralisée via Keycloak (SSO). L’API Symfony et les services d’infrastructure s’appuient sur des jetons OAuth2 / OpenID Connect (OIDC) pour l’authentification et l’autorisation.

```text
┌─────────────────┐       1. Auth (MFA)      ┌──────────────────────────┐
│   Utilisateur   ├─────────────────────────►│ Keycloak SSO (Port 31415)│
└────────┬────────┘                          └────────────┬─────────────┘
         │                                                │
         │ 2. Requête + JWT Bearer                        │ 3. Vérification public key
         ▼                                                ▼
┌───────────────────────────────────────────────────────────────────────┐
│                           API Symfony (Backend)                       │
│     Vérification des rôles et scopes selon le principe du moindre    │
│                          privilège et du contexte                     │
└───────────────────────────────────────────────────────────────────────┘
```

### Composants impliqués

- Keycloak : fournisseur d’identité centralisé et gestion SSO
- Symfony : validation des jetons, contrôle d’accès et gestion des rôles applicatifs
- Docker Compose : orchestration locale des services, y compris Keycloak
- MySQL / MongoDB : stockage des données applicatives, sans gestion des identités côté base

### Objectif

Assurer que :

- chaque principal est authentifié de manière fiable,
- chaque action est autorisée uniquement avec les droits nécessaires,
- le contexte métier est pris en compte,
- les comptes de service et les comptes humains ne sont pas sur-délégués.

---

## 2. Modèle d’accès au moindre privilège (RBAC)

Conformément au principe du moindre privilège, chaque utilisateur ou service ne dispose que des droits strictement nécessaires à l’accomplissement de ses tâches.

### Matrice des rôles et autorisations

| Rôle Keycloak        | Périmètre / contexte métier | Droits accordés (API Symfony)                                                    | Conditions et limitations                                                       |
| -------------------- | --------------------------- | -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| ROLE_STUDENT         | Élèves de la plateforme     | Lecture de profils publics, soumission d’avis, consultation de son propre profil | Uniquement ses propres données ; modification limitée aux ressources autorisées |
| ROLE_PROF            | Enseignants inscrits        | Consultation de son profil, gestion de son espace, lecture des avis reçus        | Accès limité au périmètre de son activité professionnelle                       |
| ROLE_ADMIN           | Administrateurs MyProf      | Gestion des utilisateurs, suppression de contenus critiques, audit               | Nécessite un compte nominatif dédié et MFA obligatoire                          |
| ROLE_SERVICE_TRAFFIC | Compte de service technique | Vérification de santé et métriques inter-services                                | Jeton client_credentials uniquement, accès minimisé                             |

### Principes appliqués

- séparation des responsabilités, par rôle et par périmètre métier ;
- alignement sur les besoins fonctionnels de MyProf ;
- pas d’accès administratif partagé entre comptes utilisateurs classiques ;
- comptes de service distincts des comptes humains.

### Cas métier MyProf

- un apprenant ne peut pas modifier le profil d’un autre utilisateur ;
- un enseignant ne peut pas supprimer un avis d’un autre formateur ;
- un administrateur ne peut agir qu’avec un compte dédié et vérifiable ;
- les services techniques ne récupèrent que les droits de surveillance ou d’intégrité nécessaires.

---

## 3. Configuration des politiques d’accès JSON (Keycloak / Symfony)

Chaque décision d’accès repose sur le triplet : principal + action + contexte.

### Exemple de politique de sécurité d’évaluation des avis

```json
{
    "Version": "2026-09-14",
    "Statement": [
        {
            "Sid": "AllowStudentReadProfsAndPostReviews",
            "Effect": "Allow",
            "Principal": { "Role": "ROLE_STUDENT" },
            "Action": ["prof:list", "review:create"],
            "Resource": "arn:myprof:api:v1:*"
        },
        {
            "Sid": "AllowAdminDeleteReviewWithMFA",
            "Effect": "Allow",
            "Principal": { "Role": "ROLE_ADMIN" },
            "Action": ["review:delete", "user:suspend"],
            "Resource": "arn:myprof:api:v1:admin/*",
            "Condition": {
                "Bool": { "auth:MultiFactorAuthPresent": "true" }
            }
        },
        {
            "Sid": "DenyDirectDatabaseAccess",
            "Effect": "Deny",
            "Principal": "*",
            "Action": "db:*",
            "Resource": "arn:myprof:db:production",
            "Condition": {
                "StringNotEquals": { "aws:PrincipalType": "ServiceRole" }
            }
        }
    ]
}
```

### Adaptation à MyProf

Dans le contexte de Symfony + Keycloak, cette logique se traduit par :

- rôle détecté depuis le JWT Keycloak,
- validation du scope ou du rôle dans le contrôleur / service,
- vérification du contexte (session utilisateur, ressource demandée, action métier),
- refus par défaut si le rôle ou le scope n’est pas présent.

Exemple de logique conceptuelle :

```php
if (!$token->hasRole('ROLE_ADMIN') || !$token->hasClaim('amr', 'mfa')) {
    throw new AccessDeniedException('Accès refusé : MFA requis pour cette action.');
}
```

---

## 4. RBAC vs ABAC

### RBAC (Role-Based Access Control)

Les autorisations sont attribuées à des rôles, puis affectées aux utilisateurs.

Avantages :

- simplicité de mise en œuvre,
- compréhension facile pour l’équipe,
- cohérence avec les besoins fonctionnels de MyProf.

Limites :

- moins précis si une décision dépend d’un contexte particulier.

### ABAC (Attribute-Based Access Control)

Les autorisations dépendent d’attributs tels que :

- rôle utilisateur,
- identité du propriétaire,
- contexte d’authentification,
- présence de MFA,
- domaine ou environnement.

### Cas d’usage MyProf

Un apprenant ne peut modifier que ses propres avis ;

```text
Rôle = ROLE_STUDENT
Objet = avis
Propriétaire = utilisateur courant
Condition = user_id == owner_id
```

Un administrateur peut supprimer un avis uniquement si :

```text
Rôle = ROLE_ADMIN
MFA = true
Action = delete review
```

La meilleure stratégie pour MyProf est une combinaison :

- RBAC pour les rôles métiers,
- ABAC pour les contrôles contextuels (propriétaire, MFA, action, environnement).

---

## 5. MFA et protection des comptes sensibles

### Obligation MFA dans le realm `myprof-realm`

Le MFA est requis pour le rôle `ROLE_ADMIN` dans le realm Keycloak `myprof-realm`.

Concrètement :

- la connexion du compte administrateur est impossible sans validation d’une clé OTP,
- les méthodes acceptées sont des générateurs de codes à usage unique, notamment Google Authenticator, FreeOTP ou YubiKey,
- cette exigence est appliquée à chaque tentative d’authentification du rôle administratif.

### Bonnes pratiques d’implémentation

- authentification forte sur l’admin console Keycloak,
- MFA par application authentifiante ou OTP,
- surveillance des échecs d’authentification,
- logs d’accès centralisés dans des services d’audit,
- blocage ou alerte si un compte admin ne vérifie pas la seconde étape d’authentification.

### Règle de sécurité MyProf

Un administrateur ne peut pas supprimer un avis, suspendre un compte ou accéder à une action critique sans validation MFA.

---

## 5.1 Politique de mot de passe forte

Pour les comptes administrateurs du realm `myprof-realm`, la politique de mot de passe est renforcée :

- longueur minimale : 12 caractères,
- complexité : au moins une majuscule, un chiffre et un caractère spécial,
- expiration : 90 jours,
- historique : blocage de la réutilisation d’anciens mots de passe si nécessaire,
- refus des mots de passe trop faibles ou trop courants.

Exemple de politique appliquée :

```text
Longueur minimale : 12
Obligatoire : majuscule, chiffre, caractère spécial
Expiration : 90 jours
Rôle cible : ROLE_ADMIN
```

Cette règle réduit le risque de mot de passe compromis, en particulier pour les comptes ayant des droits sensibles.

---

## 5.2 Gestion des sessions JWT

Les sessions JWT émises par Keycloak sont configurées avec des durées courtes afin de limiter la fenêtre d’attaque en cas de fuite du jeton.

### Paramètres appliqués

- Access Token Lifespan : 15 minutes
- Refresh Token Lifespan : 8 heures
- Rotation automatique du refresh token : activée

### Justification

- un access token court réduit la durée de validité d’un jeton compromis,
- la rotation de refresh token limite le risque de réutilisation du token après fuite,
- la combinaison de ces règles renforce la résilience du système face au vol de jetons ou à la session hijacking.

### Exemple de politique JWT

```text
Access Token Lifespan: 15 minutes
Refresh Token Lifespan: 8 heures
Rotation automatique: oui

Rôle cible: ROLE_ADMIN, ROLE_PROF, ROLE_STUDENT selon contexte
```

---

## 6. Vérification et tests de conformité IAM

Afin de valider la bonne application des règles du chapitre 2, le référentiel impose les contrôles suivants :

- [x] Aucun identifiant passe-partout / générique : les comptes de test `admin@myprof.local` doivent être remplacés par des comptes nominatifs.
- [x] MFA actif : la tentative de connexion d’un administrateur sans MFA doit renvoyer un code d’erreur Keycloak `401 Unauthorized` ou `MFA Required`.
- [x] Rôles API isolés : un jeton `ROLE_STUDENT` appelant `DELETE /api/reviews/123` doit être refusé avec un statut HTTP `403 Forbidden`.

### Contrôles de conformité à exécuter

#### 1. Comptes nominaux uniquement

Chaque compte admin ou technique doit être associé à un individu identifié et tracé.

Vérification recommandée :

```text
- pas de compte générique ou type shared account
- pas d’email de type admin@myprof.local
- chaque accès admin est attribué à une personne nominative
```

#### 2. Test MFA pour l’administrateur

Scénario de validation :

```text
1. Création d’un compte administrateur dans le realm myprof-realm
2. Désactivation ou absence de configuration OTP
3. Tentative de connexion
4. Vérification du statut HTTP ou du message Keycloak
Résultat attendu: 401 Unauthorized / MFA Required
```

#### 3. Test d’isolation des rôles API

Scénario de validation :

```text
1. Générer un access token avec le rôle ROLE_STUDENT
2. Appeler DELETE /api/reviews/123
3. Vérifier le refus côté API
Résultat attendu: 403 Forbidden
```

### Règle opérationnelle

Tout écart observé sur l’un de ces trois tests doit être traité comme un incident de sécurité et corriger avant mise en production.

---

## 5.3 Procédure de départ d’un collaborateur / administrateur

En cas de départ d’un membre de l’équipe technique ou de révocation des accès administrateur, le workflow suivant doit être appliqué sans délai :

```text
[1. Désactiver le compte dans Keycloak] ──► [2. Invalider les sessions actives (Revoke Tokens)]
                                                    │
                                                    ▼
[4. Mettre à jour les clés SSH / Secrets] ◄── [3. Révoquer les accès CLI / Service Accounts]
```

### Règles de mise en œuvre

- désactiver immédiatement le compte utilisateur dans le realm `myprof-realm` ;
- révoquer les sessions actives pour empêcher toute utilisation de jetons déjà émis ;
- révoquer tous les accès CLI, comptes de service et tokens de maintenance ;
- mettre à jour les secrets, clés SSH et credentials associés ;
- documenter l’incident et la date exacte de clôture des accès.

### Script CLI d’urgence pour la désactivation immédiate (Keycloak Admin CLI)

```bash
# 1. Connexion au Keycloak Master CLI
./kcadm.sh config credentials \
  --server http://localhost:31415/auth \
  --realm master \
  --user admin

# 2. Désactivation du compte utilisateur
./kcadm.sh update users/USER_UUID -r myprof-realm -s enabled=false

# 3. Révocation de toutes les sessions actives de l'utilisateur
./kcadm.sh logout users/USER_UUID -r myprof-realm
```

### Procédure complémentaire recommandée

```text
- Vérifier l’état des rôles Keycloak
- Désactiver les accès à Docker Compose et au dépôt
- Révoquer les secrets de CI/CD liés au compte
- Contrôler les journaux d’authentification sur les 24 dernières heures
- Confirmer la fermeture des droits avant clôture du départ
```

Cette démarche est essentielle pour assurer la continuité de sécurité et éviter toute persistance de droits après départ d’un collaborateur ou révocation des accès administrateur.

---

## 5.3 Contrôle d’accès et MFA dans le flux MyProf

Dans le flux applicatif de MyProf :

1. l’utilisateur s’authentifie sur Keycloak,
2. le serveur vérifie les assertions OIDC du token,
3. le rôle est évalué (ROLE_ADMIN, ROLE_PROF, ROLE_STUDENT),
4. le système applique les droits selon le contexte,
5. les actions critiques demandent le MFA, notamment pour le rôle administrateur.

Exemple conceptuel :

```php
if ($token->hasRole('ROLE_ADMIN')) {
    if (!$token->hasClaim('amr', 'mfa')) {
        throw new AccessDeniedException('MFA requis pour l’accès administratif.');
    }
}
```

L’objectif est de coupler sécurité de l’authentification forte, moindre privilège et protection des jetons.

---

## 6. Principe du moindre privilège pour les services et conteneurs

### Contexte Docker Compose

Le projet MyProf s’exécute avec des services Docker :

- Symfony application,
- MySQL,
- MongoDB,
- Keycloak.

Chaque service doit avoir :

- des ports réduits au strict nécessaire,
- des identifiants distincts,
- des droits spécifiques et non globaux,
- une séparation nette entre service web et service données.

### Exemples de bonnes pratiques

- le service Symfony ne doit pas avoir de droit d’administration sur MySQL au-delà du besoin applicatif ;
- les services de lecture/écriture MySQL et MongoDB doivent être séparés dans la configuration ;
- Keycloak doit être isolé du réseau applicatif et accessible uniquement via les chemins de service attendus.

---

## 7. Gestion des départs et procédure de révocation

### Procédure de départ

Quand un membre part, le processus doit être standardisé :

1. Désactivation du compte Keycloak.
2. Révocation des sessions actives.
3. Vérification des droits applicatifs et des rôles associés.
4. Vérification des accès aux conteneurs, secrets et comptes de service.
5. Contrôle des accès aux dépôts, outils CI et bases de données.
6. Archivage des logs d’accès et des événements de sécurité.

### Exemple de procédure MyProf

```text
- Compte utilisateur supprimé ou désactivé dans Keycloak
- Accès de l’API Symfony immédiatement invalidé
- Sessions JWT invalidées
- Vérification des éventuels droits de maintenance sur Docker ou infrastructure
- Revue des accès sur l’admin Keycloak
```

### Règle importante

Aucun compte ne doit conserver des droits après la fin effective de l’engagement, même si le membre est temporairement inactif.

---

## 8. Sécurité opérationnelle et bonnes pratiques de gouvernance

### Recommandations MyProf

- créer un compte unique et nominatif pour chaque utilisateur,
- ne pas partager les comptes d’administration,
- isoler les roles applicatifs et les roles techniques,
- surveiller les logs d’authentification et de refus,
- sécuriser les secrets via variables d’environnement ou gestion de secrets,
- restreindre les permissions de base de données aux services concernés,
- appliquer le principe du “refus par défaut”.

### Matrice de contrôle

| Domaine          | Contrôle                       | Statut attendu |
| ---------------- | ------------------------------ | -------------- |
| Authentification | MFA sur comptes sensibles      | Obligatoire    |
| Autorisation     | Rôles métier limités           | Obligatoire    |
| Services         | Comptes de service distincts   | Obligatoire    |
| Révocation       | Procédure de départ documentée | Obligatoire    |
| Audit            | Logs et traces d’accès         | Obligatoire    |

---

## 9. Synthèse

La stratégie IAM de MyProf repose sur une centralisation de l’identité avec Keycloak, complétée par un modèle RBAC/ABAC basé sur le moindre privilège, l’usage de MFA pour les comptes sensibles, et une procédure stricte de départ. Cette organisation permet de sécuriser les accès à l’API Symfony, de limiter les risques de sur-autorisation et d’aligner la gestion des identités avec les exigences de sécurité du projet.

En pratique, le bon équilibre pour MyProf est :

- Keycloak pour l’identité et la fédération SSO,
- Symfony pour le contrôle applicatif des accès,
- Docker Compose pour le cloisonnement technique,
- RBAC + ABAC pour un contrôle précis et adapté au contexte,
- MFA et révocation immédiate pour les comptes sensibles.
