FROM cargomedia/cm-application:latest

RUN apt-get update && apt-get install -y netcat curl redis-tools mysql-client

WORKDIR /app/cm

COPY composer.json ./composer.json
RUN composer up
