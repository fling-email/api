#!/bin/bash

# Some useful paths
network_id_file="build_scripts/docker_network.id"
app_container_id_file="build_scripts/docker_app.id"
database_container_id_file="build_scripts/docker_database.id"

if [ -f $network_id_file ] || [ -f $app_container_id_file ] || [ -f $database_container_id_file ]; then
    echo "Docker ID files already exist. Either the test environment is already running or they need to be deleted"
    echo
    echo "ID files found:"

    ls -lah build_scripts/*.id

    exit 1
fi

# Create a private network for the containers and store its name for later
docker_network_name=$(uuidgen)
docker_network_id=$(docker network create --internal --attachable "${docker_network_name}")

echo -n $docker_network_id > $network_id_file

# Run the build process
build_scripts/build.sh

# Start the application container
docker run \
    --rm \
    --detach \
    --cidfile $app_container_id_file \
    --network $docker_network_id \
    -e "APACHE_RUN_USER=#$(id -u)" \
    -e "APACHE_RUN_GROUP=#$(id -g)" \
    --mount "type=bind,source=$(pwd),target=/var/www" \
    flingemail/api:latest-test

# Start a database for testing
docker run \
    --rm \
    --detach \
    --cidfile $database_container_id_file \
    --network $docker_network_id \
    --health-cmd "mysqladmin ping --silent" \
    -e "MYSQL_ROOT_PASSWORD=secret" \
    -e "MYSQL_DATABASE=api" \
    mariadb:10.7.1

database_container_id=$(cat $database_container_id_file)

# Wait for the database to be ready
until [ "$(docker inspect --format='{{ .State.Health.Status }}' $database_container_id)" == "healthy" ]; do

    echo "Waiting for database to start"
    sleep 5

done
