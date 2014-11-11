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
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_SplitAddressCheck
    extends TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_TextBox_Abstract
{
    /**
     * Xpaths to split street configuration options.
     */
    const XPATH_SPLIT_STREET       = 'postnl/cif_labels_and_confirming/split_street';
    const XPATH_USE_POSTCODE_CHECK = 'postnl/cif_labels_and_confirming/use_postcode_check';
    const XPATH_CHECKOUT_EXTENSION = 'postnl/cif_address/checkout_extension';

    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_system_config_form_field_splitaddresscheck';

    /**
     * Template file used.
     *
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/form/field/split_address_check.phtml';

    /**
     * Get if the split_street field or the postcode check is enabled.
     *
     * @return boolean
     */
    public function getIsAddressSplit()
    {
        $request = Mage::app()->getRequest();

        /**
         * Check if the split_street field is enabled based on the current scope
         */
        if ($request->getParam('store')) {
            $store = $request->getparam('store');

            $checkoutExtension = Mage::getStoreConfig(self::XPATH_CHECKOUT_EXTENSION, $store);
            $usePostcodeCheck  = Mage::getStoreConfigFlag(self::XPATH_USE_POSTCODE_CHECK, $store);
            $splitStreet       = Mage::getStoreConfigFlag(self::XPATH_SPLIT_STREET, $store);
        } elseif ($request->getParam('website')) {
            $website = Mage::getModel('core/website')->load($request->getparam('website'), 'code');

            $checkoutExtension = $website->getConfig(self::XPATH_CHECKOUT_EXTENSION, $website->getId());
            $usePostcodeCheck  = (bool) $website->getConfig(self::XPATH_USE_POSTCODE_CHECK, $website->getId());
            $splitStreet       = (bool) $website->getConfig(self::XPATH_SPLIT_STREET, $website->getId());
        } else {
            $store = Mage_Core_Model_App::ADMIN_STORE_ID;

            $checkoutExtension = Mage::getStoreConfig(self::XPATH_CHECKOUT_EXTENSION, $store);
            $usePostcodeCheck  = Mage::getStoreConfigFlag(self::XPATH_USE_POSTCODE_CHECK, $store);
            $splitStreet       = Mage::getStoreConfigFlag(self::XPATH_SPLIT_STREET, $store);
        }

        if ((!$checkoutExtension || $checkoutExtension == 'other') && !$splitStreet) {
            return false;
        } elseif ($checkoutExtension && $checkoutExtension != 'other' && $splitStreet) {
            return true;
        }

        return $usePostcodeCheck;
    }
}