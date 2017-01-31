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

if [ ! -z "${COMPOSER_REQUIRE}" ]; then
    composer global require ${COMPOSER_REQUIRE}
else
    echo "All dependencies installed"
fi;