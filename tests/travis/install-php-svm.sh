#!/bin/bash -e

# VERSION="0.1.9"

# curl -sL https://github.com/ianbarber/php-svm/archive/${VERSION}.tar.gz | tar -xzf -
git clone https://github.com/echobot/php-svm.git
cd php-svm
phpize
./configure
make
make install
