# Fling.email API

App behind the fling.email service.

## Development

This section assumes you have a local kubernetes deployment already running and
working. For instructions see the Running Locally section of the readme for out
kubernetes deployment [repo](https://github.com/fling-email/deploy-kubernetes#flingemail-kubernetes-deployment).

Make sure you have ksync installed and initialised in your cluster
```
 url https://ksync.github.io/gimme-that/gimme.sh | bash
ksync init
```

Run a watch process to sync the files to the pod

```
ksync watch
```

Start the dev version of the deployment
```
build_scripts/start.sh
```

To run the database migrations

```
kubectl exec deployment/fling --container web -- php artisan migrate:fresh
```

You can then populate the database with generated test data

```
kubectl exec deployment/fling --container web -- php artisan db:seed
```

## Testing

PHPUnit tests are run using a local database independant of the one hosted by
minikube. This makes it easier for CI servers.

To prepare the test environment run

```
build_scripts/prepare_test.sh
```

Run the tests using the composer script

```
build_scripts/test_exec.sh vendor/bin/phpunit
```

Once done cleanup the test environment

```
build_scripts/teardown_test.sh
```
