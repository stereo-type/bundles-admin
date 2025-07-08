clear: cache-clear cache-warmup

cache-clear:
	composer dump-autoload -o  && php bin/console cache:clear
cache-warmup:
	php bin/console cache:warmup
stan:
	php vendor/bin/phpstan analyse src

fix:
	php vendor/bin/php-cs-fixer fix src

clean: fix stan cache-clear

entity:
	php bin/console m:e

migration:
	php bin/console make:migration

migration-migrate:
	php bin/console doctrine:migrations:migrate

jwt:
	php bin/console lexik:jwt:generate-keypair --skip-if-exists
