# Makefile

# Variables
DOCKER_COMPOSE = docker-compose
PHP_CONTAINER = php

# Symfony commands
sf:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console $(cmd)

# Database commands
db-make-migration:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console make:migration

db-migrate:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate

db-diff:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console doctrine:migrations:diff

db-load-fixtures:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console doctrine:fixtures:load

test-coverage:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/phpunit --coverage-html=coverage

test-coverage-file:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/phpunit tests/Controller/SpaceControllerTest.php

asset-compile:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php php bin/console asset-map:compile

# Cache commands
cache-clear:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console cache:clear

# Composer commands
composer-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install

composer-update:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer update

# NPM commands
npm-install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm install

npm-build:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) npm run build

# Usage:
# make sf cmd="command" - Run Symfony command
# make db-migrate - Run database migrations
# make db-diff - Generate migration diff
# make db-load-fixtures - Load database fixtures
# make cache-clear - Clear Symfony cache
# make composer-install - Install Composer dependencies
# make composer-update - Update Composer dependencies
# make npm-install - Install NPM dependencies
# make npm-build - Build assets with NPM