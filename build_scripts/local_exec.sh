#!/bin/bash

# Helper script to execute a command in the local container running the app.

local_container_id=$( \
    docker ps \
        --no-trunc \
        --filter "ancestor=flingemail/api:latest" \
        --format "{{.ID}}"
)

if [ "${local_container_id}" = "" ]; then
    echo "Unable to find local container, try running start.sh again"
    exit 1
fi

docker exec \
    --interactive \
    -u "$(id -u):$(id -g)" \
    "${local_container_id}" \
    ${@:1}
