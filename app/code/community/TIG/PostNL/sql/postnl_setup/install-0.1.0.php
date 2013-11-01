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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
 
$installer = $this;

$installer->startSetup();

$postnlShipmentTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Shipment Id')
    ->addColumn('main_barcode', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Main Barcode')
    ->addColumn('confirm_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Confirm Date')
    ->addColumn('confirm_status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Confirm Status')
    ->addColumn('shipping_phase', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Shipping Phase')
    ->addColumn('product_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Product Code')
    ->addIndex($installer->getIdxName('postnl_core/shipment', array('shipment_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
        array('shipment_id'), 
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('postnl_core/shipment', array('main_barcode'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
        array('main_barcode'), 
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('postnl_core/shipment', 'shipment_id', 'sales/shipment', 'entity_id'),
        'shipment_id', $installer->getTable('sales/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment');

$installer->getConnection()->createTable($postnlShipmentTable);

$postnlShipmentLabelTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment_label'))
    ->addColumn('label_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Label Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        ), 'Label')
    ->addColumn('label_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Label Type')
    ->addIndex($installer->getIdxName('postnl_core/shipment_label', array('parent_id')), 
        array('parent_id'))
    ->addForeignKey($installer->getFkName('postnl_core/shipment_label', 'parent_id', 'postnl_core/shipment', 'entity_id'),
        'parent_id', $installer->getTable('postnl_core/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment Label');

$installer->getConnection()->createTable($postnlShipmentLabelTable);

$installer->endSetup();