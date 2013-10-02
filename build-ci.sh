#!/bin/bash -e
cd $(dirname $0)

USER="root"
HOST="10.10.10.100"

ssh -o StrictHostKeyChecking=no ${USER}@${HOST} echo -n
DIR=$(ssh ${USER}@${HOST} "mktemp -d /tmp/build.XXXXXX")

scp -qr . ${USER}@${HOST}:${DIR}
ssh ${USER}@${HOST} "
 cd ${DIR} &&
 composer -n install &&
 scripts/cm.php app generate-local-config resources/config/local.ci.json &&
 scripts/cm.php app set-deploy-version &&
 phpunit -d display_errors=1
"
ssh ${USER}@${HOST} "rm -rf ${DIR}"
