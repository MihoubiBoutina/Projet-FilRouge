# 🔒 Protection des Données & Durcissement de l'Infrastructure

> **Module BC04-FM04 — Chapitre 5 : Sécurisation des données dans le Cloud**  
> **Compétence C25 :** Protéger l'infrastructure et les données contre les menaces identifiées en appliquant le chiffrement en transit et au repos.

---

## 1. Stratégie Globale de Chiffrement (Transit & Repos)

La donnée est ce qui reste quand l'infrastructure a été recréée. Sur MyProf, deux états de la donnée doivent impérativement être sécurisés :

| État de la donnée | Risque identifié | Protection appliquée sur MyProf | Norme / Standard |
| :--- | :--- | :--- | :--- |
| **En transit (In motion)** | Interception réseau / Attaque Man-in-the-Middle (MitM) sur les API ou Keycloak. | Chiffrement HTTPS obligatoire, **TLS 1.3** imposé sur le Reverse Proxy (Nginx/Caddy). | HSTS + TLS 1.2 minimum / 1.3 recommandé. |
| **Au repos (At rest)** | Vol de volume Docker, dump non chiffré de base de données MySQL / MongoDB. | Chiffrement natif des volumes de données via KMS / AES-256. | Chiffrement LUKS / AWS EBS Encryption / SSE-KMS. |

---

## 2. Configuration TLS 1.3 & Chiffrement en Transit

Toutes les connexions HTTP en clair sont explicitement rejetées. Le reverse proxy frontal gère la terminaison TLS avec une configuration renforcée.

### Configuration Reverse Proxy Nginx / Caddy (`docker/proxy/Caddyfile`)

```caddy
# Imposition de TLS 1.3 et redirection HTTP vers HTTPS automatique
myprof.local {
    tls internal {
        protocols tls1.3
    }

    # Strict Transport Security (HSTS) - Forcer le navigateur en HTTPS
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "DENY"
    }

    # Redirection vers l'API Symfony
    reverse_proxy api:8000
}
```

## 3. Durcissement du Stockage Objets (S3 / MinIO)

Pour le stockage des documents ou photos de profil sur S3 / MinIO, le verrouillage est appliqué directement à la création du bucket.

**1. Bloquer tout accès public :** Bloquer la visibilité publique au niveau du compte. Aucun fichier ne doit être accessible en lecture directe sans jeton d'accès ou URL signée.

Bash

```bash
aws s3control put-public-access-block \
  --account-id <ACCOUNT_ID> \
  --public-access-block-configuration \
    BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=true,RestrictPublicBuckets=true
```

**2. Activer le chiffrement par défaut (SSE-KMS) :** Utiliser la clé maîtresse KMS configurée au chapitre 3. Chaque fichier écrit dans le stockage est chiffré automatiquement au repos.

Bash

```bash
aws s3api put-bucket-encryption \
  --bucket myprof-user-data \
  --server-side-encryption-configuration '{
    "Rules": [{
      "ApplyServerSideEncryptionByDefault": {
        "SSEAlgorithm": "aws:kms",
        "KMSMasterKeyID": "alias/myprof-prod"
      }
    }]
  }'
```

**3. Imposer le transport HTTPS (TLS) :** Politique JSON interdisant les requêtes en clair. Un `Deny` explicite ferme la porte à toute connexion non chiffrée (`aws:SecureTransport: false`).

JSON

```json
{
  "Sid": "ForceHTTPSOnly",
  "Effect": "Deny",
  "Principal": "*",
  "Action": "s3:*",
  "Resource": "arn:aws:s3:::myprof-user-data/*",
  "Condition": {
    "Bool": { "aws:SecureTransport": "false" }
  }
}
```

**4. Activer le versionnement des données :** Protection contre la suppression accidentelle ou malveillante. Le versionnement permet de restaurer un fichier ou une base de données écrasée lors d'un incident.

Bash

```bash
aws s3api put-bucket-versioning \
  --bucket myprof-user-data \
  --versioning-configuration Status=Enabled
```

## 4. Durcissement des Conteneurs Docker (`docker-compose.yml`)

Pour éviter qu'une faille dans un conteneur (ex: API Symfony) ne compromette l'hôte ou la base de données, la configuration Docker Compose applique des règles de durcissement avancées :

YAML

```yaml
version: '3.8'

services:
  api:
    build: .
    user: "1000:1000"              # Ne JAMAIS exécuter les conteneurs en root
    read_only: true                # Système de fichiers en lecture seule (Hardening)
    tmpfs:
      - /tmp                       # Requis pour les fichiers temporaires
    security_opt:
      - no-new-privileges:true     # Interdit l'élévation de privilèges
    cap_drop:
      - ALL                        # Supprime toutes les capacités Linux inutiles
    environment:
      - APP_ENV=prod
      - DATABASE_URL=mysql://user:secret@db:3306/myprof

  db:
    image: mysql:8.0
    restart: always
    volumes:
      - mysql_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD_FILE=/run/secrets/db_root_password # Secret injecté hors .env
```

## 5. Matrice de Conformité du Chapitre 5 (Résumé)

- [x] **TLS 1.3 imposé :** Les requêtes HTTP non chiffrées renvoient une redirection HTTPS obligatoire (301).
- [x] **Secrets hors des images Docker :** Utilisation de Docker Secrets ou Vault au lieu de variables en clair.
- [x] **Conteneurs non-root :** Exécution sous un utilisateur dédié à faibles privilèges (`UID 1000`).
- [x] **Durcissement S3 :** Visibilité publique désactivée et chiffrement SSE-KMS activé par défaut.

---

## 6. Conclusion

La sécurisation de MyProf repose sur trois piliers : le chiffrement systématique, la réduction des droits et la minimisation des surfaces d'attaque. En appliquant TLS 1.3, le chiffrement des données au repos, le durcissement des conteneurs Docker et la protection du stockage objet, l'infrastructure répond aux exigences de confidentialité, intégrité et disponibilité attendues par le module de sécurité cloud.
