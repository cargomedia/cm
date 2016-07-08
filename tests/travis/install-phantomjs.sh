#!/bin/bash -e

VERSION="2.1.7"

cd $(mktemp -dt phantomjs-setup-XXX)
npm install phantomjs-prebuilt@${VERSION}
export PATH=$(pwd)/node_modules/phantomjs-prebuilt/bin:$PATH
echo "phantomjs $(phantomjs --version) installed in $(which phantomjs)"
cd -
