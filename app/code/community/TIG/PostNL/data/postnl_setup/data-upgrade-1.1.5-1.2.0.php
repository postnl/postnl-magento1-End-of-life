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

$settingsToReset = array(
    'cif_version_shippingstatus',
    'cif_version_confirming',
    'cif_version_labelling',
    'cif_version_checkout',
);

$newAclResources = array(
    'admin/postnl',
    'admin/postnl/shipment',
    'admin/postnl/shipment/complete_status',
    'admin/postnl/shipment/actions',
    'admin/postnl/shipment/actions/confirm',
    'admin/postnl/shipment/actions/print_label',
    'admin/postnl/shipment/actions/create_parcelware_export',
    'admin/postnl/shipment/actions/send_track_and_trace',
    'admin/postnl/shipment/actions/reset_confirmation',
    'admin/postnl/shipment/actions/delete_labels',
);

$requiredAclResources = array(
    'admin/sales',
    'admin/sales/order',
    'admin/sales/order/actions',
    'admin/sales/order/actions/ship',
    'admin/sales/shipment',
);

/**
 * When upgrading from v1.1.x we need to reset the webservice versions used to default, add a new product option and
 * move a config setting. If you're installing the extension for the first time, all of this will be handled by the
 * default settings in config.xml.
 *
 * We also added several new ACL resources. Before this was handled solely by Magento's existing ACL resources. To
 * prevent merchants from being unable to process shipments as before, we need to add the new resources to the existing
 * admin roles.
 */
/** @noinspection PhpDeprecationInspection */
$installer->resetWebserviceVersions($settingsToReset)
          ->addSupportedProductCode('3553')
          ->moveConfigSetting('postnl/cif_labels_and_confirming/mode', 'postnl/cif/mode', true)
          ->addAclRules($newAclResources, $requiredAclResources)
          ->clearConfigCache();
