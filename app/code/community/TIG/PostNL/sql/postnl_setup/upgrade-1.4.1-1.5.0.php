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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
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

if (!$conn->tableColumnExists($tableName, 'expected_delivery_time_start')) {
    $conn->addColumn(
        $tableName,
        'expected_delivery_time_start',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'Expected Delivery Time Start',
            'after'    => 'delivery_date',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'expected_delivery_time_end')) {
    $conn->addColumn(
        $tableName,
        'expected_delivery_time_end',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'Expected Delivery Time End',
            'after'    => 'expected_delivery_time_start',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL SHIPMENT
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/shipment');

if (!$conn->tableColumnExists($tableName, 'return_labels_printed')) {
    $conn->addColumn(
        $tableName,
        'return_labels_printed',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'nullable' => false,
            'default'  => '0',
            'comment'  => 'Return labels Printed',
            'after'    => 'labels_printed',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'expected_delivery_time_start')) {
    $conn->addColumn(
        $tableName,
        'expected_delivery_time_start',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'Expected Delivery Time Start',
            'after'    => 'delivery_date',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'expected_delivery_time_end')) {
    $conn->addColumn(
        $tableName,
        'expected_delivery_time_end',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'Expected Delivery Time End',
            'after'    => 'expected_delivery_time_start',
        )
    );
}

if (!$conn->tableColumnExists($tableName, 'return_phase')) {
    $conn->addColumn(
        $tableName,
        'return_phase',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => '2',
            'nullable' => true,
            'unsigned' => true,
            'comment'  => 'Return Phase',
            'after'    => 'shipping_phase',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL SHIPMENT BARCODE
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/shipment_barcode');

if (!$conn->tableColumnExists($tableName, 'barcode_type')) {
    $conn->addColumn(
        $tableName,
        'barcode_type',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'comment'  => 'Barcode Type',
            'after'    => 'parent_id',
        )
    );
}

if ($conn->tableColumnExists($tableName, 'barcode_number')) {
    $conn->modifyColumn(
        $tableName,
        'barcode_number',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'length'   => '5',
            'nullable' => true,
            'comment'  => 'Barcode Number',
            'unsigned' => true,
        )
    );
}

/***********************************************************************************************************************
 * POSTNL SHIPMENT STATUS HISTORY
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/shipment_status_history');

if (!$conn->tableColumnExists($tableName, 'return_labels_printed')) {
    $conn->addColumn(
        $tableName,
        'shipment_type',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '16',
            'nullable' => true,
            'default'  => 'shipment',
            'comment'  => 'Shipment Type',
            'after'    => 'parent_id',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL INTEGRITY
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/integrity');

if (!$conn->isTableExists($tableName)) {
    $table = $installer->getConnection()
                       ->newTable($tableName);

    $table->addColumn(
              'integrity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'unsigned' => true,
                  'nullable' => false,
                  'primary'  => true,
              ), 'Primary key'
          )
          ->addColumn(
              'entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
                  'nullable' => false,
              ), 'Entity Type'
          )
          ->addColumn(
              'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'nullable' => false,
              ), 'Entity Id'
          )
          ->addColumn(
              'error_code', Varien_Db_Ddl_Table::TYPE_TEXT, 11, array(
                  'nullable' => false,
                  'default'  => '0',
              ), 'Error Code'
          )
          ->setComment('PostNL Integrity');

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
