#!/bin/bash -e

VERSION="1.2.4"

git clone https://github.com/hnw/php-timecop.git php-timecop
cd php-timecop
git checkout ${VERSION}
phpize
./configure
make
make install
