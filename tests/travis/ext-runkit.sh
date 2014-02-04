#!/bin/bash -e

VERSION="1.0.3"

curl -sL https://github.com/downloads/zenovich/runkit/runkit-${VERSION}.tgz | tar -xzf -
cd runkit-${VERSION}
phpize
./configure
make
sudo make install
