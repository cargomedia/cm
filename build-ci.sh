#!/bin/bash -e
cd $(dirname $0)

USER="root"
HOST="172.10.1.100"

ssh -o StrictHostKeyChecking=no ${USER}@${HOST} echo -n
DIR=$(ssh ${USER}@${HOST} "mktemp -d /tmp/build.XXXXXX")

scp -qr . ${USER}@${HOST}:${DIR}
ssh ${USER}@${HOST} "
 cd ${DIR} &&
 cp resources/config/local.ci.php resources/config/local.php
 composer -n install &&
 phpunit -d display_errors=1
"
ssh ${USER}@${HOST} "rm -rf ${DIR}"
