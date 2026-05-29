# DCEV Backend Server Setup Checklist

Use this for any server that will run the Laravel backend.

## 1. Install Required Software

Install:

- PHP 8.4 or agreed PHP version
- Composer
- IIS or preferred web server
- Microsoft ODBC Driver for SQL Server
- Microsoft PHP Drivers for SQL Server

PHP must have these extensions enabled:

```text
sqlsrv
pdo_sqlsrv
```

Download PHP SQL Server drivers here:

```text
https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
```

## 2. Enable SQL Server Extensions in php.ini

The driver files must match the server PHP version, thread safety, and architecture.

Example for PHP 8.4, Thread Safe, x64:

```ini
extension=php_sqlsrv_84_ts_x64.dll
extension=php_pdo_sqlsrv_84_ts_x64.dll
```

Confirm they are loaded:

```bash
php -m | findstr sqlsrv
```

Expected result:

```text
pdo_sqlsrv
sqlsrv
```

## 3. Create Database and Login

Run this in SQL Server Management Studio.

Change the database name, username, and password before production use.

```sql
CREATE DATABASE [dcev_prod];
GO

USE [master];
GO

CREATE LOGIN [dcev_prod_user]
WITH PASSWORD = 'CHANGE_THIS_STRONG_PASSWORD',
DEFAULT_DATABASE = [dcev_prod],
CHECK_POLICY = ON,
CHECK_EXPIRATION = OFF;
GO

USE [dcev_prod];
GO

CREATE USER [dcev_prod_user] FOR LOGIN [dcev_prod_user];
GO

ALTER ROLE [db_owner] ADD MEMBER [dcev_prod_user];
GO
```

For staging, use separate names:

```text
Database: dcev_staging
Username: dcev_staging_user
Password: separate strong staging password
```

For local development, example:

```text
Database: dcev_local
Username: dcev_user
Password: DcevLocal@12345
```

## 4. Laravel .env Example

```env
APP_NAME=DCEV
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.dcev.domain.gov.ng

DB_CONNECTION=sqlsrv
DB_HOST=production-sql-server-host
DB_PORT=1433
DB_DATABASE=dcev_prod
DB_USERNAME=dcev_prod_user
DB_PASSWORD=CHANGE_THIS_STRONG_PASSWORD
```

## 5. Deployment Commands

Run from the Laravel backend folder:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
```

## 6. Final Check

Before going live, confirm:

```bash
php -m | findstr sqlsrv
```

returns:

```text
pdo_sqlsrv
sqlsrv
```

If not, Laravel will not connect to MSSQL.
