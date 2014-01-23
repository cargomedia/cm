#!/bin/bash

if [ "$TRAVIS_PHP_VERSION" == "5.3" ]
then
    exit 0
fi

# install this version
APCU=4.0.2

# compile manually
wget http://pecl.php.net/get/apcu-$APCU.tgz
tar zxvf apcu-$APCU.tgz
cd "apcu-${APCU}"
phpize && ./configure && make install && echo "Installed ext/apcu-${APCU}"
