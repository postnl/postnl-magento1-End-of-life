#!/bin/bash
set -e
set -x

TMPNAME=`openssl rand -base64 32 | tr -cd '[:alnum:]' | head -c8`;

if [ -z $MAGENTO_DB_HOST ]; then MAGENTO_DB_HOST="localhost"; fi
if [ -z $MAGENTO_DB_PORT ]; then MAGENTO_DB_PORT="3306"; fi
if [ -z $MAGENTO_DB_USER ]; then MAGENTO_DB_USER="root"; fi
if [ -z $MAGENTO_DB_PASS ]; then MAGENTO_DB_PASS=""; fi
if [ -z $MAGENTO_DB_ALLOWSAME ]; then MAGENTO_DB_ALLOWSAME="0"; fi
if [ ! -z $PHP_VERSION ]; then phpenv global $PHP_VERSION; fi
if [ -z $MAGENTO_DB_NAME ]; then
    MAGENTO_DB_NAME="magento_${TMPNAME}";
fi


CURRENT_DIR=`pwd`
BUILDENV="/tmp/magento.${TMPNAME}"
mkdir -p ${BUILDENV}
TOOLS="${CURRENT_DIR}/tools"
PUBLIC_DIR="${BUILDENV}/public/"

mkdir -p "${TOOLS}"
mkdir -p "${PUBLIC_DIR}"

if [ ! -f "${TOOLS}/n98-magerun" ]; then
    curl https://files.magerun.net/n98-magerun.phar -o "${TOOLS}/n98-magerun"
    chmod +x "${TOOLS}/n98-magerun"
fi

if [ ! -f "${TOOLS}/modman" ]; then
    curl https://raw.githubusercontent.com/colinmollenhour/modman/master/modman -o "${TOOLS}/modman"
    chmod +x "${TOOLS}/modman"
fi

if [ ! -f "${TOOLS}/phpunit" ]; then
    wget https://phar.phpunit.de/phpunit-old.phar -O "${TOOLS}/phpunit"
    chmod +x "${TOOLS}/phpunit"
fi

echo "Using build directory ${BUILDENV}"

echo "Installing Magento version ${MAGENTO_VERSION}"

# Create main database
MYSQLPASS=""
if [ ! -z $MAGENTO_DB_PASS ]; then MYSQLPASS="-p${MAGENTO_DB_PASS}"; fi
mysql -u${MAGENTO_DB_USER} ${MYSQLPASS} -h${MAGENTO_DB_HOST} -P${MAGENTO_DB_PORT} -e "DROP DATABASE IF EXISTS \`${MAGENTO_DB_NAME}\`; CREATE DATABASE \`${MAGENTO_DB_NAME}\`;"

"${TOOLS}/n98-magerun" install \
      --dbHost="${MAGENTO_DB_HOST}" --dbUser="${MAGENTO_DB_USER}" --dbPass="${MAGENTO_DB_PASS}" --dbName="${MAGENTO_DB_NAME}" --dbPort="${MAGENTO_DB_PORT}" \
      --installSampleData=no \
      --useDefaultConfigParams=yes \
      --magentoVersionByName="${MAGENTO_VERSION}" \
      --installationFolder="${PUBLIC_DIR}" \
      --baseUrl="http://magento.local/" || { echo "Installing Magento failed"; exit 1; }

mkdir -p "${PUBLIC_DIR}/.modman/project"

cp -rf . "${PUBLIC_DIR}/.modman/project"

cd "${PUBLIC_DIR}"

"${TOOLS}/modman" deploy-all
"${TOOLS}/n98-magerun" config:set dev/template/allow_symlink 1
"${TOOLS}/n98-magerun" sys:setup:run

if [ -z $ENABLE_FLAT_CATALOG ] || [ $ENABLE_FLAT_CATALOG == false ]; then
    "${TOOLS}/n98-magerun" config:set catalog/frontend/flat_catalog_category 0
    "${TOOLS}/n98-magerun" config:set catalog/frontend/flat_catalog_product 0
else
    "${TOOLS}/n98-magerun" config:set catalog/frontend/flat_catalog_category 1
    "${TOOLS}/n98-magerun" config:set catalog/frontend/flat_catalog_product 1
    "${TOOLS}/n98-magerun" index:reindex:all
fi

cd "${PUBLIC_DIR}/.modman/project";
mkdir -p "${PUBLIC_DIR}var/session";
chmod -R 777 "${PUBLIC_DIR}var/session";

"${TOOLS}/phpunit" -c "${PUBLIC_DIR}/app/code/community/TIG/PostNL/Test/phpunit.xml"

mysql -u${MAGENTO_DB_USER} ${MYSQLPASS} -h${MAGENTO_DB_HOST} -P${MAGENTO_DB_PORT} -e "DROP DATABASE IF EXISTS \`${MAGENTO_DB_NAME}\`;"
echo "Deleting ${BUILDENV}"
rm -rf "${BUILDENV}"