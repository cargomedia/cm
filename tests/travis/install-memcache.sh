#!/bin/bash

# install this version
MEMCACHE=2.2.6

# compile manually
wget http://pecl.php.net/get/memcache-$MEMCACHE.tgz
tar zxvf memcache-$MEMCACHE.tgz
cd "memcache-${MEMCACHE}"
phpize && ./configure && make install && echo "Installed ext/memcache-${MEMCACHE}"
