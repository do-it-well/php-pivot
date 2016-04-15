test:
	docker run --rm -e LANG=C -w /app -v `pwd`:/app wpalmer/php:5.3-fpm-bundle-atlanta php /app/vendor/phpunit/phpunit/phpunit
