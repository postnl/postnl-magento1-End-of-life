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
 * ORDER
 **********************************************************************************************************************/
/**
 * Add PostNL COD fee columns to sales/order
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
 * Add PostNL COD fee columns to sales/order_invoice
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
 * Add PostNL COD fee columns to sales/quote
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
 * Add PostNL COD fee columns to sales/quote_address
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
 * Add PostNL COD fee columns to sales/creditmemo
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

$installer->endSetup();