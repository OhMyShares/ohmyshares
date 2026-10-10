docker-build:
	docker compose build

up:
	docker compose up -d

dev:
	docker compose -f docker-compose.dev.yml up -d
	docker compose -f docker-compose.dev.yml exec -T app sh -c 'envsubst < /var/www/oms/frontend/src/assets/env.template.js > /var/www/oms/frontend/src/assets/env.js'
	cd frontend && npm run start -- --open

build:
	cd frontend && npm run build

test:
	php artisan test

start:
	docker compose start

stop:
	docker compose stop

down:
	docker compose down

restart:
	docker compose stop && docker compose start

shell:
	docker compose exec app bash

shell-db:
	docker compose exec mysql bash
