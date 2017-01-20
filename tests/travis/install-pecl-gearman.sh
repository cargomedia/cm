#!/bin/bash -e

git clone https://github.com/wcgallego/pecl-gearman.git
cd pecl-gearman
phpize
./configure
make
make install
