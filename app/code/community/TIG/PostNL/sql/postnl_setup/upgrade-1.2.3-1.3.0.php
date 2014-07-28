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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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

$conn->addColumn($installer->getTable('postnl_core/shipment'),
    'order_id',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'length'   => 10,
        'nullable' => true,
        'comment'  => 'Order Id',
        'after'    => 'shipment_id',
    )
);

$conn->addColumn($installer->getTable('postnl_core/shipment'),
    'shipment_type',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 32,
        'nullable' => true,
        'comment'  => 'Shipment Type',
        'after'    => 'product_code',
    )
);

$conn->addColumn($installer->getTable('postnl_core/shipment'),
    'is_buspakje',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => true,
        'comment'  => 'Is Buspakje',
        'after'    => 'is_pakketautomaat',
    )
);

$conn->addIndex(
    $installer->getTable('postnl_core/shipment'),
    $installer->getIdxName($installer->getTable('postnl_core/shipment'), array('order_id')),
    'order_id'
);

$conn->addForeignKey(
    $installer->getFkName('postnl_core/shipment', 'order_id', 'sales/order', 'entity_id'),
    $installer->getTable('postnl_core/shipment'),
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

$conn->addColumn($installer->getTable('postnl_core/order'),
    'created_at',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'comment'  => 'Created At',
        'after'    => 'quote_id',
    )
);

$conn->addColumn($installer->getTable('postnl_core/order'),
    'updated_at',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'comment'  => 'Updated At',
        'after'    => 'created_at',
    )
);

/**
 * Modify the shipment_costs column so it conforms to Magento's standard format for storing costs.
 */
$conn->modifyColumn(
    $installer->getTable('postnl_core/order'),
    'shipment_costs',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'   => '12,4',
        'nullable' => true,
        'default'  => 0,
        'comment'  => 'Shipment Costs',
    )
);

/**
 * Update the PostNL order table so that a PostNL order is deleted when its corresponding Magento order is deleted. This
 * prevents errors caused by missing IDs.
 */
$conn->addForeignKey(
    $installer->getFkName('postnl_core/order', 'order_id', 'sales/order', 'entity_id'),
    $installer->getTable('postnl_core/order'),
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

/***********************************************************************************************************************
 * INVOICE
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/order_invoice.
 */
$salesInvoiceTable = $installer->getTable('sales/invoice');
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

/***********************************************************************************************************************
 * QUOTE
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/quote.
 */
$salesQuoteTable = $installer->getTable('sales/quote');
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

/***********************************************************************************************************************
 * QUOTE ADDRESS
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/quote_address.
 */
$salesQuoteAddressTable = $installer->getTable('sales/quote_address');
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

/***********************************************************************************************************************
 * CREDITMEMO
 **********************************************************************************************************************/

/**
 * Add PostNL COD fee columns to sales/creditmemo.
 */
$salesCreditmemoTable = $installer->getTable('sales/creditmemo');
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

/***********************************************************************************************************************
 * PRODUCT ATTRIBUTES
 **********************************************************************************************************************/

if (!$installer->getAttribute('catalog_product', 'postnl_max_qty_for_buspakje')) {
    $installer->addAttribute(
        'catalog_product',
        'postnl_max_qty_for_buspakje',
        array(
            'backend'                    => 'catalog/product_attribute_backend_boolean',
            'group'                      => 'General',
            'sort_order'                 => 110,
            'frontend'                   => '',
            'frontend_class'             => 'validate-digits',
            'default'                    => '0',
            'label'                      => 'PostNL Max Qty For Letter Box Parcels',
            'input'                      => 'text',
            'type'                       => 'int',
            'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
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