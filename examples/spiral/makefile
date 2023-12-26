.DEFAULT_GOAL := help
MAKEFLAGS += --no-print-directory

.EXPORT_ALL_VARIABLES:
ROOT_DIR := $(shell pwd)

install:
	@cp .env.sample .env
	@echo "UID=$(shell id -u)" >> .env
	@docker-compose build
	@make composer_install
	@docker run -ti --rm -v ${ROOT_DIR}:/data graphql-spiral:local sh -c "cd /data && php app.php encrypt:key --no-ansi -n -m .env"
	@make up

up:
	@docker-compose up -d

down:
	@docker-compose down

restart:
	@make down
	@make up

composer_install:
	@docker run -ti --rm -v ${ROOT_DIR}:/data graphql-spiral:local sh -c "cd /data && composer install"

build:
	@docker-compose build

rr:
	@docker exec -ti graphql-spiral-dev sh

help:
	@echo "make install          - first installation"
	@echo "make up               - run docker"
	@echo "make down             - stop docker"
	@echo "make restart          - stop docker & run"
	@echo "make composer_install - install dependencies"
	@echo "make build            - build docker images"
	@echo "make rr               - run shell on RoadRunner container"
