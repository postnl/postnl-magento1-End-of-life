<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 * @var TIG_PostNL_Model_Resource_Setup $this
 */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

/***********************************************************************************************************************
 * PRODUCT ATTRIBUTES
 **********************************************************************************************************************/

$applyTo = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
);

$tableName = $installer->getTable('postnl_core/order');

if (!$conn->tableColumnExists($tableName, 'idcheck_type')) {
    $conn->addColumn(
        $tableName,
        'idcheck_type',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => '32',
            'nullable' => true,
            'comment' => 'ID Check type',
            'after'    => 'pg_retail_network_id'
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'idcheck_number')) {
    $conn->addColumn(
        $tableName,
        'idcheck_number',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => '1000',
            'nullable' => true,
            'comment' => 'ID Check number',
            'after'    => 'idcheck_type'
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'idcheck_expiration_date')) {
    $conn->addColumn(
        $tableName,
        'idcheck_expiration_date',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_DATE,
            'nullable' => true,
            'comment' => 'ID Check expiration date',
            'after'    => 'idcheck_number'
        )
    );
}

$tableName = $installer->getTable('postnl_core/shipment');

if (!$conn->tableColumnExists($tableName, 'idcheck_type')) {
    $conn->addColumn(
        $tableName,
        'idcheck_type',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => '32',
            'nullable' => true,
            'comment' => 'ID Check type',
            'after'    => 'pg_retail_network_id'
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'idcheck_number')) {
    $conn->addColumn(
        $tableName,
        'idcheck_number',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => '1000',
            'nullable' => true,
            'comment' => 'ID Check number',
            'after'    => 'idcheck_type'
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'idcheck_expiration_date')) {
    $conn->addColumn(
        $tableName,
        'idcheck_expiration_date',
        array(
            'type'   => Varien_Db_Ddl_Table::TYPE_DATE,
            'nullable' => true,
            'comment' => 'ID Check expiration date',
            'after'    => 'idcheck_number'
        )
    );
}

/***********************************************************************************************************************
 * ADD PRODUCT CODES
 **********************************************************************************************************************/

$productCodes = array(
    '3440',
    '3444',
    '3447',
    '3450',
    '3442',
    '3445',
    '3448',
    '3451',
    '3437',
    '3438',
    '3443',
    '3446',
    '3449',
    '3571',
    '3572',
    '3573',
    '3574',
    '3575',
    '3576',
    '3581',
    '3582',
    '3583',
    '3584',
    '3585',
    '3586',
);

$this->addSupportedProductCode($productCodes);

/***********************************************************************************************************************
 * DISABLE THE POSTNL CHECKOUT
 **********************************************************************************************************************/

$this->resetConfig(array(
    'postnl/checkout/active',
    'postnl/checkout_payment_methods/activate_belgium',
    'postnl/checkout_payment_methods/ideal',
    'postnl/checkout_payment_methods/creditcard',
    'postnl/checkout_payment_methods/checkpay',
    'postnl/checkout_payment_methods/paypal',
    'postnl/checkout_payment_methods/directdebit',
    'postnl/checkout_payment_methods/acceptgiro',
    'postnl/checkout_payment_methods/vooraf_betalen',
    'postnl/checkout_payment_methods/termijnen',
    'postnl/checkout_payment_methods/giftcard',
    'postnl/checkout_payment_methods/rabobank_internetkassa',
    'postnl/checkout_payment_methods/afterpay',
    'postnl/checkout_payment_methods/klarna',
    'postnl/delivery_options/mijnpakket_login_active',
    'postnl/delivery_options/mijnpakket_notification',
    'postnl/delivery_options/show_create_mijnpakket_account_link',
    'postnl/delivery_options/show_mijnpakket_app_link',
));

$installer->endSetup();
