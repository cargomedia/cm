#!/bin/bash -e

VERSION="1.0.4"

git clone http://github.com/zenovich/runkit.git runkit
cd runkit
git checkout ${VERSION}
phpize
./configure
make
make install
