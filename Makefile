.PHONY: help build up down sh composer-install test phpunit stan pint format

# Default target
help:
	@echo "StitchWizard Development Commands:"
	@echo "--------------------------------"
	@echo "build              - Build Docker containers (docker compose build)"
	@echo "up                 - Start Docker containers in detached mode (docker compose up -d)"
	@echo "down               - Stop and remove Docker containers (docker compose down)"
	@echo "sh                 - Open bash shell in PHP container (docker compose exec php bash)"
	@echo "composer-install   - Install PHP dependencies (docker compose run --rm php composer install)"
	@echo "test               - Run Pest tests (docker compose run --rm php vendor/bin/pest -q)"
	@echo "phpunit            - Run PHPUnit tests (docker compose run --rm php vendor/bin/phpunit -q)"
	@echo "stan               - Run PHPStan static analysis (docker compose run --rm php vendor/bin/phpstan analyse --memory-limit=1G)"
	@echo "pint               - Run Laravel Pint code style fixer (docker compose run --rm php vendor/bin/pint)"
	@echo "format             - Alias for pint (docker compose run --rm php vendor/bin/pint)"

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

sh:
	docker compose exec php bash

composer-install:
	docker compose run --rm php composer install

test:
	docker compose run --rm php vendor/bin/pest -q

phpunit:
	docker compose run --rm php vendor/bin/phpunit -q

stan:
	docker compose run --rm php vendor/bin/phpstan analyse --memory-limit=1G

pint:
	docker compose run --rm php vendor/bin/pint

format: pint
