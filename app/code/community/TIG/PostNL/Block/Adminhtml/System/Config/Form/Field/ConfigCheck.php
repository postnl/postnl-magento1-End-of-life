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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                                         hasPostnlHelper()
 * @method TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ConfigCheck setPostnlHelper(TIG_PostnL_Helper_Data $value)
 */
class TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_ConfigCheck
    extends TIG_PostNL_Block_Adminhtml_System_Config_Form_Field_TextBox_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_system_config_form_field_configcheck';

    /**
     * XML paths to use GlobalPack/Checkout settings.
     */
    const XPATH_USE_GLOBALPACK = 'postnl/cif_globalpack_settings/use_globalpack';
    const XPATH_USE_CHECKOUT   = 'postnl/cif/use_checkout';

    /**
     * Template file used by this element.
     *
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/form/field/config_check.phtml';

    /**
     * Get the postnl helper.
     *
     * @return TIG_PostNL_Helper_Data
     */
    public function getPostnlHelper()
    {
        if ($this->hasPostnlHelper()) {
            return $this->getData('postnl_helper');
        }

        $helper = Mage::helper('postnl');

        $this->setPostnlHelper($helper);
        return $helper;
    }

    /**
     * Check if live mode is enabled.
     *
     * @return boolean
     */
    public function isLiveEnabled()
    {
        $helper = $this->getPostnlHelper();

        return $helper->isEnabled(false, false, true);
    }

    /**
     * Check if test mode is enabled.
     *
     * @return boolean
     */
    public function isTestEnabled()
    {
        $helper = $this->getPostnlHelper();

        return $helper->isEnabled(false, true, true);
    }

    /**
     * Gets config errors from the registry.
     *
     * @return array|null
     */
    public function getConfigErrors()
    {
        $configErrors = Mage::registry('postnl_core_is_configured_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_core_is_enabled_errors');
        }

        return $configErrors;
    }

    /**
     * Check if the extension is currently set to test mode.
     *
     * @return boolean
     */
    public function isTestModeActive()
    {
        $helper = $this->getPostnlHelper();

        $isTestMode = $helper->isTestMode();
        return $isTestMode;
    }

    /**
     * Check if global shipments are
     *
     * @return boolean
     */
    public function isGlobalConfigured()
    {
        $globalEnabled = Mage::getStoreConfigFlag(self::XPATH_USE_GLOBALPACK, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$globalEnabled) {
            return true;
        }

        $helper = $this->getPostnlHelper();

        return $helper->isGlobalConfigured(false, true);
    }

    /**
     * gets config errors from the registry
     *
     * @return array|null
     */
    public function getGlobalConfigErrors()
    {
        $configErrors = Mage::registry('postnl_core_is_global_configured_errors');

        return $configErrors;
    }

    /**
     * Check if checkout is enabled
     *
     * @return boolean
     */
    public function isCheckoutEnabled()
    {
        $helper = Mage::helper('postnl/checkout');

        if (!$helper->isCheckoutActive()) {
            return true;
        }

        return $helper->isCheckoutEnabled(false);
    }

    /**
     * gets config errors from the registry
     *
     * @return array|null
     */
    public function getCheckoutConfigErrors()
    {
        $configErrors = Mage::registry('postnl_checkout_is_configured_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_checkout_is_enabled_errors');
        }

        return $configErrors;
    }
}