#!/usr/bin/env bash
set -e
./bin/cm app generate-config-internal
./bin/cm app setup --reload
