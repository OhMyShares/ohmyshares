#!/bin/sh
envsubst < /var/www/oms/frontend/public/assets/env.template.js > /var/www/oms/frontend/public/assets/env.js
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
