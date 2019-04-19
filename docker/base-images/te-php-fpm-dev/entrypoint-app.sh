#!/bin/sh
set -e

if [ "$APP_D_FOR" == 'sf'  ]; then
    mkdir -p var/cache var/logs var/sessions
    composer install
    php bin/console cache:clear
#    chown -R www-data var

#    echo "Waiting for MySQL to be ready..."
#    until php bin/console doctrine:migrations:migrate; do
#        sleep 3
#    done
else
    echo "APP_D_FOR is NOOP"
fi

exec docker-php-entrypoint "$@"