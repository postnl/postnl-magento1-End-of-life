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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

/***********************************************************************************************************************
 * POSTNL SHIPMENT
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/shipment');

if (!$conn->tableColumnExists($tableName, 'order_id')) {
    $conn->addColumn(
        $tableName,
        'order_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => 10,
            'nullable' => true,
            'comment'  => 'Order Id',
            'after'    => 'shipment_id',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'shipment_type')) {
    $conn->addColumn(
        $tableName,
        'shipment_type',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 32,
            'nullable' => true,
            'comment'  => 'Shipment Type',
            'after'    => 'product_code',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'is_buspakje')) {
    $conn->addColumn(
        $tableName,
        'is_buspakje',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'nullable' => true,
            'comment'  => 'Is Buspakje',
            'after'    => 'is_pakketautomaat',
        )
    );
}

$conn->addIndex(
    $tableName,
    $installer->getIdxName($installer->getTable('postnl_core/shipment'), array('order_id')),
    'order_id'
);

$conn->addForeignKey(
    $installer->getFkName('postnl_core/shipment', 'order_id', 'sales/order', 'entity_id'),
    $tableName,
    'order_id',
    $installer->getTable('sales/order'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, //on delete cascade
    Varien_Db_Ddl_Table::ACTION_CASCADE //on update cascade
);

/**
 * Update the PostNL shipment table so that a PostNl shipment is deleted when its corresponding Magento shipment is
 * deleted. This prevents errors caused by missing IDs.
 */
$conn->addForeignKey(
    $installer->getFkName('postnl_core/shipment', 'shipment_id', 'sales/shipment', 'entity_id'),
    $installer->getTable('postnl_core/shipment'),
    'shipment_id',
    $installer->getTable('sales/shipment'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, //on delete cascade
    Varien_Db_Ddl_Table::ACTION_CASCADE //on update cascade
);

/***********************************************************************************************************************
 * POSTNL ORDER
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/order');

if (!$conn->tableColumnExists($tableName, 'created_at')) {
    $conn->addColumn(
        $tableName,
        'created_at',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => true,
            'comment'  => 'Created At',
            'after'    => 'quote_id',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'updated_at')) {
    $conn->addColumn(
        $tableName,
        'updated_at',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            'nullable' => true,
            'comment'  => 'Updated At',
            'after'    => 'created_at',
        )
    );
}

/**
 * Modify the shipment_costs column so it conforms to Magento's standard format for storing costs.
 */
if ($conn->tableColumnExists($tableName, 'shipment_costs')) {
    $conn->modifyColumn(
        $tableName,
        'shipment_costs',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length'   => '12,4',
            'nullable' => true,
            'default'  => 0,
            'comment'  => 'Shipment Costs',
        )
    );
}

/**
 * Update the PostNL order table so that a PostNL order is deleted when its corresponding Magento order is deleted. This
 * prevents errors caused by missing IDs.
 */
$conn->addForeignKey(
    $installer->getFkName('postnl_core/order', 'order_id', 'sales/order', 'entity_id'),
    $tableName,
    'order_id',
    $installer->getTable('sales/order'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, //on delete cascade
    Varien_Db_Ddl_Table::ACTION_CASCADE //on update cascade
);

/***********************************************************************************************************************
 * ORDER
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/order.
 */
$salesOrderTable = $installer->getTable('sales/order');

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee',
            'after'    => 'base_shipping_tax_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Invoiced',
            'after'    => 'base_postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Refunded',
            'after'    => 'base_postnl_cod_fee_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax',
            'after'    => 'base_postnl_cod_fee_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee_tax_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee_tax_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax Invoiced',
            'after'    => 'base_postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_postnl_cod_fee_tax_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_postnl_cod_fee_tax_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax Refunded',
            'after'    => 'base_postnl_cod_fee_tax_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee',
            'after'    => 'shipping_tax_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Invoiced',
            'after'    => 'postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Refunded',
            'after'    => 'postnl_cod_fee_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax',
            'after'    => 'postnl_cod_fee_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee_tax_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee_tax_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax Invoiced',
            'after'    => 'postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'postnl_cod_fee_tax_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'postnl_cod_fee_tax_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax Refunded',
            'after'    => 'postnl_cod_fee_tax_invoiced',
        )
    );
}

/***********************************************************************************************************************
 * INVOICE
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/order_invoice.
 */
$salesInvoiceTable = $installer->getTable('sales/invoice');

if (!$conn->tableColumnExists($salesInvoiceTable, 'base_postnl_cod_fee')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'base_postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee',
            'after'    => 'base_shipping_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'base_postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'base_postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax',
            'after'    => 'base_postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'postnl_cod_fee')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee',
            'after'    => 'base_postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax',
            'after'    => 'postnl_cod_fee',
        )
    );
}

/***********************************************************************************************************************
 * QUOTE
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/quote.
 */
$salesQuoteTable = $installer->getTable('sales/quote');

if (!$conn->tableColumnExists($salesQuoteTable, 'base_postnl_cod_fee')) {
    $conn->addColumn(
        $salesQuoteTable,
        'base_postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee',
            'after'    => 'customer_gender',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'base_postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesQuoteTable,
        'base_postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax',
            'after'    => 'base_postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'postnl_cod_fee')) {
    $conn->addColumn(
        $salesQuoteTable,
        'postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee',
            'after'    => 'base_postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesQuoteTable,
        'postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax',
            'after'    => 'postnl_cod_fee',
        )
    );
}

