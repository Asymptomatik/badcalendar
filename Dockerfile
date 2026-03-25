FROM php:8.2-apache

# Dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Configuration OPcache pour la production
RUN { \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache-prod.ini

# Activer mod_rewrite et mod_headers pour Symfony et les en-têtes de sécurité
RUN a2enmod rewrite headers

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier les fichiers de dépendances en premier (optimisation du cache Docker)
COPY composer.json composer.lock symfony.lock ./

# Installer les dépendances PHP (sans dev)
RUN APP_ENV=prod composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Copier le reste de l'application
COPY . .

# Installer les dépendances (avec scripts cette fois)
RUN APP_ENV=prod composer run-script --no-dev post-install-cmd || true

# Compiler les assets Tailwind
RUN APP_ENV=prod php bin/console tailwind:build --minify

# Compiler le cache d'assets (Asset Mapper)
RUN APP_ENV=prod php bin/console asset-map:compile

# Vider et réchauffer le cache de production
RUN APP_ENV=prod php bin/console cache:warmup

# Permissions sur var/
RUN chown -R www-data:www-data var/ \
    && chmod -R 775 var/

# Configuration Apache pour Symfony (sans .htaccess)
RUN { \
    echo '<VirtualHost *:${PORT}>'; \
    echo '    DocumentRoot /var/www/html/public'; \
    echo '    DirectoryIndex index.php'; \
    echo '    <Directory /var/www/html/public>'; \
    echo '        AllowOverride None'; \
    echo '        Require all granted'; \
    echo '        FallbackResource /index.php'; \
    echo '    </Directory>'; \
    echo '    # En-têtes de sécurité HTTP'; \
    echo '    Header always set X-Frame-Options "SAMEORIGIN"'; \
    echo '    Header always set X-Content-Type-Options "nosniff"'; \
    echo '    Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
    echo '    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"'; \
    echo '    # HSTS : activer uniquement si HTTPS est garanti (Render le garantit)'; \
    echo '    # Ajouter includeSubDomains uniquement si TOUS les sous-domaines supportent HTTPS.'; \
    echo '    Header always set Strict-Transport-Security "max-age=31536000"'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
} > /etc/apache2/sites-available/symfony.conf \
    && a2ensite symfony.conf \
    && a2dissite 000-default.conf

# Masquer la version d'Apache (réduction de la surface d'attaque)
RUN { \
    echo 'ServerTokens Prod'; \
    echo 'ServerSignature Off'; \
} > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening

# Script de démarrage
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

EXPOSE 10000

CMD ["/usr/local/bin/startup.sh"]
