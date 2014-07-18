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

/**
 * Several new ACL roles have been added.
 */
$newConfigAclResources = array(
    'admin/system/config/postnl/download_logs',
);
$configRequiredResources = array(
    'admin/system/',
    'admin/system/config',
    'admin/system/config/postnl',
);

$newPostnLAclResources = array(
    'admin/postnl/shipment/actions/print_label/print_packing_slips',
);
$postnlRequiredResources = array(
    'admin/postnl',
    'admin/postnl/shipment',
    'admin/postnl/shipment/actions',
    'admin/postnl/shipment/actions/print_label',
);

/**
 * These settings have moved.
 */
$settingsToMove = array(
    'postnl/delivery_options/shipping_duration'    => 'postnl/cif_labels_and_confirming/shipping_duration',
    'postnl/delivery_options/cutoff_time'          => 'postnl/cif_labels_and_confirming/cutoff_time',
    'postnl/delivery_options/allow_sunday_sorting' => 'postnl/cif_labels_and_confirming/allow_sunday_sorting',
    'postnl/delivery_options/sunday_cutoff_time'   => 'postnl/cif_labels_and_confirming/sunday_cutoff_time',
);

foreach ($settingsToMove as $oldXpath => $newXpath) {
    $installer->moveConfigSetting($oldXpath, $newXpath, true);
}

/**
 * In this new version we need to fill the new 'order_id' and 'shipment_type' columns. We also need to add several new
 * ACL rules and add 2 new support product codes for 'buspakje' shipments.
 */
$installer->setOrderId()
          ->setShipmentType()
          ->setIsBuspakje()
          ->addAclRules($newConfigAclResources, $configRequiredResources)
          ->addAclRules($newPostnLAclResources, $postnlRequiredResources)
          ->addSupportedProductCode(array('2828', '2928'))
          ->clearConfigCache();
