#!/usr/bin/env bash

apt-get update && apt-get install -y net-tools netcat curl redis-tools mysql-client

set -e
CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

${CURRENT_DIR}/app-wait-services.sh
${CURRENT_DIR}/app-setup.sh
${CURRENT_DIR}/test.sh "$1"
