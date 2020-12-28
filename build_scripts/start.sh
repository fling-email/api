#!/bin/bash

docker run \
    --rm \
    -e "APACHE_RUN_USER=#$(id -u)" \
    -e "APACHE_RUN_GROUP=#$(id -g)" \
    --mount "type=bind,source=$(pwd),target=/var/www" \
    -p 8080:80 \
    fling/api:latest
