# Sistema de Gestión de Órdenes - PDVSA

Este proyecto es una aplicación Laravel para la gestión de órdenes, con roles de administrador, planificador, supervisor y técnico.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- Servidor local en Windows (por ejemplo: XAMPP, WAMP, Laragon)
- Base de datos:
  - SQLite (recomendado para instalación rápida)
  - MySQL/MariaDB si prefieres una base de datos tradicional

## Instalación

1. Clonar o copiar el proyecto:

   ```bash
   git clone <url-del-repositorio>
   cd S_WILLIAM_M
   ```

2. Instalar dependencias de PHP:

   ```bash
   composer install
   ```

3. Instalar dependencias de Node.js:

   ```bash
   npm install
   ```

4. Crear el archivo de entorno:

   ```bash
   copy .env.example .env
   ```

5. Generar la clave de aplicación:

   ```bash
   php artisan key:generate
   ```

## Configurar la base de datos

### Opción 1: SQLite (más simple)

1. Crear el archivo SQLite:

   ```bash
   type nul > database\database.sqlite
   ```

2. Editar `.env` y establecer:

   ```ini
   DB_CONNECTION=sqlite
   DB_DATABASE="${PWD}\\database\\database.sqlite"
   ```

   > Si la ruta no funciona, usa solo `DB_DATABASE=database/database.sqlite`.

### Opción 2: MySQL / MariaDB

Editar `.env` con los datos de tu servidor:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_la_base
DB_USERNAME=root
DB_PASSWORD=
```

## Migrar la base de datos

```bash
php artisan migrate
```

> Si necesitas datos iniciales, puedes ejecutar migraciones con seeders si existen.

## Compilar assets

Para producción:

```bash
npm run build
```

Para desarrollo con recarga automática:

```bash
npm run dev
```

## Ejecutar la aplicación

```bash
php artisan serve
```

Luego abrir en el navegador:

```
http://127.0.0.1:8000
```

## Tips para compartir el proyecto

- No incluyas el archivo `.env` si lo envías por Git.
- Incluye `composer.json`, `composer.lock`, `package.json`, `package-lock.json` o `pnpm-lock.yaml` según corresponda.
- Si el receptor usa SQLite, envía también el archivo `database/database.sqlite` solo si quieres compartir datos ya poblados.

## Resumen rápido

- `composer install`
- `npm install`
- `copy .env.example .env`
- `php artisan key:generate`
- Configurar la base de datos en `.env`
- `php artisan migrate`
- `npm run build`
- `php artisan serve`

---

Hecho con Laravel 12 y Tailwind/Vite.
