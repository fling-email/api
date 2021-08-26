#!/bin/bash

# Run the build process to make sure we have the current image ready
build_scripts/build.sh

# Run that image and replace the container in the cluster with a proxy
telepresence \
    --swap-deployment fling-email-api \
    --expose 80 \
    --docker-run \
        --rm \
        -e "APACHE_RUN_USER=#$(id -u)" \
        -e "APACHE_RUN_GROUP=#$(id -g)" \
        --mount "type=bind,source=$(pwd),target=/var/www" \
        flingemail/api:latest
