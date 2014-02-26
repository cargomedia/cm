#!/bin/bash -e

LIBSVM_VERSION="3.17"
PHPSVM_VERSION="0.1.9"

wget -c http://www.csie.ntu.edu.tw/~cjlin/libsvm/libsvm-$LIBSVM_VERSION.tar.gz
tar xvfz libsvm-$LIBSVM_VERSION.tar.gz
cd libsvm-$LIBSVM_VERSION
make lib all
sudo install -D -m755 svm-train /usr/bin/svm-train
sudo install -D -m755 svm-predict /usr/bin/svm-predict
sudo install -D -m755 svm-scale /usr/bin/svm-scale
sudo install -D -m644 libsvm.so.2 /usr/lib/libsvm.so.2
sudo ldconfig
cd ..

curl -L https://github.com/ianbarber/php-svm/archive/${PHPSVM_VERSION}.tar.gz | tar xz
cd php-svm-${PHPSVM_VERSION}
phpize
./configure
make
sudo make install