/***********************************************************************************************************************
 * QUOTE ADDRESS
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/quote_address.
 */
$salesQuoteAddressTable = $installer->getTable('sales/quote_address');

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'base_postnl_cod_fee')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'base_postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee',
            'after'    => 'base_shipping_tax_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'base_postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'base_postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax',
            'after'    => 'base_postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'postnl_cod_fee')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee',
            'after'    => 'base_postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax',
            'after'    => 'postnl_cod_fee',
        )
    );
}

/***********************************************************************************************************************
 * CREDITMEMO
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/creditmemo.
 */
$salesCreditmemoTable = $installer->getTable('sales/creditmemo');

if (!$conn->tableColumnExists($salesCreditmemoTable, 'base_postnl_cod_fee')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'base_postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee',
            'after'    => 'shipping_tax_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'base_postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'base_postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base PostNL COD Fee Tax',
            'after'    => 'base_postnl_cod_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'postnl_cod_fee')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'postnl_cod_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee',
            'after'    => 'base_postnl_cod_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'postnl_cod_fee_tax')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'postnl_cod_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'PostNL COD Fee Tax',
            'after'    => 'postnl_cod_fee',
        )
    );
}

/***********************************************************************************************************************
 * PRODUCT ATTRIBUTES
 **********************************************************************************************************************/

$entityType = Mage_Catalog_Model_Product::ENTITY;

$attributesToMove = array(
    'postnl_shipping_duration',
);

$attributeSets = $installer->getAllAttributeSetIds($entityType);
foreach ($attributesToMove as $attributeCode) {
    foreach ($attributeSets as $attributeSet) {
        $installer->addAttributeGroup($entityType, $attributeSet, 'PostNL', 40);

        $attributeGroupId = $installer->getAttributeGroupId($entityType, $attributeSet, 'PostNL');

        $installer->addAttributeToGroup($entityType, $attributeSet, $attributeGroupId, $attributeCode);
    }
}

$applyTo = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
);

if (!$installer->getAttribute('catalog_product', 'postnl_allow_delivery_options')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_allow_delivery_options',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'PostNL',
            'sort_order'                 => 110,
            'frontend'                   => '',
            'class'                      => '',
            'default'                    => '1',
            'label'                      => 'PostNL Allow Delivery Options',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'eav/entity_attribute_source_boolean',
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

if (!$installer->getAttribute('catalog_product', 'postnl_allow_delivery_days')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_allow_delivery_days',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'PostNL',
            'sort_order'                 => 120,
            'frontend'                   => '',
            'frontend_class'             => '',
            'default'                    => '1',
            'label'                      => 'PostNL Delivery Options - Allow Delivery Days',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'eav/entity_attribute_source_boolean',
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
            'apply_to'                   => implode(',', $applyTo),
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

if (!$installer->getAttribute('catalog_product', 'postnl_allow_timeframes')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_allow_timeframes',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'PostNL',
            'sort_order'                 => 130,
            'frontend'                   => '',
            'frontend_class'             => '',
            'default'                    => '1',
            'label'                      => 'PostNL Delivery Options - Allow Time Frames',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'eav/entity_attribute_source_boolean',
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
            'apply_to'                   => implode(',', $applyTo),
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

if (!$installer->getAttribute('catalog_product', 'postnl_allow_pakje_gemak')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_allow_pakje_gemak',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'PostNL',
            'sort_order'                 => 140,
            'frontend'                   => '',
            'frontend_class'             => '',
            'default'                    => '1',
            'label'                      => 'PostNL Delivery Options - Allow Post Office Locations',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'eav/entity_attribute_source_boolean',
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
            'apply_to'                   => implode(',', $applyTo),
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

if (!$installer->getAttribute('catalog_product', 'postnl_allow_pakketautomaat')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_allow_pakketautomaat',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'PostNL',
            'sort_order'                 => 150,
            'frontend'                   => '',
            'frontend_class'             => '',
            'default'                    => '1',
            'label'                      => 'PostNL Delivery Options - Allow Parcel Dispensers',
            'input'                      => 'select',
            'type'                       => 'int',
            'source'                     => 'eav/entity_attribute_source_boolean',
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
            'apply_to'                   => implode(',', $applyTo),
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

if (!$installer->getAttribute('catalog_product', 'postnl_max_qty_for_buspakje')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_max_qty_for_buspakje',
        array(
            'group'                      => 'PostNL',
            'sort_order'                 => 160,
            'frontend'                   => '',
            'frontend_class'             => 'validate-digits',
            'default'                    => '0',
            'label'                      => 'PostNL Max Qty For Letter Box Parcels',
            'note'                       => 'Een zending zal enkel als brievenbuspakje verwerkt worden indien het ' .
                                            'bestelde aantal van dit product niet deze waarde overschrijdt.',
            'input'                      => 'text',
            'type'                       => 'varchar',
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
            'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'is_configurable'            => false,
            'used_for_sort_by'           => false,
            'position'                   => 0,
            'used_for_promo_rules'       => false,
        )
    );
}

$installer->endSetup();