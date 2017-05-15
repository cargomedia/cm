FROM cargomedia/cm-application:v1

WORKDIR /app/cm

RUN apt-get update && apt-get install -y netcat curl redis-tools mysql-client

ADD composer.json /app/cm/composer.json
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

ADD codeship/app-wait-services.sh codeship/app-setup.sh codeship/test.sh /app/cm/
