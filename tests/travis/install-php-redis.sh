#!/bin/bash -e

VERSION="2.2.4"

curl -sL https://github.com/nicolasff/phpredis/archive/${VERSION}.tar.gz | tar -xzf -
cd phpredis-${VERSION}
phpize
./configure
make
sudo make install
