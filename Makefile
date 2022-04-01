start-dev-db-server:
	docker-compose up -d

init: start-dev-db-server
	composer install && \
	symfony console doctrine:migrations:migrate -q && \
	symfony console doctrine:fixtures:load -q

run:
	symfony server:start

stop:
	symfony server:stop