#!/bin/bash

# this is helpful to compile extensions
sudo apt-get install autoconf

tests/travis/install-apcu.sh
tests/travis/install-memcache.sh
