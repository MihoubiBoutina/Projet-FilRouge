. ANNEXE TECHNIQUE — Architecture Existante MyProf 
Ce document cartographie l'état initial de la maquette MyProf sur hôte unique avant migration.   


                    +-------------------------------------------------+
                     |               HÔTE DOCKER UNIQUE                |
                     |                                                 |
                     |  +-------------------------------------------+  |
                     |  |         Proxy / WAF (Nginx)               |  |
                     |  |         Port Externe: 8000/443            |  |
                     |  +---------------------+---------------------+  |
                     |                        |                        |
                     |              [prof-sr1-frontend]                |
                     |                        |                        |
                     |  +---------------------+---------------------+  |
                     |  |       API Backend Symfony (FPM)           |  |
                     |  |       user: 1000 | read_only: true        |  |
                     |  +----------+---------------------+----------+  |
                     |             |                     |             |
            [profs-sr-backend]     |                     |             |
                   |               |                     |             |
 +-----------------+---+  [profs-sr-db-sql]    [profs-sr-db-nosql]   |
 | Keycloak SSO        |           |                     |             |
 | Port Interne: 31415 |  +--------+--------+   +--------+--------+    |
 +---------------------+  | MySQL 8.0       |   | MongoDB 6.0     |    |
                          | Port: 3306      |   | Port: 27017     |    |
                          +-----------------+   +-----------------+    |
                     +-------------------------------------------------+