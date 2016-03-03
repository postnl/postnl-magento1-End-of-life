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
 * @method boolean hasIsOsc()
 * @method boolean hasIsGoMage()
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
     * Gets whether the current checkout page is GoMage LightCheckout.
     *
     * @return boolean|mixed
     */
    public function getIsGoMage()
    {
        if (!$this->hasIsGoMage()) {
            return false;
        }

        return $this->_getData('is_go_mage');
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
        /** @noinspection PhpUndefinedFieldInspection */
        $files = $theme->files;
        if (!$files) {
            return '';
        }

        $file = '';
        if ($this->getIsOsc()
            && isset($files->onestepcheckout)
            && isset($files->onestepcheckout->main)
        ) {
            $file = (string) $files->onestepcheckout->main;
        } elseif ($this->getIsGoMage()
            && isset($files->gomage_checkout)
            && isset($files->gomage_checkout->main)
        ) {
            $file = (string) $files->gomage_checkout->main;
        } elseif (isset($files->onepage)
            && isset($files->onepage->main)
        ) {
            $file = (string) $files->onepage->main;
        }

        return $file;
    }

    /**
     * Gets a css file path for the current theme.
     *
     * @return array
     */
    public function getResponsiveThemeCssFiles()
    {
        $cssFiles = array();

        /**
         * @var Varien_Simplexml_Element $theme
         */
        $theme = $this->getCurrentTheme();
        if (!$theme) {
            return $cssFiles;
        }

        /**
         * @var Varien_Simplexml_Element $files
         */
        /** @noinspection PhpUndefinedFieldInspection */
        $files = $theme->files;
        if (!$files) {
            return $cssFiles;
        }

        if ($this->getIsOsc()
            && isset($files->onestepcheckout)
            && isset($files->onestepcheckout->responsive)
        ) {
            /**
             * @var Mage_Core_Model_Config_Element $cssFiles
             */
            $cssFiles = $files->onestepcheckout->responsive;
            $cssFiles = $cssFiles->asArray();
        } elseif ($this->getIsGoMage()
            && isset($files->gomage_checkout)
            && isset($files->gomage_checkout->responsive)
        ) {
            /**
             * @var Mage_Core_Model_Config_Element $cssFiles
             */
            $cssFiles = $files->gomage_checkout->responsive;
            $cssFiles = $cssFiles->asArray();
        } elseif (isset($files->onepage)
            && isset($files->onepage->responsive)
        ) {
            /**
             * @var Mage_Core_Model_Config_Element $cssFiles
             */
            $cssFiles = $files->onepage->responsive;
            $cssFiles = $cssFiles->asArray();
        }

        return $cssFiles;
    }

    /**
     * @return bool
     */
    public function canUseResponsive()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        return $helper->canUseResponsive();
    }

    /**
     * Check if PostNL delivery options are available for the current quote.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');

        if (!$helper->canUseDeliveryOptions($quote)) {
            return '';
        }

        return parent::_toHtml();
    }
}
