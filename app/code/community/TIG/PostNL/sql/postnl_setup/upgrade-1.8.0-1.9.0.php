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
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

/***********************************************************************************************************************
 * POSTNL ORDER
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/order');

if (!$conn->tableColumnExists($tableName, 'pg_location_code')) {
    $conn->addColumn(
        $tableName,
        'pg_location_code',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'PakjeGemak Location Code',
            'after'    => 'expected_delivery_time_end',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'pg_retail_network_id')) {
    $conn->addColumn(
        $tableName,
        'pg_retail_network_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'PakjeGemak Retail Network ID',
            'after'    => 'pg_location_code',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL SHIPMENT
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/shipment');

if (!$conn->tableColumnExists($tableName, 'pg_location_code')) {
    $conn->addColumn(
        $tableName,
        'pg_location_code',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'PakjeGemak Location Code',
            'after'    => 'treat_as_abandoned',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'pg_retail_network_id')) {
    $conn->addColumn(
        $tableName,
        'pg_retail_network_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'PakjeGemak Retail Network ID',
            'after'    => 'pg_location_code',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL MATRIX RATES
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_carrier/matrixrate');

$conn->modifyColumn(
    $tableName,
    'parcel_type',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'default'  => '*',
        'length'   => 16,
    )
);

/***********************************************************************************************************************
 * PRODUCT ATTRIBUTES
 **********************************************************************************************************************/

$applyTo = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
);

/**
 * Add PostNL product type attribute to products.
 */
if (!$installer->getAttribute('catalog_product', 'postnl_product_type')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_product_type',
        array(
            'backend'                    => '',
            'group'                      => 'PostNL',
            'sort_order'                 => 170,
            'frontend'                   => '',
            'frontend_class'             => '',
            'default'                    => '0',
            'label'                      => 'PostNL product type',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'postnl_deliveryoptions/product_attribute_source_productType',
            'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'                    => true,
            'required'                   => false,
            'searchable'                 => false,
            'filterable'                 => false,
            'filterable_in_search'       => false,
            'unique'                     => false,
            'comparable'                 => false,
            'visible_on_front'           => false,
            'visible_in_advanced_search' => false,
            'is_html_allowed_on_front'   => false,
            'used_in_product_listing'    => false,
            'user_defined'               => false,
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
            'apply_to'                   => implode(',', $applyTo),
        )
    );
}

$installer->endSetup();
