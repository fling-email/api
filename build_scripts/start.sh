#!/bin/bash

docker-compose stop
docker-compose rm -f

docker-compose pull
docker-compose build

docker-compose up --no-start --remove-orphans

docker-compose restart

db_cid=$(docker-compose ps -q db)

while [ "$(docker inspect $db_cid --format '{{.State.Health.Status}}')" != "healthy" ]; do
    echo "Waiting for database to start"
    sleep 5
done

docker-compose exec web php artisan migrate:fresh --seed
