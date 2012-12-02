#!/bin/bash -e
cd $(dirname $0)

USER="root"
HOST="172.10.1.100"
DIR=$(ssh ${USER}@${HOST} "mktemp -d /tmp/build.XXXXXX")

scp -qr . ${USER}@${HOST}:${DIR}
ssh ${USER}@${HOST} "
 cd ${DIR} &&
 cp resources/config/local.ci.php resources/config/local.php
 composer -q -n install &&
 phpunit -d display_errors=1 tests/
"
ssh ${USER}@${HOST} "rm -rf ${DIR}"
