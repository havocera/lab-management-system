FROM php:8.2-fpm-alpine

# 安装系统依赖
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli

# 安装Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 设置工作目录
WORKDIR /var/www/html

# 复制后端代码
COPY . ./

# 安装PHP依赖
RUN composer install --no-dev --optimize-autoloader

# 复制配置文件
COPY deploy/php.ini /usr/local/etc/php/php.ini
COPY deploy/nginx-backend.conf /etc/nginx/nginx.conf
COPY deploy/supervisord.conf /etc/supervisor/supervisord.conf

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 创建日志目录
RUN mkdir -p /var/log/supervisor

# 暴露端口
EXPOSE 80

# 启动supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]