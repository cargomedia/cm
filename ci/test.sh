#!/usr/bin/env bash
set -e
CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cp ./resources/config/_local.docker.php ./resources/config/local.docker.php
cp ./resources/config/_test.docker.php ./resources/config/test.docker.php

${CURRENT_DIR}/app-wait-services.sh
${CURRENT_DIR}/app-setup.sh

export PHPUNIT_TEST_RANGE=${1:-"1/1"}
./bin/phpunit  --bootstrap ./tests/bootstrap.php --configuration ./phpunit.xml  ./tests/helpers/CMTest/library/CMTest/SplittedUnitTest.php
