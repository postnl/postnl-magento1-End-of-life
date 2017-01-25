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
 *
 * @var TIG_PostNL_Model_Resource_Setup $installer
 */
$installer = $this;

set_time_limit(0);

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

$productTypes = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
);

$installer->generateShippingStatusCronExpr()
          ->generateUpdateStatisticsCronExpr()
          ->expandSupportTab()
          ->installTestPassword()
          ->installWebshopId()
          ->installPackingSlipItemColumns()
          ->updateAttributeValues($simpleAttributesData, array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE))
          ->updateAttributeValues($attributesData, $productTypes)
          ->clearConfigCache();
