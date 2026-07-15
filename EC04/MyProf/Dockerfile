# --- Étape 1 : Build (Composer & Assets) ---
FROM composer:2.7 AS builder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-scripts

# --- Étape 2 : Production Runtime ---
FROM php:8.2-alpine
WORKDIR /var/www/html

# Extensions PHP nécessaires pour Symfony
RUN apk add --no-cache icu-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql

# Récupération du build propre
COPY --from=builder /app /var/www/html

# Durcissement sécurité : Utilisateur non-root obligatoire
RUN chown -R 1000:1000 /var/www/html
USER 1000:1000

EXPOSE 8000
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD nc -z localhost 8000 || exit 1
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]