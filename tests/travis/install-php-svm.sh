#!/bin/bash -e

LIBSVM_VERSION="3.17"
PHPSVM_VERSION="0.1.9"

curl http://www.csie.ntu.edu.tw/~cjlin/libsvm/libsvm-${LIBSVM_VERSION}.tar.gz | tar xz
cd libsvm-${LIBSVM_VERSION}
sudo make lib
sudo cp libsvm.so.2 /usr/lib
sudo ln -sf /usr/lib/libsvm.so.2 /usr/lib/libsvm.so
ldconfig
ldconfig --print | grep libsvm

curl -L https://github.com/ianbarber/php-svm/archive/${PHPSVM_VERSION}.tar.gz | tar xz
cd php-svm-${PHPSVM_VERSION}
phpize
./configure
make
sudo make install
