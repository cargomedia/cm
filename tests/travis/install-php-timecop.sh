#!/bin/bash -e

VERSION="v1.2.6"

git clone https://github.com/hnw/php-timecop.git php-timecop
cd php-timecop
git checkout ${VERSION}
phpize
./configure
make
make install
