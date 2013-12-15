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
    /**
     * Entity ID
     */
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    /**
     * Mage_Sales_Model_Order_Shipment ID
     */
    ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Shipment Id')
    /**
     * Created at timestamp
     */
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    /**
     * Updated at timestamp
     */
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Updated At')
    /**
     * Confirmed at timestamp
     */
    ->addColumn('confirmed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Confirmed At')
    /**
     * Status history updated at timestamp
     */
    ->addColumn('status_history_updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Status History Updated At')
    /**
     * Main barcode - this is a shipment's primary identifier in CIF
     */
    ->addColumn('main_barcode', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Main Barcode')
    /**
     * The date at which this shipment should be confirmed
     */
    ->addColumn('confirm_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Confirm Date')
    /**
     * the shipment's current confirm status
     */
    ->addColumn('confirm_status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Confirm Status')
    /**
     * The shipment's current shipping phase
     */
    ->addColumn('shipping_phase', Varien_Db_Ddl_Table::TYPE_INTEGER, 2, array(
        'unsigned' => true,
        ), 'Shipping Phase')
    /**
     * The shipment's product code - used to determine a shipment's option's such as extra cover, signature required etc.
     */
    ->addColumn('product_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Product Code')
    /**
     * Flag that determines whether or not this is a PakjeGemak shipment
     */
    ->addColumn('is_pakje_gemak', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Is PakjeGemak')
    /**
     * The shipment's shipment type such as Commercial Goods, Gifts, Commercial sample etc. Only used for international shipments
     */
    ->addColumn('shipment_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => true,
        ), 'Shipment Type')
    /**
     * The number of parcels in this shipment
     */
    ->addColumn('parcel_count', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
        'unsigned' => true,
        'default'  => 1,
        ), 'Parcel Count')
    /**
     * The optional amount of extra cover this shipment has
     */
    ->addColumn('extra_cover_amount', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable' => true,
        ), 'Extra Cover Amount')
    /**
     * The optional treat_as_abandoned flag. This is only used for international shipments
     */
    ->addColumn('treat_as_abandoned', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Treat As Abandoned')
    /**
     * Whether or not this shipment's labels have been printed
     */
    ->addColumn('labels_printed', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Labels Printed')
    /**
     * Whether or not a track&trace e-mail has been sent to the customer for this shipment
     */
    ->addColumn('track_and_trace_email_sent', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Track And Trace Email Sent')
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

$postnlShipmentBarcodeTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment_barcode'))
    /**
     * Barcode ID
     */
    ->addColumn('barcode_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Barcode Id')
    /**
     * TIG_PostNL_Model_Core_Shipment ID
     */
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Id')
    /**
     * This barcode's number (related to the number of parcels in the shipment)
     */
    ->addColumn('barcode_number', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Barcode Number')
    /**
     * The actual barcode. Used by CIF to identify a shipment
     */
    ->addColumn('barcode', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Barcode')
    ->addIndex($installer->getIdxName('postnl_core/shipment_barcode', array('parent_id')), 
        array('parent_id'))
    ->addForeignKey($installer->getFkName('postnl_core/shipment_barcode', 'parent_id', 'postnl_core/shipment', 'entity_id'),
        'parent_id', $installer->getTable('postnl_core/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment Barcode');

$installer->getConnection()->createTable($postnlShipmentBarcodeTable);

$postnlShipmentLabelTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment_label'))
    /**
     * Label ID
     */
    ->addColumn('label_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Label Id')
    /**
     * TIG_PostNL_Model_Core_Shipment ID
     */
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Id')
    /**
     * A binary PDF file stored as a medium BLOB containing the actual label
     */
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_BLOB, 16777216 /* 2^24 (medium blob) */, array(
        'nullable'  => false,
        ), 'Label')
    /**
     * The type of label. This is used to determine how the label should be printed (what size and orientation)
     */
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

$postnlShipmentStatusHistoryTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment_status_history'))
    /**
     * Status ID
     */
    ->addColumn('status_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Status Id')
    /**
     * TIG_PostNL_Model_Core_Shipment ID
     */
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Id')
    /**
     * The status's code (an internal PostNL identifier)
     */
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 4, array(
        'nullable'  => false,
        ), 'Code')
    /**
     * The status's description. This should be human-readable
     */
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Description')
    /**
     * The status's location code
     */
    ->addColumn('location_code', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
        'nullable'  => true,
        ), 'Location Code')
    /**
     * The status's destination location code
     */
    ->addColumn('destination_location_code', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
        'nullable'  => true,
        ), 'Destination Location Code')
    /**
     * The status's route code
     */
    ->addColumn('route_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Route Code')
    /**
     * The status's route name
     */
    ->addColumn('route_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Route Name')
    /**
     * When the status was assigned by PostNL
     */
    ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Timestamp')
    ->addIndex($installer->getIdxName('postnl_core/shipment_status_history', array('parent_id')), 
        array('parent_id'))
    ->addForeignKey($installer->getFkName('postnl_core/shipment_status_history', 'parent_id', 'postnl_core/shipment', 'entity_id'),
        'parent_id', $installer->getTable('postnl_core/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment Status History');

$installer->getConnection()->createTable($postnlShipmentStatusHistoryTable);

$postnlOrderTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_checkout/order'))
    /**
     * Entity ID
     */
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    /**
     * Mage_Sales_Model_Order ID
     */
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Order Id')
    /**
     * Mage_Sales_Model_Quote ID
     */
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        ), 'Quote Id')
    /**
     * PostNL Checkout ordertoken used by PostNL to reference an order
     */
    ->addColumn('token', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Token')
    /**
     * Flag that determines whether or not this order is active
     */
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Is Active')
    /**
     * Flag that determines whether or not this order has been canceled
     */
    ->addColumn('is_canceled', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Is Canceled')
    /**
     * Optional product code required to ship PakjeGemak orders
     */
    ->addColumn('product_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => true,
        ), 'Product Code')
    /**
     * Flag that determines whether or not this is a PakjeGemak shipment
     */
    ->addColumn('is_pakje_gemak', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Is PakjeGemak')
    /**
     * Date on which the shipment has to be confirmed in order to be delivered on time
     */
    ->addColumn('confirm_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Confirm Date')
    /**
     * Date on which the delivery of the order should take place. Purely informational
     */
    ->addColumn('delivery_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Delivery Date')
    ->addIndex($installer->getIdxName('postnl_checkout/order', array('order_id')), 
        array('order_id'))
    ->addIndex($installer->getIdxName('postnl_checkout/order', array('quote_id')), 
        array('quote_id'))
    ->addForeignKey($installer->getFkName('postnl_checkout/order', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('postnl_checkout/order', 'quote_id', 'sales/quote', 'entity_id'),
        'quote_id', $installer->getTable('sales/quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Order');

$installer->getConnection()->createTable($postnlOrderTable);

$installer->endSetup();