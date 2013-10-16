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
    ->newTable($installer->getTable('postnl/shipment'))
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
    ->addColumn('barcode', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'unsigned'  => true,
        ), 'Barcode')
    ->addColumn('confirm_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Confirm Date')
    ->addColumn('confirm_status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        ), 'Confirm Status')
    ->addColumn('shipping_status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => true,
        ), 'Shipping Status')
    ->addColumn('product_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'unsigned'  => true,
        ), 'Product Code')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'unsigned'  => true,
        ), 'Label')
    ->addIndex($installer->getIdxName('postnl/shipment', array('shipment_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
        array('shipment_id'), 
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('postnl/shipment', array('barcode'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), 
        array('barcode'), 
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('postnl/shipment', 'shipment_id', 'sales/shipment', 'entity_id'),
        'shipment_id', $installer->getTable('sales/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment');

$installer->getConnection()->createTable($postnlShipmentTable);

$installer->endSetup();