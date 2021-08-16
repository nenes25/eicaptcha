#!/bin/bash
PS_VERSION=$1

set -e

# Docker images prestashop/prestashop may be used, even if the shop remains uninstalled
echo "Pull PrestaShop files (Tag ${PS_VERSION})"

docker rm -f temp-ps || true
docker volume rm -f ps-volume || true

docker run -tid --rm -v ps-volume:/var/www/html --name temp-ps prestashop/prestashop:$PS_VERSION

# Clear previous instance of the module in the PrestaShop volume
echo "Clear previous module"

docker exec -t temp-ps rm -rf /var/www/html/modules/eicaptcha

# Run a container for PHPStan, having access to the module content and PrestaShop sources.
# This tool is outside the composer.json because of the compatibility with PHP 5.6
echo "Run PHPStan using phpstan-${PS_VERSION}.neon file"

docker run --rm --volumes-from temp-ps \
       -v $PWD:/var/www/html/modules/eicaptcha \
       -e _PS_ROOT_DIR_=/var/www/html \
       --workdir=/var/www/html/modules/eicaptcha phpstan/phpstan:0.12 \
       analyse \
       --configuration=/var/www/html/modules/eicaptcha/tests/phpstan/phpstan-$PS_VERSION.neon