# 🚨 Surveillance, Détection et Plan de Réponse à Incident

> Module BC04-FM04 — Chapitre 4 : Surveillance, détection et réponse aux incidents  
> Compétence C25 : Auditer régulièrement l’infrastructure et exécuter un plan de réponse à incident formalisé.

---

## 1. Stack de surveillance et détection MyProf

On ne défend que ce que l’on voit. L’architecture MyProf s’appuie sur quatre piliers d’observabilité pour détecter les menaces et anomalies d’accès :

| Question clé                         | Type de journal / outil                                     | Service utilisé sur MyProf                     | Objectif de sécurité                                                     |
| ------------------------------------ | ----------------------------------------------------------- | ---------------------------------------------- | ------------------------------------------------------------------------ |
| Qui a fait quoi ?                    | Journal d’audit des API (Keycloak Audit Events, CloudTrail) | Keycloak + logs API Symfony                    | Tracer toutes les connexions et actions d’administration                 |
| Comment va le système ?              | Métriques et alarmes (Prometheus / CloudWatch)              | Docker Healthchecks + métriques applicatives   | Détecter les pics anormaux de requêtes, brute force, panne ou saturation |
| Y a-t-il une menace active ?         | Détection d’anomalies (SIEM / GuardDuty)                    | Loki, Promtail ou autre centralisation de logs | Alerter sur accès depuis IP suspecte ou escades de privilèges            |
| Mes données sont-elles compromises ? | Audit des bases de données                                  | MySQL General Log + MongoDB access logs        | Tracer les requêtes d’extraction massive ou anomalies de lecture         |

### Rôle des journaux dans MyProf

- les accès Keycloak sont centralisés pour l’authentification et l’autorisation ;
- les appels API Symfony sont traçables pour détecter les accès non conformes ;
- les bases MySQL et MongoDB fournissent des preuves en cas d’exfiltration de données ou d’activité suspecte ;
- les conteneurs Docker sont surveillés pour détecter les comportements anormaux.

---

## 2. Principes de gestion et centralisation des journaux

Pour garantir la traçabilité en cas de compromission, les journaux respectent quatre règles de gestion strictes :

1. Centraliser  
   Tous les logs des conteneurs Docker (Keycloak, Symfony, MySQL, MongoDB) sont envoyés vers un compte d’audit dédié, isolé du réseau de production.

2. Rendre immuable  
   Les journaux sont écrits en mode append-only avec verrouillage WORM ou équivalent. L’attaquant ne doit pas pouvoir effacer ses traces même s’il prend le contrôle d’un conteneur.

3. Conserver dans la durée  
   La conservation minimale est de 12 mois. Un incident est souvent découvert plusieurs mois après l’intrusion initiale.

4. Restreindre la lecture  
   L’accès aux journaux est réservé exclusivement au rôle `ROLE_ADMIN` d’audit avec authentification MFA obligatoire.

### Exigence de sécurité

Les journaux doivent être :

- chronométrés de manière fiable,
- stockés hors du périmètre applicatif direct,
- protégés contre modification ou suppression non autorisée,
- retracés pour permettre la reconstruction d’un scénario d’attaque.

---

## 3. Déroulement d’une investigation forensique

Lorsqu’une alerte de sécurité est levée, le Security Champion suit une procédure d’investigation en 5 étapes :

### 1. Établir la chronologie

Créer le fil chronologique de l’incident.

Identifier :

- la première anomalie observée,
- l’extension des accès,
- la dernière action détectée,
- les événements Keycloak, API ou base concernés.

### 2. Identifier le principal

Déterminer quelle identité a exécuté les actions suspectes :

- utilisateur humain,
- compte de service,
- token JWT,
- adresse IP source.

### 3. Mesurer la portée

Identifier les ressources touchées :

- tables MySQL,
- collections MongoDB,
- endpoints API Symfony,
- fichiers ou configs critiques.

### 4. Conserver les preuves

Avant toute remédiation, isoler le conteneur ou le service compromis et réaliser un instantané des volumes de logs et de données.

### 5. Notifier les parties prenantes

Informer :

- la direction technique,
- les utilisateurs impactés,
- les acteurs concernés par la conformité,
- les autorités réglementaires si nécessaire.

---

## 4. Procédure réglementaire : notification CNIL sous 72h

En cas de violation de données à caractère personnel (par exemple fuite de données d’élèves ou de professeurs), l’article 33 du RGPD et le référentiel de sécurité imposent une notification légale.

```text
[Détection de la fuite de données]
               │
               ▼
   [Validation par l'équipe Sécurité]
               │
               ├──────────────────────────────────────────────┐
               ▼                                              ▼
  < 72 Heures maximum                            Si risque élevé pour les personnes
  Notification obligatoire à la CNIL              Notification aux utilisateurs impactés
  (Portail notifications.cnil.fr)                 (Email + consignes de sécurité)
```

### Éléments obligatoires à fournir à la CNIL sous 72h

- nature de la violation (accès non autorisé, exfiltration, destruction de données),
- catégories et nombre approximatif de personnes concernées,
- nom et coordonnées du Délégué à la Protection des Données (DPO) ou du responsable sécurité,
- conséquences probables de la fuite,
- mesures prises ou envisagées pour remédier et atténuer les effets.

### Cas MyProf

Un accès non autorisé à des données de profil ou à des évaluations d’élèves doit être traité comme un incident de sécurité à forte criticité, avec vérification immédiate et notification si nécessaire.

---

## 5. Plan de réponse à incident MyProf

### Phase 1 : Détection

- activation des alertes sur accès suspect,
- vérification des métriques sur les API Symfony,
- suivi des notifications Keycloak et des logs Docker,
- contrôle des erreurs répétées ou accès inhabituels.

### Phase 2 : Contention

- isolation du service concerné,
- blocage temporaire des comptes ou tokens compromis,
- désactivation des accès administrateur si nécessaire,
- arrêt des flux suspects vers les bases ou services externes.

### Phase 3 : Investigation

- analyse des traces,
- reconstruction de la chronologie,
- identification du principal et de la porte d’entrée,
- analyse de la portée de la menace.

### Phase 4 : Réparation

- rotation des secrets et clés concernées,
- correctif applicatif ou configuration,
- surveillance renforcée de la zone impactée,
- validation de la remédiation.

### Phase 5 : Rétroaction

- document de post-mortem,
- action corrective et plan de prévention,
- mise à jour des règles de sécurité et des procédures de revue.

---

## 6. Good practices de surveillance

- surveillance des échecs de connexion,
- alerte sur les changements sensibles de rôle ou permissions,
- audit régulier des accès admin,
- corrélation entre logs Keycloak, Docker, MySQL et MongoDB,
- vérification régulière du niveau de conservation des journaux.

---

## 7. Synthèse

La surveillance, la détection et la réponse à incident constituent la couche de résilience de MyProf. En centralisant les journaux, en traçant les accès administrateurs, et en appliquant un plan de réponse formalisé, l’équipe réduit le temps de détection, limite la propagation de l’incident et renforce la conformité aux exigences de sécurité et de notification.
