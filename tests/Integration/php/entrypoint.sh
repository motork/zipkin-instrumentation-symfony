#!/bin/sh
set -e

# Includes zipkin-instrumentation-symfony to the composer.json of the app
cp /symfony-skeleton/composer.json composer.json.dist
cat composer.json.dist \
| jq '. + {"minimum-stability": "dev"}' \
| jq '. + {"prefer-stable": true}' \
| jq '.require["jcchavezs/zipkin-instrumentation-symfony"] = "*"' \
| jq '.repositories = [{"type": "path","url": "/.zipkin-instrumentation-symfony","options": {"symlink": true}}]' > composer.json

echo "cat composer.json"
cat composer.json

rm composer.lock || true

echo "Installing web-server-bundle"
# web-server-bundle:4.4 supports ^3.4, ^4.0 and ^5.0 (see https://github.com/symfony/web-server-bundle/blob/4.4/composer.json#L23)
php -d memory_limit=-1 /usr/bin/composer require symfony/web-server-bundle:"^${SYMFONY_VERSION}|^4.4" --dev

# includes configuration files to run the middleware in the app
mv ./config/services.yaml ./config/services.yaml.dist
echo "imports: [{ resource: tracing.yaml }]" > ./config/services.yaml
cat ./config/services.yaml.dist >> ./config/services.yaml

exec docker-php-entrypoint "$@"