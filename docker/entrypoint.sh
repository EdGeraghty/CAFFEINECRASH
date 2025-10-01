#!/bin/bash

# Ensure mounted volume directories exist and have proper permissions
mkdir -p /var/www/html/data/database
mkdir -p /var/www/html/data/sessions
chown -R www-data:www-data /var/www/html/data
chmod -R 777 /var/www/html/data

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
