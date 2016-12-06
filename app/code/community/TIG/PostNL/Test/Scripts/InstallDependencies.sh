#!/bin/bash

#set -e
#set -x

COMPOSER_REQUIRE="";

which -s n98-magerun
if [ $? != "0" ]; then
    COMPOSER_REQUIRE="n98/magerun"
fi

which -s modman
if [ $? != "0" ]; then
    COMPOSER_REQUIRE="colinmollenhour/modman"
fi

if [ ! -z "${COMPOSER_REQUIRE}" ]; then
    composer global require "${COMPOSER_REQUIRE}"
else
    echo "All dependencies installed"
fi;