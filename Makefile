.PHONY: help unitTest test, cs-check, cs-fix
bin_dir=vendor/bin

vendor/autoload.php:
	composer install

cs-check: vendor/autoload.php ## Check PHP CS
	${bin_dir}/php-cs-fixer --version
	${bin_dir}/php-cs-fixer fix -v --diff --dry-run

cs-fix: vendor/autoload.php ## Fix PHP CS
	${bin_dir}/php-cs-fixer --version
	${bin_dir}/php-cs-fixer fix -v --diff

console: ## Launch zsh in docker container with PHP
	docker run \
		--name=security_bundle_console \
		--volume=$(shell pwd):/srv \
		--volume=$$DEV/.home-developer:/home/developer \
		--env USERNAME=$(shell whoami) \
		--env UNIX_UID=$(shell id -u) \
		--env=CONTAINER_SHELL=/bin/zsh \
		--workdir=/srv \
		--interactive \
		--tty \
		--rm \
		code202/php-console:8.1 \
		/bin/login -p -f $(shell whoami)

help:
    @grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help