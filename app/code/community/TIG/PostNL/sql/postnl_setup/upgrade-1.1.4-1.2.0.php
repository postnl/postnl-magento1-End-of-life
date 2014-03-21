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
    'is_parcelware_exported',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Is Parcelware Exported',
        'after'    => 'labels_printed',
    )
);

$conn->addColumn($installer->getTable('postnl_core/shipment'),
    'delivery_date',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        'nullable' => true,
        'comment'  => 'Delivery Date',
        'after'    => 'confirm_date',
    )
);

/***********************************************************************************************************************
 * POSTNL ORDER
 **********************************************************************************************************************/

$conn->addColumn($installer->getTable('postnl_checkout/order'),
    'type',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'default'  => null,
        'comment'  => 'Type',
        'after'    => 'quote_id',
    )
);

$conn->addColumn($installer->getTable('postnl_checkout/order'),
    'shipment_costs',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_FLOAT,
        'nullable' => true,
        'default'  => 0,
        'comment'  => 'Shipment Costs',
        'after'    => 'product_code',
    )
);

$conn->addColumn($installer->getTable('postnl_checkout/order'),
    'mobile_phone_number',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'default'  => null,
        'comment'  => 'Mobile Phone Number',
        'after'    => 'is_pakje_gemak',
    )
);

$installer->endSetup();