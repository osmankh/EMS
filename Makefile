start-dev-db-server:
	docker-compose up -d

init: start-dev-db-server
	composer install && \
	symfony check:requirements && \
	symfony console doctrine:migrations:migrate -q && \
	symfony console doctrine:fixtures:load -q --purge-with-truncate --group=app-initial-data

run:
	symfony server:start

stop:
	symfony server:stop