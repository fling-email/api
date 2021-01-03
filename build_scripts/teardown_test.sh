#!/bin/bash

# Some useful paths
network_id_file="build_scripts/docker_network.id"
app_container_id_file="build_scripts/docker_app.id"
database_container_id_file="build_scripts/docker_database.id"

if [ -f $app_container_id_file ]; then
    app_container_id=$(cat $app_container_id_file)

    docker kill $app_container_id

    rm $app_container_id_file
fi

if [ -f $database_container_id_file ]; then
    database_container_id=$(cat $database_container_id_file)

    docker kill $database_container_id

    rm $database_container_id_file
fi

if [ -f $network_id_file ]; then
    network_id=$(cat $network_id_file)

    docker network rm $network_id

    rm $network_id_file
fi
