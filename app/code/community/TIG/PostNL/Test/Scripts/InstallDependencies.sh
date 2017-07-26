#!/bin/bash

#set -e
set -x

COMPOSER_REQUIRE="";

which n98-magerun
if [ $? != "0" ]; then
    COMPOSER_REQUIRE="${COMPOSER_REQUIRE} n98/magerun"
fi

which modman
if [ $? != "0" ]; then
    COMPOSER_REQUIRE="${COMPOSER_REQUIRE} colinmollenhour/modman"
fi

which coveralls
if [ $? != "0" ] && [ "${CODE_COVERAGE}" = "true" ]; then
    COMPOSER_REQUIRE="${COMPOSER_REQUIRE} satooshi/php-coveralls"
fi

if [ ! -f "${COMPOSER_HOME}phpunit" ]; then
    COMPOSER_REQUIRE="${COMPOSER_REQUIRE} phpunit/phpunit 4.8.*"
fi

if [ ! -z "${COMPOSER_REQUIRE}" ]; then
    composer global require ${COMPOSER_REQUIRE}
else
    echo "All dependencies installed"
fi

# Imagick is only available on 5.4 and higher.
if [[ ${TRAVIS_PHP_VERSION:0:3} != "5.3" ]]; then
    # Imagick is only used for testing purposes. It is not a dependency.
    printf "\n" | pecl install imagick
fi
