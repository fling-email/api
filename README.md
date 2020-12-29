# Fling.email API

App behind the fling.email service.

## Development

This section assumes you have a local kubernetes deployment already running and
working. For instructions see the Running Locally section of the readme for out
kubernetes deployment [repo](https://github.com/fling-email/deploy-kubernetes#flingemail-kubernetes-deployment).

To replace the container running in the cluster with a local one that can be
edited in realtime.

```
build_scripts/start.sh
```

To run the database migrations

```
build_scripts/local_exec.sh php artisan migrate:fresh
```

You can then populate the database with generated test data

```
build_scripts/local_exec.sh php artisan db:seed
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
