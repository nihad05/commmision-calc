# Commission Calculator

Calculates commissions for cash-in, cash-out and loan-repayment transactions. Built with Laravel 13, PHP 8.3 and MySQL 8.4, running in Docker.

## Requirements

Docker with Compose v2. No local PHP, Composer or MySQL is needed.

## Getting started

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app sh -c "composer install && php artisan key:generate && php artisan migrate"
```

The API is now available at `http://localhost:8080`.

On Linux, if your user id isn't 1000, build with `HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up -d --build` so files created by the container belong to you.

## Database access

| Setting  | Value        |
|----------|--------------|
| Host     | `127.0.0.1`  |
| Port     | `3307`       |
| User     | `commission` |
| Password | `secret`     |
| Database | `commission` |

Use `127.0.0.1`, not `localhost`. Or open the MySQL client directly:

```bash
docker exec -it commission-mysql mysql -ucommission -psecret commission
```
