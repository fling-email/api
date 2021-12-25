#!/bin/bash

# Work with the Docker daemon used by Kubernetes
eval $(minikube docker-env)

# Store the initial values of things we're going to patch so we can change them
# back later.
orig_pull_policy=$(kubectl get deployment fling-api -o json | jq -r '.spec.template.spec.containers[] | select(.name == "web").imagePullPolicy')
orig_image=$(kubectl get deployment fling-api -o json | jq -r '.spec.template.spec.containers[] | select(.name == "web").image')

function setup() {
    # Build the image and tag it as the loxal one
    echo "Building image"
    docker build -t flingemail/api:local --target app .

    # Override imagePullPolicy setting to make sure we use the local images first
    # and update the web container to use the one we just built
    echo "Updating deployment"
    kubectl patch deployment fling-api --patch '{"spec": {"template": {"spec": {"containers": [{"name": "web", "imagePullPolicy": "Never"}]}}}}'
    kubectl set image deployment/fling-api web=flingemail/api:local

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
    kubectl patch deployment fling-api --patch '{"spec": {"template": {"spec": {"containers": [{"name": "web", "imagePullPolicy": "Never"}]}}}}'
    kubectl set image deployment/fling-api web=flingemail/api:local
}

# Call the setup function
setup

# Call the cleanup script after the user exits logs
trap teardown INT

# Output logs from the deployment
kubectl logs --selector app=fling,service=api --container web --follow
