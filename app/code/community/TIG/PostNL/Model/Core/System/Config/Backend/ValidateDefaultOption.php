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
 *
 * @method boolean                                                            hasIsIncludingTax()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setIsIncludingTax(boolean $value)
 * @method boolean                                                            hasMockShippingAddress()
 * @method TIG_PostNL_Model_DeliveryOptions_System_Config_Backend_ValidateFee setMockShippingAddress(Mage_Customer_Model_Address $value)
 */
class TIG_PostNL_Model_Core_System_Config_Backend_ValidateDefaultOption extends Mage_Core_Model_Config_Data
{
    /**
     * Xpath to supported options configuration setting
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/cif_product_options/supported_product_options';

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
        $postData = Mage::app()->getRequest()->getPost();
        if (isset($postData['groups']['cif_product_options']['fields']['supported_product_options']['value'])) {
            $options = $postData['groups']['cif_product_options']['fields']['supported_product_options']['value'];
        } else {
            $options = Mage::getStoreConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS, Mage_Core_Model_App::ADMIN_STORE_ID);
            $options = explode(',', $options);
        }

        if (!in_array($value, $options)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    "You have chosen a default shipping option that is not supported. Please only select default " .
                    "options that you have selected in the 'Select supported option' field."
                ),
                'POSTNL-0159'
            );
        }

        return true;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $this->validateDefaultOption($value);

        return parent::_beforeSave();
    }
}