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
 * POSTNL ORDER
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_core/order');

if (!$conn->tableColumnExists($tableName, 'options')) {
    $conn->addColumn(
        $tableName,
        'options',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => '64k',
            'nullable' => true,
            'comment'  => 'Options',
        )
    );
}

/***********************************************************************************************************************
 * POSTNL MATRIXRATE
 **********************************************************************************************************************/

$tableName = $installer->getTable('postnl_carrier/matrixrate');

if (!$conn->isTableExists($tableName)) {
    $table = $installer->getConnection()
                       ->newTable($tableName);

    $table->addColumn(
              'pk', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'identity' => true,
                  'unsigned' => true,
                  'nullable' => false,
                  'primary'  => true,
              ), 'Primary key'
          )
          ->addColumn(
              'website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'nullable' => false,
                  'default'  => '0',
              ), 'Website Id'
          )
          ->addColumn(
              'dest_country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
                  'nullable' => false,
                  'default'  => '0',
              ), 'Destination country ISO/2 or ISO/3 code'
          )
          ->addColumn(
              'dest_region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                  'nullable' => false,
                  'default'  => '0',
              ), 'Destination Region Id'
          )
          ->addColumn(
              'dest_zip', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
                  'nullable' => false,
                  'default'  => '*',
              ), 'Destination Post Code (Zip)'
          )
          ->addColumn(
              'weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                  'nullable' => false,
                  'default'  => '0.0000',
              ), 'Minimum Order Weight'
          )
          ->addColumn(
              'subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                  'nullable' => false,
                  'default'  => '0.0000',
              ), 'Minimum Order Amount'
          )
          ->addColumn(
              'qty', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
                  'nullable' => false,
                  'default'  => '0',
              ), 'Minimum Quantity'
          )
          ->addColumn(
              'parcel_type', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
                  'nullable' => false,
                  'default'  => '*',
              ), 'Parcel Type (Letter Box Parcel or Regular)'
          )
          ->addColumn(
              'price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
                  'nullable' => false,
                  'default'  => '0.0000',
              ), 'Price'
          )
          ->addIndex(
              $installer->getIdxName(
                  'postnl_carrier/matrixrate',
                  array(
                      'website_id',
                      'dest_country_id',
                      'dest_region_id',
                      'dest_zip',
                      'weight',
                      'subtotal',
                      'qty',
                      'parcel_type',
                  ),
                  Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
              ),
              array(
                  'website_id',
                  'dest_country_id',
                  'dest_region_id',
                  'dest_zip',
                  'weight',
                  'subtotal',
                  'qty',
                  'parcel_type',
              ),
              array(
                  'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
              )
          )
          ->setComment('PostNL Matrixrate');

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();