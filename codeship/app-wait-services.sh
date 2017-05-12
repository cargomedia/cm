#!/usr/bin/env bash

function test_mysql {
  mysqladmin -h "mysql" ping &>/dev/null
}

function test_redis {
  redis-cli -h "redis" PING &>/dev/null
}

function test_memcached {
  echo "flush_all" | nc -w1 "memcached" "11211" &>/dev/null
}

function test_mongo {
  curl "http://mongo:27017" &>/dev/null
}

echo "Waiting for all services..."
count=0
until ( test_mysql && test_redis && test_memcached && test_mongo )
do
  ((count++))
  if [ ${count} -gt 120 ]
  then
    echo "Services didn't become ready in time"
    exit 1
  fi
  sleep 0.5
done
echo "All services are ready!"
exit 0
