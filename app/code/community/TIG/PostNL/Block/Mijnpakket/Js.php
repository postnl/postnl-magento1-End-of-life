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
 * @method boolean                        hasIsTestMode()
 * @method TIG_PostNL_Block_Mijnpakket_Js setIsTestMode(boolean $value)
 * @method boolean                        hasBaseUrl()
 * @method TIG_PostNL_Block_Mijnpakket_Js setBaseUrl(string $value)
 */
class TIG_PostNL_Block_Mijnpakket_Js extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_mijnpakket_js';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/js.phtml';

    /**
     * Available URl's for PostNL's login API.
     */
    const LIVE_BASE_URL            = 'https://mijnpakket.postnl.nl/';
    const TEST_BASE_URL            = 'https://tppwscheckout-sandbox.e-id.nl/';
    const LOGIN_JS_PATH            = 'Checkout2/Login.js';
    const CHECKOUT_PREMIUM_JS_PATH = 'Checkout2/CheckoutPremium.js';

    /**
     * @return boolean
     */
    public function getIsTestMode()
    {
        if ($this->hasIsTestMode()) {
            return $this->_getData('is_test_mode');
        }

        $isTestMode = Mage::helper('postnl/mijnpakket')->isTestMode();

        $this->setIsTestMode($isTestMode);
        return $isTestMode;
    }

    /**
     * Gets the current base URL based on whether the extension is set to test mode.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->hasBaseUrl()) {
            return $this->_getData('base_url');
        }

        $isTestMode = $this->getIsTestMode();
        if ($isTestMode) {
            $baseUrl = self::TEST_BASE_URL;
        } else {
            $baseUrl = self::LIVE_BASE_URL;
        }

        $this->setBaseUrl($baseUrl);
        return $baseUrl;
    }

    /**
     * gets the Mijnpakket Login JS URL for either live or test mode.
     *
     * @return string
     */
    public function getLoginJsUrl()
    {
        $baseUrl = $this->getBaseUrl();

        $url = $baseUrl . self::LOGIN_JS_PATH;

        return $url;
    }

    /**
     * Get the Checkout premium JS URL which is used to check if the customer has a MijnPakket account.
     *
     * @return string
     */
    public function getCheckoutPremiumJsUrl()
    {
        $baseUrl = $this->getBaseUrl();

        $url = $baseUrl . self::CHECKOUT_PREMIUM_JS_PATH;

        return $url;
    }

    /**
     * Check if the current customer may login using Mijnpakket.
     *
     * @return string
     */
    protected function _tohtml()
    {
        $helper = Mage::helper('postnl/mijnpakket');
        if (!$helper->canLoginWithMijnpakket()) {
            return '';
        }

        return parent::_toHtml();
    }
}