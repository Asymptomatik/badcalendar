#!/bin/sh
set -e

# Render définit la variable PORT (par défaut 10000 pour les conteneurs Docker)
PORT="${PORT:-10000}"

echo "Démarrage de l'application sur le port $PORT..."

# Configurer Apache pour écouter sur le port défini par Render
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/\${PORT}/$PORT/g" /etc/apache2/sites-available/symfony.conf

# Exécuter les migrations Doctrine (échoue si la DB est inaccessible)
echo "Exécution des migrations..."
if ! APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration; then
    echo "ERREUR : Les migrations Doctrine ont échoué. Vérifiez DATABASE_URL." >&2
    exit 1
fi

# Démarrer Apache
echo "Démarrage d'Apache sur le port $PORT..."
exec apache2-foreground
