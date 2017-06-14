#!/usr/bin/env bash
set -e
CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

${CURRENT_DIR}/app-wait-services.sh
${CURRENT_DIR}/app-setup.sh
${CURRENT_DIR}/test.sh "$1"
