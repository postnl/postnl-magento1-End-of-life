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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @deprecated This file has been superseded by the data-upgrade-1.2.2-1.4.0.php file.
 */

/**
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

set_time_limit(0);

/**
 * A new ACL role has been added for the config page.
 */
$newConfigAclResources = array(
    'admin/system/config/postnl/download_logs',
);
$configRequiredResources = array(
    'admin/system/',
    'admin/system/config',
    'admin/system/config/postnl',
    'admin/system/config/convert',
    'admin/system/config/convert/to_buspakje',
    'admin/system/config/convert/to_package',
    'admin/system/config/convert/change_product_code',
);

/**
 * A new ACl role has also been added for printing packing slips.
 */
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
 * This attribute needs to be updated for simple products.
 */
$simpleAttributesData = array(
    'postnl_max_qty_for_buspakje' => 0,
);

/**
 * These attributes need to be updated for the product types specified below.
 */
$attributesData = array(
    'postnl_allow_pakje_gemak'      => 1,
    'postnl_allow_delivery_days'    => 1,
    'postnl_allow_timeframes'       => 1,
    'postnl_allow_pakketautomaat'   => 1,
    'postnl_allow_delivery_options' => 1,
);

/**
 * The attributes need to be updated for these product types.
 */
$productTypes = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
);

/**
 * In this new version we need to fill the new 'order_id' and 'shipment_type' columns. We also need to add several new
 * ACL rules and add several new support product codes for 'buspakje' and COD shipments, and update several attribute
 * values for existing products. We've also moved several config settings, so we need to copy the previous settings
 * there. Otherwise the existing configuration will be lost.
 */
/** @noinspection PhpDeprecationInspection */
$installer->setOrderId()
          ->setShipmentType()
          ->setIsBuspakje()
          ->addAclRules($newConfigAclResources, $configRequiredResources)
          ->addAclRules($newPostnLAclResources, $postnlRequiredResources)
          ->addSupportedProductCode(
              array(
                  '2828',
                  '2928',
                  '3086',
                  '3091',
                  '3093',
                  '3097',
                  '3535',
                  '3545',
                  '3536',
                  '3546'
              )
          )
          ->installPackingSlipItemColumns()
          ->updateAttributeValues($simpleAttributesData, array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE))
          ->updateAttributeValues($attributesData, $productTypes)
          ->moveConfigSetting(
              'postnl/delivery_options/shipping_duration',
              'postnl/cif_labels_and_confirming/shipping_duration',
              true
          )
          ->moveConfigSetting(
              'postnl/delivery_options/cutoff_time',
              'postnl/cif_labels_and_confirming/cutoff_time',
              true
          )
          ->moveConfigSetting(
              'postnl/delivery_options/allow_sunday_sorting',
              'postnl/cif_labels_and_confirming/allow_sunday_sorting',
              true
          )
          ->moveConfigSetting(
              'postnl/delivery_options/sunday_cutoff_time',
              'postnl/cif_labels_and_confirming/sunday_cutoff_time',
              true
          )
          ->clearConfigCache();
