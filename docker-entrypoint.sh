#!/bin/sh
# 启动 PHP-FPM
php-fpm &
# 启动 Nginx
nginx -g "daemon off;"
