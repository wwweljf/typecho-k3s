FROM php:8.1-fpm-alpine
WORKDIR /var/www/html
# 安装 PDO MySQL 扩展与 Nginx
RUN docker-php-ext-install pdo_mysql \
    && apk add --no-cache nginx
# 复制源代码
COPY . .
# 设置权限
RUN chown -R www-data:www-data .
# 拷贝配置与启动脚本
COPY nginx.conf /etc/nginx/nginx.conf
COPY docker-entrypoint.sh /usr/local/bin/
# 设置入口
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
