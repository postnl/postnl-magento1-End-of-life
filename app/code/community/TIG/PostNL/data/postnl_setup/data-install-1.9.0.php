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
        'postnl_shipping_duration'      => -1,
    );

    $productTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
    );

    /**
     * Matrix rate data.
     */
    $matrixRateData = array(
        array('NL', '*', '*', '0', '0', '0', 'regular', '5'), // Regular Dutch shipments
        array('NL', '*', '*', '0', '0', '0', 'letter_box', '2.5'), // Dutch buspakje shipments
        array('NL', '*', '*', '0', '0', '0', 'food', '10'), // Dutch buspakje shipments
        array('BE', '*', '*', '0', '0', '0', 'pakje_gemak', '10'), // Belgian shipments
        array('BE', '*', '*', '0', '0', '0', '*', '5'), // Belgian shipments
        array('BE', '*', '*', '1', '0', '0', '*', '5'),
        array('BE', '*', '*', '2', '0', '0', '*', '5'),
        array('BE', '*', '*', '3', '0', '0', '*', '5'),
        array('BE', '*', '*', '4', '0', '0', '*', '5'),
        array('BE', '*', '*', '5', '0', '0', '*', '5'),
        array('BE', '*', '*', '6', '0', '0', '*', '5'),
        array('BE', '*', '*', '7', '0', '0', '*', '5'),
        array('BE', '*', '*', '8', '0', '0', '*', '5'),
        array('BE', '*', '*', '9', '0', '0', '*', '5'),
        array('BE', '*', '*', '10', '0', '0', '*', '5'),
        array('BE', '*', '*', '11', '0', '0', '*', '5'),
        array('BE', '*', '*', '12', '0', '0', '*', '5'),
        array('BE', '*', '*', '13', '0', '0', '*', '5'),
        array('BE', '*', '*', '14', '0', '0', '*', '5'),
        array('BE', '*', '*', '15', '0', '0', '*', '5'),
        array('BE', '*', '*', '16', '0', '0', '*', '5'),
        array('BE', '*', '*', '17', '0', '0', '*', '5'),
        array('BE', '*', '*', '18', '0', '0', '*', '5'),
        array('BE', '*', '*', '19', '0', '0', '*', '5'),
        array('BE', '*', '*', '20', '0', '0', '*', '5'),
        array('BE', '*', '*', '21', '0', '0', '*', '5'),
        array('BE', '*', '*', '22', '0', '0', '*', '5'),
        array('BE', '*', '*', '23', '0', '0', '*', '5'),
        array('BE', '*', '*', '24', '0', '0', '*', '5'),
        array('BE', '*', '*', '25', '0', '0', '*', '5'),
        array('BE', '*', '*', '26', '0', '0', '*', '5'),
        array('BE', '*', '*', '27', '0', '0', '*', '5'),
        array('BE', '*', '*', '28', '0', '0', '*', '5'),
        array('BE', '*', '*', '29', '0', '0', '*', '5'),
        array('DE', '*', '*', '0', '0', '0', '*', '5'), // German shipments
        array('DE', '*', '*', '1', '0', '0', '*', '5'),
        array('DE', '*', '*', '2', '0', '0', '*', '5'),
        array('DE', '*', '*', '3', '0', '0', '*', '5'),
        array('DE', '*', '*', '4', '0', '0', '*', '5'),
        array('DE', '*', '*', '5', '0', '0', '*', '5'),
        array('DE', '*', '*', '6', '0', '0', '*', '5'),
        array('DE', '*', '*', '7', '0', '0', '*', '5'),
        array('DE', '*', '*', '8', '0', '0', '*', '5'),
        array('DE', '*', '*', '9', '0', '0', '*', '5'),
        array('DE', '*', '*', '10', '0', '0', '*', '5'),
        array('DE', '*', '*', '11', '0', '0', '*', '5'),
        array('DE', '*', '*', '12', '0', '0', '*', '5'),
        array('DE', '*', '*', '13', '0', '0', '*', '5'),
        array('DE', '*', '*', '14', '0', '0', '*', '5'),
        array('DE', '*', '*', '15', '0', '0', '*', '5'),
        array('DE', '*', '*', '16', '0', '0', '*', '5'),
        array('DE', '*', '*', '17', '0', '0', '*', '5'),
        array('DE', '*', '*', '18', '0', '0', '*', '5'),
        array('DE', '*', '*', '19', '0', '0', '*', '5'),
        array('DE', '*', '*', '20', '0', '0', '*', '5'),
        array('DE', '*', '*', '21', '0', '0', '*', '5'),
        array('DE', '*', '*', '22', '0', '0', '*', '5'),
        array('DE', '*', '*', '23', '0', '0', '*', '5'),
        array('DE', '*', '*', '24', '0', '0', '*', '5'),
        array('DE', '*', '*', '25', '0', '0', '*', '5'),
        array('DE', '*', '*', '26', '0', '0', '*', '5'),
        array('DE', '*', '*', '27', '0', '0', '*', '5'),
        array('DE', '*', '*', '28', '0', '0', '*', '5'),
        array('DE', '*', '*', '29', '0', '0', '*', '5'),
        array('*', '*', '*', '0', '0', '0', '*', '5'), // GlobalPack shipments
    );

    $installer->generateShippingStatusCronExpr()
        ->generateUpdateStatisticsCronExpr()
        ->generateReturnStatusCronExpr()
        ->expandSupportTab()
        ->installTestPassword()
        ->installWebshopId()
        ->installPackingSlipItemColumns()
        ->setProductAttributeUpdateCron(
            array(
                array(
                    $simpleAttributesData,
                    array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE),
                ),
                array(
                    $attributesData,
                    $productTypes,
                )
            )
        )
        ->installMatrixRates($matrixRateData)
        ->updateAttributeData(
            array(
                'postnl_shipping_duration' => array(
                    'default_value' => -1,
                ),
            )
        )
        ->clearConfigCache();
