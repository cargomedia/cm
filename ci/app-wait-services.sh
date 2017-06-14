#!/usr/bin/env bash

function log_status {
  [ $1 -ne 0 ] && printf "$2:waiting " || printf "$2:ready "
  return $1
}

function test_mysql {
  mysqladmin -h "mysql" ping &>/dev/null
  log_status $? mysql
}

function test_redis {
  redis-cli -h "redis" PING &>/dev/null
  log_status $? redis
}

function test_memcached {
  echo "flush_all" | nc -w1 "memcached" "11211" &>/dev/null
  log_status $? memcached
}

function test_mongo {
  curl "http://mongo:27017" &>/dev/null
  log_status $? mongo
}

function test_gearman {
  echo "status" | nc -w1 "gearman" "4730" &>/dev/null
  log_status $? gearman
}

function test_elasticsearch {
  curl "http://elasticsearch:9200" &>/dev/null
  log_status $? elasticsearch
}

echo "Waiting for all services..."
count=0
until ( test_mysql && test_redis && test_memcached && test_mongo && test_gearman && test_elasticsearch )
do
  ((count++))
  if [ ${count} -gt 60 ]
  then
    echo "Services didn't become ready in time"
    exit 1
  fi
  sleep 1
  printf "\n"
done
printf "\n"
echo "All services are ready!"
exit 0
