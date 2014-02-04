#!/bin/bash -e

VERSION="0.1.9"

curl -sL https://github.com/ianbarber/php-svm/archive/${VERSION}.tar.gz | tar -xzf -
cd php-svm-${VERSION}
phpize
./configure
make
sudo make install
