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
 * @method boolean                           hasWebshopId()
 * @method TIG_PostNL_Block_Checkout_Cart_Js setWebshopId(string $value)
 * @method boolean                           hasCheckoutJsUrl()
 * @method TIG_PostNL_Block_Checkout_Cart_Js setCheckoutJsUrl(string $value)
 * @method boolean                           hasCheckoutPremiumJsUrl()
 * @method TIG_PostNL_Block_Checkout_Cart_Js setCheckoutPremiumJsUrl(string $value)
 * @method boolean                           hasEnvironment()
 * @method TIG_PostNL_Block_Checkout_Cart_Js setEnvironment(string $value)
 * @method boolean                           hasContinueUrl()
 * @method TIG_PostNL_Block_Checkout_Cart_Js setContinueUrl(string $value)
 */
class TIG_PostNL_Block_Checkout_Cart_Js extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_checkout_cart_js';

    /**
     * XML path for webshop ID setting
     */
    const XPATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';

    /**
     * XML path of show_summary_page setting
     */
    const XPATH_SHOW_SUMMARY_PAGE = 'postnl/checkout/show_summary_page';

    /**
     * URLs of the primary PostNL Checkout JS files for test and live mode
     */
    const TEST_CHECKOUT_JS_URL         = 'https://tppwscheckout-sandbox.e-id.nl/Checkout2/Scripts/Checkout.js';
    const LIVE_CHECKOUT_JS_URL         = 'https://mijnpakket.postnl.nl/Checkout2/Scripts/Checkout.js';
    const TEST_CHECKOUT_PREMIUM_JS_URL = 'https://tppwscheckout-sandbox.e-id.nl/Checkout2/CheckoutPremium.js';
    const LIVE_CHECKOUT_PREMIUM_JS_URL = 'https://mijnpakket.postnl.nl/Checkout2/CheckoutPremium.js';

    /**
     * Possible PostNL Checkout environments
     */
    const TEST_ENVIRONMENT = 'PostNL_OP_Checkout.environment_sandbox';
    const LIVE_ENVIRONMENT = 'PostNL_OP_Checkout.environment_production';

    /**
     * Gets the current store's webshop ID
     *
     * @return string
     */
    public function getWebshopId()
    {
        if ($this->hasWebshopId()) {
            return $this->_getData('webshop_id');
        }

        $storeId = Mage::app()->getStore()->getId();

        $webshopId = Mage::getStoreConfig(self::XPATH_PUBLIC_WEBSHOP_ID, $storeId);

        $this->setWebshopId($webshopId);
        return $webshopId;
    }

    /**
     * Gets the correct checkout js URL depending on whether PostNL Checkout is set to test or live mode
     *
     * @return string
     */
    public function getCheckoutJsUrl()
    {
        if ($this->hasCheckoutJsUrl()) {
            return $this->_getData('checkout_js_url');
        }

        $storeId = Mage::app()->getStore()->getId();

        if (Mage::helper('postnl/checkout')->isTestMode($storeId)) {
            $url = self::TEST_CHECKOUT_JS_URL;

            $this->setCheckoutJsUrl($url);
            return $url;
        }

        $url = self::LIVE_CHECKOUT_JS_URL;

        $this->setCheckoutJsUrl($url);
        return $url;
    }

    /**
     * Gets the correct checkout premium js URL depending on whether PostNL Checkout is set to test or live mode
     *
     * @return string
     */
    public function getCheckoutPremiumJsUrl()
    {
        if ($this->hasCheckoutPremiumJsUrl()) {
            return $this->_getData('checkout_premium_js_url');
        }

        $storeId = Mage::app()->getStore()->getId();

        if (Mage::helper('postnl/checkout')->isTestMode($storeId)) {
            $url = self::TEST_CHECKOUT_PREMIUM_JS_URL;

            $this->setCheckoutPremiumJsUrl($url);
            return $url;
        }

        $url = self::LIVE_CHECKOUT_PREMIUM_JS_URL;

        $this->setCheckoutPremiumJsUrl($url);
        return $url;
    }

    /**
     * Gets the current PostNL Checkout environment value
     *
     * @return string
     */
    public function getEnvironment()
    {
        if ($this->hasEnvironment()) {
            return $this->_getData('environment');
        }

        $storeId = Mage::app()->getStore()->getId();

        if (Mage::helper('postnl/checkout')->isTestMode($storeId)) {
            $environment = self::TEST_ENVIRONMENT;

            $this->setEnvironment($environment);
            return $environment;
        }

        $environment = self::LIVE_ENVIRONMENT;

        $this->setEnvironment($environment);
        return $environment;
    }

    /**
     * Gets a URL to which the user will be redirected after finishing the order in the PostNL overlay.
     *
     * @return string
     */
    public function getContinueUrl()
    {
        if ($this->hasContinueUrl()) {
            return $this->_getData('continue_url');
        }

        $storeId = Mage::app()->getStore()->getId();
        $showConfirmPage = Mage::getStoreConfigFlag(self::XPATH_SHOW_SUMMARY_PAGE, $storeId);
        if ($showConfirmPage) {
            $url = $this->getUrl('postnl/checkout/summary');

            $this->setContinueUrl($url);
            return $url;
        }

        $url = $this->getUrl('postnl/checkout/finishCheckout');

        $this->setContinueUrl($url);
        return $url;
    }

    /**
     * Returns the block's html. Checks if the 'use_postnl_checkout' param is set. If not, returns and empty string
     *
     * @return string
     */
    protected function _toHtml()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $canUseCheckout = Mage::helper('postnl/checkout')->canUsePostnlCheckout($quote);
        if (!$canUseCheckout) {
            return '';
        }

        return parent::_toHtml();
    }
}
