#!/bin/bash -e

VERSION="5e179e978af79444d3c877d5681ea91d15134a01"

git clone http://github.com/zenovich/runkit.git runkit
cd runkit
git checkout ${VERSION}
phpize
./configure
make
sudo make install
