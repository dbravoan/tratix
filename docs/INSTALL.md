# Instalación

## 1. Requisitos

| Requisito | Versión mínima | Notas |
|---|---|---|
| PHP | 8.3 | Se recomienda 8.4 (probado con 8.4.24) |
| Composer | 2.x | |
| MySQL | 8.x | También funciona PostgreSQL/MariaDB |
| Node.js | 20+ | Solo para compilar assets (Vite) |
| Docker | 24+ | Recomendado para desarrollo con **Laravel Sail** |
| OpenSSL | 3.x | Con subcomando `ts` (para el sello TSA RFC 3161) |
| Extensión `curl` | — | Necesaria para VIES y TSA |

Extensiones PHP recomendadas: `pdo_mysql`, `mbstring`, `dom`, `gd`, `fileinfo`,
`curl`, `openssl`, `intl`, `zip`.

## 2. Instalación con Laravel Sail (recomendado)

Laravel Sail levanta **PHP, MySQL, Redis y Mailpit** en contenedores Docker.
Los puertos ya están reasignados para no chocar con otros stacks
(app `8090`, MySQL `3307`, Redis `6380`, Mailpit `8026`/`1026`).

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

La configuración del `.env` ya apunta a los servicios de Sail
(`DB_HOST=mysql`, `REDIS_HOST=redis`, `MAIL_HOST=mailpit`,
`APP_URL=http://localhost:8090`).

Arranca el entorno y ejecuta las migraciones:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed     # crea la BD y el catálogo de documentos
npm run build                                 # compila assets (Breeze/Vite)
```

Entra en **http://localhost:8090**. Usuario de prueba: `test@example.com` /
`password` (plan Pro + admin). El correo llega a Mailpit: **http://localhost:8026**.

Comandos útiles:

```bash
./vendor/bin/sail artisan test               # tests
./vendor/bin/sail artisan queue:work         # procesar colas (emails)
./vendor/bin/sail artisan schedule:run       # tareas programadas (backup diario)
```

### Puerto de producción vs dev

- **Desarrollo**: `APP_PORT=8090` (evita conflicto con otros stacks en el 80/5173).
- **Producción**: expón el 80 con Nginx (sección 5) y configura `APP_URL` con tu dominio.

## 3. Instalación sin Docker (servidor local/VPS)

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configura la base de datos en `.env` apuntando a tu MySQL local:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=contratos_db
DB_USERNAME=contratos_user
DB_PASSWORD=tu_password
CACHE_STORE=file          # o redis si tienes Redis
SESSION_DRIVER=file
MAIL_MAILER=log           # o smtp en producción
```

Crea la base de datos y ejecuta las migraciones con el seeder (incluye el
catálogo de documentos y trámites):

```bash
mysql -u root -p -e "CREATE DATABASE contratos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

Compila los assets y arranca:

```bash
npm run build        # o npm run dev durante el desarrollo
php artisan serve    # http://localhost:8000
```

> **Nota sobre bases de datos de prueba:** `phpunit.xml` usa `contratos_test`
> (MySQL). Crea esa base antes de ejecutar los tests:
> `mysql -u root -p -e "CREATE DATABASE contratos_test ..."` y da permisos.

## 4. Pruebas

```bash
php artisan test          # 101 tests (validadores, workflow, negociación, firma,
                          # sellado, documentos, países LATAM, billing, backups)
vendor/bin/pint           # estilo de código
```

Existe además un **test de concurrencia** (`tests/concurrency.sh`) que lanza 15
peticiones paralelas de firma por rol contra el servidor de Sail y verifica que
el guard de base de datos admite exactamente una firma por rol y que el sellado
es idempotente:

```bash
./tests/concurrency.sh http://127.0.0.1:8090
```

## 5. Despliegue en un VPS (producción)

Ejemplo con Nginx + PHP-FPM + MySQL en un VPS Linux (Debian/Ubuntu).

### 5.1 Preparar el proyecto

```bash
git clone <repo> /var/www/contratos
cd /var/www/contratos
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

Edita `.env` para producción:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://contratos.tudominio.com

DB_HOST=127.0.0.1
DB_DATABASE=contratos_db
DB_USERNAME=contratos_user
DB_PASSWORD=clave_fuerte

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
```

Crea los directorios con permisos correctos:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
mkdir -p storage/app/private/contracts storage/app/private/documents
php artisan migrate --seed --force
php artisan config:cache route:cache view:cache
```

### 5.2 Nginx

```nginx
server {
    listen 80;
    server_name contratos.tudominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name contratos.tudominio.com;

    root /var/www/contratos/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/contratos.tudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/contratos.tudominio.com/privkey.pem;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

HTTPS con Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d contratos.tudominio.com
```

### 5.3 Colas (emails y tareas pesadas)

El sistema usa colas de Laravel. Instala supervisor:

```bash
sudo apt install supervisor
```

`/etc/supervisor/conf.d/contratos-worker.conf`:

```ini
[program:contratos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/contratos/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/contratos/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start contratos-worker:*
```

Añade un cron para tareas programadas (limpieza de enlaces caducados, etc.):

```cron
* * * * * php /var/www/contratos/artisan schedule:run >> /dev/null 2>&1
```

### 5.4 Copias de seguridad

Backup de la BD y de `storage/app/private` (PDFs firmados, firmas y documentos
adjuntos son **evidencias legales irrecuperables**: hay que respaldarlas sí o sí).

```bash
#!/bin/bash
# /usr/local/bin/backup-contratos.sh
BACKUP_DIR=/var/backups/contratos
DATE=$(date +%Y%m%d_%H%M)
mkdir -p "$BACKUP_DIR/$DATE"

mysqldump -u contratos_user -p'clave' contratos_db | gzip > "$BACKUP_DIR/$DATE/db.sql.gz"
tar czf "$BACKUP_DIR/$DATE/storage.tar.gz" -C /var/www/contratos/storage/app/private

find "$BACKUP_DIR" -type d -mtime +30 -exec rm -rf {} +
```

Cron diario: `0 3 * * * /usr/local/bin/backup-contratos.sh`

### 5.5 Seguridad recomendada

- `fail2ban` para SSH y Nginx.
- Firewall (ufw) abriendo solo 22, 80 y 443.
- Rotación de logs (`logrotate`).
- Mantener PHP, Laravel y dependencias actualizadas (`composer audit`).
- Restringir permisos del directorio de almacenamiento.
