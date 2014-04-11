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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Checkout_Cart_CheckoutLink extends Mage_Core_Block_Template
{
    /**
     * Base URLs of the checkout button
     */
    const CHECKOUT_BUTTON_TEST_BASE_URL = 'https://tppcb-sandbox.e-id.nl/Button/Checkout';
    const CHECKOUT_BUTTON_LIVE_BASE_URL = 'https://checkout.postnl.nl/Button/Checkout';

    /**
     * XML path to public webshop ID setting
     */
    const XML_PATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';

    /**
     * XML path to 'hide_button_if_disallowed' setting
     */
    const XML_PATH_HIDE_BUTTON_IF_DISALLOWED = 'postnl/checkout/hide_button_if_disallowed';

    /**
     * XML path to the 'instruction_cms_page' setting
     */
    const XML_PATH_INSTRUCTION_CMS_PAGE = 'postnl/checkout/instruction_cms_page';

    /**
     * Gets the checkout URL
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        $url = Mage::helper('checkout/url')->getCheckoutUrl();

        return $url;
    }

    /**
     * Check if the button should be disabled
     *
     * @return boolean
     */
    public function isDisabled()
    {
        if (!$this->canUsePostnlCheckout()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the button should be displayed
     *
     * @return boolean
     */
    public function canUsePostnlCheckout()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $helper = Mage::helper('postnl/checkout');
        $canUseCheckout = $helper->canUsePostnlCheckout($quote);

        /**
         * If Checkout is not available, log the reason why for debugging purposes
         */
        if (!$canUseCheckout && Mage::registry('postnl_checkout_logged') === null) {
            $configErrors = Mage::registry('postnl_is_configured_checkout_errors');
            if (is_null($configErrors)) {
                $configErrors = Mage::registry('postnl_enabled_checkout_errors');
            }

            if (is_null($configErrors)) {
                return $canUseCheckout;
            }

            $errorMessage = $helper->__('PostNL Checkout is not available due to the following reasons:');
            foreach ($configErrors as $error) {
                $errorMessage .= PHP_EOL . $error['message'];
            }

            Mage::register('postnl_checkout_logged', true);
            $helper->log($errorMessage);
        }

        return $canUseCheckout;

    }

    /**
     * Gets this webshop's public ID
     *
     * @return string
     */
    public function getPublicWebshopId()
    {
        if ($this->hasPublicWebshopId()) {
            return $this->getData('public_webshop_id');
        }

        $webshopId = Mage::getStoreConfig(self::XML_PATH_PUBLIC_WEBSHOP_ID, Mage::app()->getStore()->getId());

        $this->setPublicWebshopId($webshopId);
        return $webshopId;
    }

    /**
     * Gets the checkout button src attribute
     *
     * @param boolean $forceDisabled
     *
     * @return string
     */
    public function getSrc($forceDisabled = false)
    {
        if (Mage::helper('postnl/checkout')->isTestMode()) {
            $baseUrl = self::CHECKOUT_BUTTON_TEST_BASE_URL;
        } else {
            $baseUrl = self::CHECKOUT_BUTTON_LIVE_BASE_URL;
        }

        $webshopId = $this->getPublicWebshopId();

        $url =  $baseUrl
             . '?publicId=' . $webshopId
             . '&format=Large'
             . '&type=Orange';

        if ($forceDisabled === true || $this->isDisabled()) {
            $url .= '&disabled=true';
        }

        return $url;
    }

    /**
     * Gets the URL of a CMS page containing instructions on how to use PostNL Checkout
     *
     * @return boolean|string
     */
    public function getInstructionUrl()
    {
        $instructionPage = Mage::getStoreConfig(self::XML_PATH_INSTRUCTION_CMS_PAGE, Mage::app()->getStore()->getId());
        if (!$instructionPage) {
            return false;
        }

        $pageUrl = Mage::helper('cms/page')->getPageUrl($instructionPage);
        return $pageUrl;
    }

    /**
     * Returns the block's html. Checks if the 'use_postnl_checkout' param is set. If not, returns and empty string
     *
     * @return string
     */
    protected function _toHtml()
    {
        $helper = Mage::helper('postnl/checkout');
        if (!$helper->isCheckoutActive() && Mage::registry('postnl_checkout_logged') === null) {
            /**
             * If Checkout is not available, log the reason why for debugging purposes
             */
            $configErrors = Mage::registry('postnl_enabled_checkout_errors');

            if (is_null($configErrors)) {
                return '';
            }

            $errorMessage = $helper->__('PostNL Checkout is not available due to the following reasons:');
            foreach ($configErrors as $error) {
                $errorMessage .= PHP_EOL . $error['message'];
            }

            Mage::register('postnl_checkout_logged', true);
            $helper->log($errorMessage);

            /**
             * Do not render the checkout button
             */
            return '';
        }

        return parent::_toHtml();
    }
}
