# Usar PHP 8.1 con FPM
FROM php:8.1-fpm

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath zip

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copiar los archivos del proyecto
COPY . .

# Instalar dependencias de PHP con optimización para producción
RUN composer install --no-dev --optimize-autoloader

# Generar clave de la aplicación
RUN php artisan key:generate

# Dar permisos a las carpetas necesarias
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Exponer el puerto 9000 (para FPM)
EXPOSE 9000

# Comando para ejecutar PHP-FPM
CMD ["php-fpm"]
