#!/usr/bin/env bash

set -e
./app-wait-services.sh
./bin/cm app generate-config-internal
./bin/cm app setup --reload