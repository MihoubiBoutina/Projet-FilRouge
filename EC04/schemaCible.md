2. ANNEXE TECHNIQUE — Architecture Cible Haute Disponibilité 
Ce schéma présente l'organisation cible déployée sur le Cloud Souverain 
(Scénario 1 — IaaS/PaaS) avec répartition de charge, découpage multi-AZ et immuabilité S3. 




                              [ INTERNET / CLIENTS ]
                                           |
                                   ( HTTPS / TLS 1.3 )
                                           |
                                           v
                        +-------------------------------------+
                        |  Load Balancer Managé HA + WAF      |
                        |  (Terminaison TLS & Rate Limiting)  |
                        +------------------+------------------+
                                           |
                   +-----------------------+-----------------------+
                   |                                               |
                   v                                               v
        [ Zone de Disponibilité A ]                     [ Zone de Disponibilité B ]
+---------------------------------------+       +---------------------------------------+
|  Cluster API Symfony (Auto-scaling)   |       |  Cluster API Symfony (Auto-scaling)   |
|  • Instance 1        • Instance 2     |       |  • Instance 3        • Instance N     |
+------------------+--------------------+       +------------------+--------------------+
                   |                                               |
                   +-----------------------+-----------------------+
                                           |
        +----------------------------------+----------------------------------+
        |                                  |                                  |
        v                                  v                                  v
+-----------------------+      +-----------------------+      +-----------------------+
|  MySQL HA Managé      |      |  MongoDB Cluster HA   |      |  Keycloak SSO Cluster |
|  (Nœud Primary Multi-AZ)|    |  (Replica Set Data)   |      |  (OIDC / Auth HA)     |
+-----------+-----------+      +-----------------------+      +-----------------------+
            |
            v (Replication Synchrone)
+-----------------------+
|  MySQL Secondary      |
|  (Failover Automatique)|
+-----------------------+

=========================================================================================
                          SERVICE DE SAUVEGARDE ET SECRETS
  +-------------------------------------+     +-------------------------------------+
  | Vault (HashiCorp)                   |     | Stockage Objet S3 Immuable          |
  | Dynamic Secrets & Encryption Keys   |     | Policy WORM (Object Lock 30 jours)  |
  +-------------------------------------+     +-------------------------------------+
=====================================================================================