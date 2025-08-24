FROM php:8.2-fpm-alpine

# 使用国内镜像源加速
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

# 一次性安装所有依赖和扩展
RUN apk add --no-cache \
    mysql-client \
    curl \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli

# 安装Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 暴露PHP-FPM端口
EXPOSE 9000

# 启动PHP-FPM
CMD ["php-fpm"]