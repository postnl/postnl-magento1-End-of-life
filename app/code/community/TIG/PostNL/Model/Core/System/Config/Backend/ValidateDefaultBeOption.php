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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                                            hasIsIncludingTax()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setIsIncludingTax(boolean $value)
 * @method boolean                                                            hasMockShippingAddress()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setMockShippingAddress(Mage_Customer_Model_Address $value)
 */
class TIG_PostNL_Model_Core_System_Config_Backend_ValidateDefaultBeOption extends TIG_PostNL_Model_Core_System_Config_Backend_ValidateDefaultOption
{
    /**
     * Xpath to supported options configuration setting
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS_BE = 'postnl/grid/supported_product_options_be';

    /**
     * Validate that a chosen default option is actually available.
     *
     * @param $value
     *
     * @return bool
     *
     * @throws TIG_PostNL_Exception
     */
    public function validateDefaultOption($value)
    {
        /**
         * Get a list of supported options.
         */
        $postData = Mage::app()->getRequest()->getPost();
        if (!isset($postData['groups']['cif_product_options']['fields']['supported_product_options']['value'])) {
            $options = Mage::getStoreConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS_BE, Mage_Core_Model_App::ADMIN_STORE_ID);
            $options = explode(',', $options);
            $postData['groups']['cif_product_options']['fields']['supported_product_options']['value'] = $options;
        }

        Mage::app()->getRequest()->setPost($postData);

        return parent::validateDefaultOption($value);
    }
}
