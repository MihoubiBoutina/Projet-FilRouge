# 🔐 Stratégie de Gestion des Clés & des Secrets (KMS & Secrets Manager)

> Module BC04-FM04 — Chapitre 3 : Gestion des clés et des secrets  
> Compétence C25 : Protéger les secrets en gérant le cycle de vie complet des clés (création, rotation, révocation).

---

## 1. Pourquoi la gestion des clés est critique

Un chiffrement ne vaut que ce que vaut la protection de sa clé. Dans l’architecture MyProf, laisser des mots de passe en clair dans le fichier `.env` ou sur un dépôt Git constitue la principale surface d’attaque.

### Le chiffrement par enveloppe

Les données volumineuses (par exemple les bases MySQL et MongoDB) sont chiffrées par une clé de données (DEK). Cette clé DEK est elle-même chiffrée par une clé maîtresse (KEK / CMK) conservée dans un service KMS.

```text
Données métier
      ↓
  clé DEK
      ↓
  chiffrement
      ↓
  clé KEK / CMK (KMS)
```

Cette stratégie permet :

- de protéger les données de manière centralisée,
- d’isoler les clés maîtresses du stockage des données,
- de faciliter la rotation sans reconstruire tout le système.

---

## 2. Matrice des secrets et stratégie de stockage MyProf

| Type de secret | Exemple sur MyProf | Solution de stockage retenue | Mode de rotation |
| --- | --- | --- | --- |
| Secrets d’infrastructure | mots de passe MySQL / MongoDB | variables d’environnement masquées, Vault ou Secrets Manager | automatique tous les 90 jours |
| Secrets Keycloak | jeton `client_secret` OIDC, realm admin | KMS / Vault Key-Value | manuelle ou programmée |
| Clés de chiffrement | clés privées JWT, clés d’API | KMS / Key Vault non extractible | automatique annuelle |
| Secrets applicatifs | tokens de service, clés SMTP | gestion centralisée de secrets | rotation planifiée |

### Décision de conception pour MyProf

- les éléments sensibles ne doivent jamais être stockés en clair dans le dépôt Git ;
- les variables d’environnement doivent être injectées via un mécanisme sécurisé ;
- les comptes de service doivent utiliser des identités dédiées et non des comptes humains ;
- les secrets critiques doivent être centralisés dans un gestionnaire de secrets compatible avec le cycle de vie KMS.

---

## 3. Cycle de vie des clés et procédure de rotation

### Étapes d’exécution de la rotation CLI (AWS KMS / LocalStack)

```bash
# 1. Création d'une clé maîtresse KMS dédiée au projet MyProf
aws kms create-key \
  --description "myprof-database-master-key" \
  --key-usage ENCRYPT_DECRYPT

# 2. Association de l'alias applicatif
aws kms create-alias \
  --alias-name alias/myprof-prod \
  --target-key-id <KEY_ID>

# 3. Activation de la rotation automatique annuelle
aws kms enable-key-rotation --key-id <KEY_ID>
```

### Recommandations MyProf

- ne pas réutiliser une clé générée pour plusieurs finalités ;
- faire correspondre chaque clé à un périmètre : MySQL, MongoDB, JWT, Keycloak ;
- automatiser la rotation des clés si possible ;
- conserver une traçabilité des versions de clé et des opérations effectuées.

### Rotation des secrets applicatifs

- rotation du secret Keycloak client ou service account,
- rotation des mots de passe MySQL / MongoDB dans l’environnement concerné,
- rechargement des secrets sans redémarrage complet si le service le permet,
- validation post-rotation avec logs et tests de connexion.

---

## 4. Procédure de révocation d’urgence (clé / secret compromis)

Si une clé ou un jeton de connexion est poussé par erreur sur un dépôt public :

### 1. Désactiver la clé (Disable)

L’arrêt immédiat de l’utilisation sans suppression définitive permet de couper l’accès très rapidement, sans perdre la possibilité de revenir en arrière en cas de fausse alerte.

```bash
aws kms disable-key --key-id <KEY_ID_COMPROMISE>
```

### 2. Rechiffrer les données

Migration vers une nouvelle clé maîtresse. Les données existantes sont re-chiffrées avec la nouvelle clé valide.

```bash
aws kms re-encrypt \
  --ciphertext-blob fileb://data.enc \
  --destination-key-id <NOUVELLE_KEY_ID>
```

### 3. Programmer la suppression

Une période de grâce est obligatoire pour éviter la perte irréversible de données. Le délai standard est de 7 à 30 jours selon le contexte.

```bash
aws kms schedule-key-deletion \
  --key-id <KEY_ID_COMPROMISE> \
  --pending-window-in-days 7
```

### 4. Vérification de l’impact

- vérifier si un secret compromise a été exposé au dépôt,
- renouveler tous les secrets touchés,
- invalider les sessions JWT ou tokens associés,
- vérifier les journaux de l’environnement pour déceler d’éventuels usages malveillants.

---

## 5. Bonnes pratiques MyProf pour les secrets

### Règles à respecter

- pas de mot de passe en clair dans les fichiers de configuration ;
- pas d’utilisation de comptes administrateurs en production pour des tâches automatiques ;
- séparation des secrets selon les environnements (dev, test, prod) ;
- chiffrement des données au repos et au runtime ;
- journalisation des événements de création, rotation, révocation et usage.

### Recommandation d’implémentation

Pour MyProf, l’usage idéal est :

- KMS ou Vault pour les secrets de production,
- variables d’environnement et fichiers `env` maskés en local,
- rotation automatique pour les tensions légitimes,
- monitoring sur les usages suspects ou les échecs d’authentification.

---

## 6. Synthèse

La sécurisation des clés et des secrets est un axe critique de l’architecture MyProf. En séparant les clés de données et les clés maîtresses, en centralisant la gestion dans un KMS ou un secrets manager, et en appliquant une rotation régulière ainsi qu’une procédure de révocation rapide, l’équipe réduit fortement le risque de compromission et garantit la continuité de service.

Le principe fondamental est simple :

- les clés doivent être rares,
- les secrets doivent être protégés,
- la rotation doit être automatisée,
- la révocation doit être immédiate quand un secret est compromis.
