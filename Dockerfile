FROM cargomedia/cm-application:latest

RUN apt-get update && apt-get install -y netcat curl redis-tools mysql-client

WORKDIR /app/cm

COPY composer.json ./composer.json
RUN composer up

COPY phpunit.xml .
COPY bin ./bin
COPY ci ./ci
COPY resources ./resources
RUN cp ./resources/config/_local.docker.php ./resources/config/local.docker.php
RUN cp ./resources/config/_test.docker.php ./resources/config/test.docker.php

COPY layout ./layout
COPY client-vendor ./client-vendor
COPY tests ./tests
COPY library ./library

CMD ["./ci/all.sh"]
