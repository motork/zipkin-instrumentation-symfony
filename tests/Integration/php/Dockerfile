FROM php:7.3-alpine

ARG SYMFONY_VERSION=4.4
ARG LIBRARY_BRANCH=master


# INSTALLING COMPOSER
COPY --from=composer:1.9.0 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache jq

WORKDIR /test-app

#Create Symfony project
RUN composer create-project symfony/website-skeleton:^${SYMFONY_VERSION} /symfony-skeleton

#Create zipkin folder
RUN mkdir .zipkin-instrumentation-symfony

COPY entrypoint.sh /entrypoint.sh

EXPOSE 8002

ENTRYPOINT ["/entrypoint.sh"]

CMD ["bin/console", "server:run",  "0.0.0.0:8002"]