Implementation of a notification subscription feature using a queue on Laravel.

### Installation

```bash
cp .env.example .env
```

```bash
composer install
```

```bash
php artisan key:generate
```

Configure your database credentials then run,

```bash
php artisan migrate
```

### Usage

By default, the system seeds some topics for testing subscription and publishing.

Run Queue

```bash
php artisan queue:work
```

Serve application

```bash
php artisan serve
```

Run automated test

```bash
php artisan test
```
