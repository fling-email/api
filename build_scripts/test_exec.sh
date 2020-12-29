#!/bin/bash

# Some useful paths
network_id_file="build_scripts/docker_network.id"
app_container_id_file="build_scripts/docker_app.id"
database_container_id_file="build_scripts/docker_database.id"

app_container_id=$(cat $app_container_id_file)
database_container_id=$(cat $database_container_id_file)

docker exec \
    -u "$(id -u):$(id -g)" \
    -e "DB_HOST=${database_container_id:0:12}" \
    -e "DB_CONNECTION=mysql" \
    -e "DB_USERNAME=root" \
    -e "DB_PASSWORD=secret" \
    -e "DB_DATABASE=api" \
    "${app_container_id}" \
    ${@:1}
