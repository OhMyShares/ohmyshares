#!/bin/sh
set -e
envsubst < /var/www/oms/frontend/public/assets/env.template.js > /var/www/oms/frontend/public/assets/env.js
php /var/www/oms/artisan migrate --force --seed
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
