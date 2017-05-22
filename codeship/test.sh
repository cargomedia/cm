#!/usr/bin/env bash
set -e

./app-wait-services.sh

export PHPUNIT_TEST_RANGE=$1
./bin/phpunit  --bootstrap ./tests/bootstrap.php --configuration ./phpunit.xml  ./tests/helpers/CMTest/library/CMTest/SplittedUnitTest.php
