#!/bin/bash -e

git clone https://github.com/hnw/php-timecop.git
cd php-timecop
phpize
./configure
make
make install
