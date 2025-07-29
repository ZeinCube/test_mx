COMPOSE_LOCAL_FILE = -f docker-compose.yaml
THIS_FILE := $(lastword $(MAKEFILE_LIST))
MAKE:=$(MAKE) -f $(THIS_FILE)
COMPOSE_LOCAL=docker compose --env-file ${DOTENV_FILE} ${COMPOSE_LOCAL_FILE}
EXEC=$(COMPOSE_LOCAL) exec php-fpm
DOTENV_FILE:=.env.local

COMPOSE=docker compose --env-file ${DOTENV_FILE} ${COMPOSE_LOCAL_FILE}

env: ## Create .env.local file from .env
	@[ -f ./${DOTENV_FILE} ] \
 	&& (echo "${YELLOW}Env file ${DOTENV_FILE} already exists!${NC}"; exit 0) \
 	|| (cp -n .env ${DOTENV_FILE}; echo "${GREEN}New ${DOTENV_FILE} file created!\n${YELLOW}Please configure a new file first and rerun command!${NC}"; exit 1 )

migrate: env
	@echo "Running migrations..."
	./data/scripts/run_migrations_docker.sh

c-inst: env ## Run composer install
	@$(EXEC) composer install

up: env
	@echo "Starting docker containers..."
	@$(COMPOSE) up -d
	${THIS_FILE} migrate
	${THIS_FILE} c-inst