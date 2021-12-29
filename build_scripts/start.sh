#!/bin/bash

# Work with the Docker daemon used by Kubernetes
eval $(minikube docker-env)

# Store the initial values of things we're going to patch so we can change them
# back later.
orig_pull_policy=$(kubectl get deployment fling-api -o json | jq -r '.spec.template.spec.containers[] | select(.name == "web").imagePullPolicy')
orig_image=$(kubectl get deployment fling-api -o json | jq -r '.spec.template.spec.containers[] | select(.name == "web").image')

function setup() {
    # Make sure ksync is going to work before messing with the deployment
    ksync_output=$(ksync get)

    if [ $? -ne 0 ]; then
        echo ${ksync_output}
        exit $?
    fi

    # Build the image and tag it as the loxal one
    echo "Building image"
    docker build -t flingemail/api:local --target app .

    # Override imagePullPolicy setting to make sure we use the local images first
    # and update the web container to use the one we just built
    echo "Updating deployment"
    kubectl patch deployment fling-api --patch '{"spec": {"template": {"spec": {"containers": [{"name": "web", "imagePullPolicy": "Never"}]}}}}'
    kubectl set image deployment/fling-api web=flingemail/api:local

    # Wait for the containers to be ready with the new image
    until [ "$(kubectl get pods --selector app=fling,service=api --field-selector status.phase!=Running)" == "" ]; do

        echo "Waiting for replacement pods to be ready"
        echo "Current status:"

        kubectl get pods --selector app=fling,service=api

        sleep 5

    done

    # Keep local files in sync with the container
    echo "Starting ksync job"
    ksync create --selector app=fling,service=api --name fling-api --container web $(pwd) /var/www
}

function teardown() {
    # Remove the ksync job
    echo "Stopping ksync job"
    ksync delete fling-api

    # Set the imagePullPolicy and image name back to the original values
    echo "Resetting deployment changes"
    kubectl patch deployment fling-api --patch "{\"spec\": {\"template\": {\"spec\": {\"containers\": [{\"name\": \"web\", \"imagePullPolicy\": \"${orig_pull_policy}\"}]}}}}"
    kubectl set image deployment/fling-api web=${orig_image}
}

# Call the setup function
setup

# Call the cleanup script after the user exits logs
trap teardown INT

# Output logs from the deployment
kubectl logs --selector app=fling,service=api --container web --follow
