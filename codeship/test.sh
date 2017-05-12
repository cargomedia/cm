#!/usr/bin/env bash

set -e

./app-wait-services.sh
./bin/phpunit