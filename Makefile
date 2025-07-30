COMPOSE_LOCAL_FILE = -f docker-compose.yaml
THIS_FILE := $(lastword $(MAKEFILE_LIST))
MAKE:=$(MAKE) -f $(THIS_FILE)
COMPOSE_LOCAL=docker compose --env-file ${DOTENV_FILE} ${COMPOSE_LOCAL_FILE}
EXEC=$(COMPOSE_LOCAL) exec php-fpm
DOTENV_FILE:=.env.local

COMPOSE=docker compose --env-file ${DOTENV_FILE} ${COMPOSE_LOCAL_FILE}

.PHONY: help build up down restart logs migrate migrate-fresh test

help: ## Показать справку
	@echo "Доступные команды:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Собрать Docker образы
	docker compose build

up: ## Запустить контейнеры
	docker compose up -d

down: ## Остановить контейнеры
	docker compose down

restart: ## Перезапустить контейнеры
	docker compose restart

logs: ## Показать логи контейнеров
	docker compose logs -f

migrate: ## Запустить миграции базы данных
	docker exec -it test_mx-php-fpm-1 php /var/www/html/data/scripts/run_migrations.php

migrate-fresh: ## Полностью перенакатить миграции (удалить и пересоздать таблицы)
	docker exec -it test_mx-php-fpm-1 php /var/www/html/data/scripts/run_migrations.php --fresh
