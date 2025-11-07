include .env

.PHONY: install backend frontend up down test lint format pwa

install: backend frontend

backend:
@if [ ! -f vendor/autoload.php ]; then \
composer install; \
fi

frontend:
@if [ ! -d node_modules ]; then \
npm install; \
fi

up:
docker compose up -d

stop:
docker compose stop

down:
docker compose down -v

test:
php artisan test

lint:
./vendor/bin/pint

format: lint

pwa:
npm run build && php artisan export:pwa
