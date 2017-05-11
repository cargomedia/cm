FROM cargomedia/cm-application:v1

WORKDIR /app/cm

RUN apt-get update && apt-get install -y netcat curl redis-tools mysql-client
ADD codeship/app-wait-services.sh /app/cm/app-wait-services.sh
ADD codeship/app-setup.sh /app/cm/app-setup.sh
ADD codeship/test.sh /app/cm/test.sh

ADD composer.json /app/cm/composer.json
ADD composer.lock /app/cm/composer.lock
ADD vendor /app/cm/vendor
RUN composer up

ADD phpunit.xml /app/cm/phpunit.xml
ADD bin /app/cm/bin
ADD layout /app/cm/layout
ADD resources /app/cm/resources
ADD tests /app/cm/tests
ADD client-vendor /app/cm/client-vendor
ADD library /app/cm/library
RUN /bin/sh -c "[ -f '/app/cm/resources/config/test.docker.php' ] || ln -s /app/cm/resources/config/_test.docker.php /app/cm/resources/config/test.docker.php" \
 && /bin/sh -c "[ -f '/app/cm/resources/config/local.docker.php' ] || ln -s /app/cm/resources/config/_local.docker.php /app/cm/resources/config/local.docker.php"
