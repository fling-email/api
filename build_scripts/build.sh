#!/bin/bash

docker build --target app -t flingemail/api:latest .
docker build --target test -t flingemail/api:latest-test .
