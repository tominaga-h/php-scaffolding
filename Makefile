CONTAINER_NAME := php-scaffolding_php_1

up:
	docker-compose up -d
down:
	docker-compose down --remove-orphans
bash:
	docker exec -it $(CONTAINER_NAME) bash
install:
	docker exec -it $(CONTAINER_NAME) composer install
test:
	docker exec -it $(CONTAINER_NAME) composer test
php:
	docker exec -it $(CONTAINER_NAME) php -a
push:
	make test && git push origin master
