#!/usr/bin/env bash
set -e

./app-wait-services.sh

export PHPUNIT_TEST_RANGE=$1
../bin/cm app generate-config-internal
../bin/cm app setup --reload
../bin/phpunit  --bootstrap ./tests/bootstrap.php --configuration ./phpunit.xml  ./tests/helpers/CMTest/library/CMTest/SplittedUnitTest.php
