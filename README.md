# MeowWorld

Una red social diseñada específicamente para amantes de los gatos.

---

# Descripción del Proyecto

Este proyecto es una API desarrollada con Laravel que gestiona relaciones entre gatos y publicaciones en una red social. Los usuarios pueden seguir a otros, ver las publicaciones de gatos, y gestionar las relaciones entre gatos y publicaciones.

### Funcionalidades:
- **Autenticación de usuarios**: Los usuarios pueden autenticarse y manejar su sesión.
- **Relaciones de seguimiento**: Los usuarios pueden seguir y dejar de seguir a otros.
- **Relación gato-publicación**: Los gatos pueden ser asignados a publicaciones, y las publicaciones pueden tener múltiples gatos.
- **Acceso a gatos y publicaciones**: Se pueden obtener las publicaciones relacionadas con un gato y los gatos relacionados con una publicación.

---

# Instrucciones de Instalación y Uso

### Requisitos previos:
1. Tener instalado **Docker** y **Docker Compose** en tu sistema.
2. Tener instalado **Composer** para manejar las dependencias de Laravel.

### Pasos para instalar y ejecutar el proyecto:

1. **Clonar el repositorio del proyecto:**

   Abre tu terminal y clona el repositorio en tu máquina local.

   ```bash
   git clone <url-del-repositorio>
   cd <nombre-del-proyecto>
   ```

2. **Configurar las variables de entorno:**

   Copia el archivo `.env.example` a `.env`:

   ```bash
   cp .env.example .env
   ```

   Luego, abre el archivo `.env` y configura las variables para tu entorno, especialmente las de la base de datos:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=postgres
   DB_PORT=5432
   DB_DATABASE=nombre_de_base_de_datos
   DB_USERNAME=usuario
   DB_PASSWORD=contraseña
   ```

3. **Levantar los contenedores con Sail:**

   Si no tienes el contenedor de Laravel Sail instalado, corre el siguiente comando para instalarlo:

   ```bash
   ./vendor/bin/sail up
   ```

   Este comando iniciará todos los contenedores necesarios (Laravel, PostgreSQL, etc.) utilizando Docker.

   Si ya tienes el contenedor de Sail configurado, solo necesitas ejecutar:

   ```bash
   sail up -d
   ```

   Este comando iniciará los contenedores en segundo plano. Para detener los contenedores, puedes usar:

   ```bash
   sail down
   ```

4. **Componer las dependencias:**

   Una vez que los contenedores estén en funcionamiento, instala las dependencias de PHP dentro del contenedor de Sail ejecutando:

   ```bash
   sail composer install
   ```

5. **Generar la clave de la aplicación:**

   Ejecuta el siguiente comando para generar la clave de la aplicación:

   ```bash
   sail artisan key:generate
   ```

6. **Crear las tablas de la base de datos:**

   Ejecuta las migraciones para crear las tablas necesarias en la base de datos PostgreSQL:

   ```bash
   sail artisan migrate
   ```

7. **Poblar la base de datos (opcional):**

   Si necesitas poblar la base de datos con datos de prueba, puedes ejecutar:

   ```bash
   sail artisan db:seed
   ```

8. **Acceder a la aplicación:**

   Una vez que todo esté configurado, puedes acceder a la API en el navegador o utilizando herramientas como **Postman** o **Insomnia**. La aplicación estará disponible en:

   ```
   http://localhost
   ```

---

### Notas adicionales:
- Como recomponer vendor

   ```bash
   docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
   ```

--- 

## Enlace documentacion:
Documentacion generada con Swagger -> [enlace](https://meowworld-backend-production.up.railway.app/api/documentation)

## Servidor deplegado:
`meowworld-backend-production.up.railway.app`
