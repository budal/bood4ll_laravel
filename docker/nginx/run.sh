#!/bin/sh
set -e

php artisan migrate:fresh --seed

if [ "${1#-}" != "$1" ]; then
        set -- /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf "$@"
fi

exec "$@"