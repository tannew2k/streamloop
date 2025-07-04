/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
/usr/sbin/crond -f -l 8
/usr/sbin/php-fpm -F