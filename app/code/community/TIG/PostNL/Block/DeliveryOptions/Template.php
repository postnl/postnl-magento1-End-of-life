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
abstract class TIG_PostNL_Block_DeliveryOptions_Template extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_deliveryoptions_template';

    /**
     * Xpath to the current theme setting.
     */
    const XPATH_THEME = 'postnl/delivery_options/theme';

    /**
     * @var null|boolean|Varien_Simplexml_Element
     */
    protected $_currentTheme = null;

    /**
     * @var null|boolean
     */
    protected $_canUseDeliveryOptions = null;

    /**
     * Gets the current theme.
     *
     * @return bool|null|Varien_Simplexml_Element
     */
    public function getCurrentTheme()
    {
        if ($this->_currentTheme !== null) {
            return $this->_currentTheme;
        }

        $currentTheme = Mage::getStoreConfig(self::XPATH_THEME, Mage::app()->getStore()->getId());

        $config = Mage::getConfig()->getNode('tig/delivery_options/themes');

        /**
         * @var Varien_Simplexml_Element $theme
         */
        $theme = $config->$currentTheme;

        if (!$theme) {
            $this->_currentTheme = false;
            return false;
        }

        $this->_currentTheme = $theme;
        return $theme;
    }

    /**
     * Check if PostNL delivery options are available for the current quote.
     *
     * @return boolean
     */
    public function canUseDeliveryOptions()
    {
        if ($this->_canUseDeliveryOptions !== null) {
            return $this->_canUseDeliveryOptions;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseDeliveryOptions = $helper->canUseDeliveryOptions($quote, false);

        $this->_canUseDeliveryOptions = $canUseDeliveryOptions;
        return $canUseDeliveryOptions;
    }

    /**
     * Render the template if allowed.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canUseDeliveryOptions()) {
            return '';
        }

        return parent::_toHtml();
    }
}