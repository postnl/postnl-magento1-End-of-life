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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

/**
 * A new ACl role has been added for printing return labels.
 */
$returnLabelsAclResource = array(
    'admin/postnl/shipment/actions/print_label/print_return_labels',
    'admin/postnl/shipment/actions/print_label/print_return_labels/send_return_label_email',
);

$returnLabelsRequiredResources = array(
    'admin/postnl',
    'admin/postnl/shipment',
    'admin/postnl/shipment/actions',
    'admin/postnl/shipment/actions/print_label',
);

/**
 * A new ACl role has been added for changing the parcel count of a shipment.
 */
$changeParcelCountAclResource = array(
    'admin/postnl/shipment/actions/convert/change_parcel_count',
);

$changeParcelCountRequiredResources = array(
    'admin/postnl',
    'admin/postnl/shipment',
    'admin/postnl/shipment/actions',
    'admin/postnl/shipment/actions/convert',
);

/**
 * A new ACl role has been added for viewing the PostNL returns grid.
 */
$postnlReturnsGridAclResource = array(
    'admin/sales/postnl_returns',
);

$postnlReturnsGridRequiredResources = array(
    'admin/sales',
    'admin/sales/shipment',
);

/**
 * These CIF webservices have been updated.
 */
$updatedWebservices = array(
    'cif_version_labelling',
    'cif_version_timeframe',
    'cif_version_shippingstatus',
    'cif_version_location',
    'cif_version_deliverydate',
    'cif_version_confirming',
    'cif_version_barcode',
    'cif_version_timeframe',
);

$settingsToMove = array(
    'postnl/cif_address/return_firstname'             => 'postnl/cif_address/alternative_sender_firstname',
    'postnl/cif_address/return_lastname'              => 'postnl/cif_address/alternative_sender_lastname',
    'postnl/cif_address/return_company'               => 'postnl/cif_address/alternative_sender_company',
    'postnl/cif_address/return_department'            => 'postnl/cif_address/alternative_sender_department',
    'postnl/cif_address/return_streetname'            => 'postnl/cif_address/alternative_sender_streetname',
    'postnl/cif_address/return_housenumber'           => 'postnl/cif_address/alternative_sender_housenumber',
    'postnl/cif_address/return_housenumber_extension' => 'postnl/cif_address/alternative_sender_housenumber_extension',
    'postnl/cif_address/return_postcode'              => 'postnl/cif_address/alternative_sender_postcode',
    'postnl/cif_address/return_city'                  => 'postnl/cif_address/alternative_sender_city',
    'postnl/cif_address/return_region'                => 'postnl/cif_address/alternative_sender_region',
    'postnl/cif_address/return_country'               => 'postnl/cif_address/alternative_sender_country',
);

foreach ($settingsToMove as $from => $to) {
    $installer->moveConfigSettingInDb($from, $to);
}

$installer->addAclRules($returnLabelsAclResource, $returnLabelsRequiredResources)
          ->addAclRules($changeParcelCountAclResource, $changeParcelCountRequiredResources)
          ->addAclRules($postnlReturnsGridAclResource, $postnlReturnsGridRequiredResources)
          ->resetWebserviceVersions($updatedWebservices)
          ->generateReturnStatusCronExpr()
          ->setDateTimeZoneUpdateCron()
          ->clearConfigCache();
