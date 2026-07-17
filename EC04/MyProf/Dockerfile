# --- Étape 1 : Préparation du build ---
FROM php:8.2-cli-alpine AS builder

WORKDIR /app

# On copie l'intégralité du code (y compris le dossier vendor généré en amont par GitHub)
COPY . .

# --- Étape 2 : Runtime de Production (Image finale légère) ---
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Installation des extensions PHP requises (y compris MongoDB de façon optimisée)
# On utilise l'outil officiel "install-php-extensions" compatible à 100% avec Alpine
ADD https://github.com/mlocati/docker-php-extensions/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    intl \
    opcache \
    pdo_mysql \
    zip \
    mongodb

# On copie l'application optimisée depuis l'étape de préparation
COPY --from=builder /app /var/www/html

# Création du dossier var pour que Symfony puisse y écrire son cache et ses logs
RUN mkdir -p /var/www/html/var

# Configuration des permissions pour l'utilisateur non-root d'Alpine (www-data)
RUN chown -R www-data:www-data /var/www/html/var

# Exposition du port classique de PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]