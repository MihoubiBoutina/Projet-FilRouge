# --- Étape 1 : Build (Composer) ---
FROM php:8.2-cli-alpine AS builder

# Installation des dépendances système nécessaires à Composer
RUN apk add --no-cache git unzip

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copie du code Symfony
COPY . .

# On désactive la limite de mémoire pour éviter l'exit code 4 (OOM)
ENV COMPOSER_MEMORY_LIMIT=-1

# Installation de vos dépendances PHP (sur une seule ligne pour éviter les erreurs de syntaxe)
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs --no-interaction --no-audit --no-progress -v

# --- Étape 2 : Production Runtime ---