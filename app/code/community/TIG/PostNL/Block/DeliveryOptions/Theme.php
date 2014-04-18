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
 * @method boolean hasIsOsc()
 */
class TIG_PostNL_Block_DeliveryOptions_Theme extends TIG_PostNL_Block_DeliveryOptions_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_deliveryoptions_theme';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/delivery_options/theme.phtml';

    /**
     * Gets whether the current checkout page is OneStepCheckout.
     *
     * @return boolean|mixed
     */
    public function getIsOsc()
    {
        if (!$this->hasIsOsc()) {
            return false;
        }

        return $this->_getData('is_osc');
    }

    /**
     * Gets a css file path for the current theme.
     *
     * @return string
     */
    public function getThemeCssFile()
    {
        /**
         * @var Varien_Simplexml_Element $theme
         */
        $theme = $this->getCurrentTheme();
        if (!$theme) {
            return '';
        }

        /**
         * @var Varien_Simplexml_Element $files
         */
        $files = $theme->files;
        if (!$files) {
            return '';
        }

        if ($this->getIsOsc()) {
            $file = (string) $files->onestepcheckout;
        } else {
            $file = (string) $files->onepage;
        }

        return $file;
    }

    /**
     * Check if PostNL delivery options are available for the current quote.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $helper = Mage::helper('postnl/deliveryOptions');

        if (!$helper->canUseDeliveryOptions($quote, false)) {
            return '';
        }

        if (!$this->getThemeCssFile()) {
            return '';
        }

        return parent::_toHtml();
    }
}