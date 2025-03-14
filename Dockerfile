# Imagen base de Laravel Sail
FROM laravelphp/sail:1.25.0

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Generar la clave de la aplicación
RUN php artisan key:generate

# Dar permisos a la carpeta de almacenamiento
RUN chmod -R 777 storage bootstrap/cache

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]

