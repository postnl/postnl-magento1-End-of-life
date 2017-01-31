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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                 hasIsTestMode()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setIsTestMode(boolean $value)
 * @method boolean                                 hasBaseUrl()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setBaseUrl(string $value)
 * @method boolean                                 hasPublicWebshopId()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setPublicWebshopId(string $value)
 * @method boolean                                 hasSavedMijnpakketData()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setSavedMijnpakketData(string $value)
 * @method boolean                                 hasButtonUrl()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setButtonUrl(string $value)
 * @method boolean                                 hasDisabledButtonUrl()
 * @method TIG_PostNL_Block_Mijnpakket_LoginButton setDisabledButtonUrl(string $value)
 */
class TIG_PostNL_Block_Mijnpakket_LoginButton extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_mijnpakket_loginbutton';

    /**
     * The webshop's public webshop ID is used to secure communications with PostNL's servers.
     */
    const XPATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';

    /**
     * Available URl's for PostNL's login buttons.
     */
    const LIVE_BASE_URL_XPATH   = 'postnl/delivery_options/mijnpakket_login_btn_live_base_url';
    const TEST_BASE_URL_XPATH   = 'postnl/delivery_options/mijnpakket_login_btn_test_base_url';
    const BUTTON_URL_PATH_XPATH = 'postnl/delivery_options/mijnpakket_login_btn_url_path';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/login_button.phtml';

    /**
     * @return boolean
     */
    public function getIsTestMode()
    {
        if ($this->hasIsTestMode()) {
            return $this->_getData('is_test_mode');
        }

        /** @var TIG_PostNL_Helper_Mijnpakket $helper */
        $helper = Mage::helper('postnl/mijnpakket');
        $isTestMode = $helper->isTestMode();

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

        $baseUrl = Mage::app()->getRequest()->getScheme() . '://';
        if ($isTestMode) {
            $baseUrl .= Mage::getStoreConfig(self::TEST_BASE_URL_XPATH);
        } else {
            $baseUrl .= Mage::getStoreConfig(self::LIVE_BASE_URL_XPATH);
        }

        $this->setBaseUrl($baseUrl);
        return $baseUrl;
    }

    /**
     * Get the current public webshop ID.
     *
     * @return string
     */
    public function getPublicWebshopId()
    {
        if ($this->hasPublicWebshopId()) {
            return $this->_getData('public_webshop_id');
        }

        $publicWebshopId = Mage::getStoreConfig(self::XPATH_PUBLIC_WEBSHOP_ID, Mage::app()->getStore()->getId());

        $this->setPublicWebshopId($publicWebshopId);
        return $publicWebshopId;
    }

    /**
     * Get saved MijnPakket data if available.
     *
     * @return array|null
     */
    public function getSavedMijnpakketData()
    {
        if ($this->hasSavedMijnpakketData()) {
            return $this->_getData('saved_mijnpakket_data');
        }

        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        /** @noinspection PhpUndefinedMethodInspection */
        $data = $session->getPostnlMijnpakketData();

        $this->setSavedMijnpakketData($data);
        return $data;
    }

    /**
     * Gets the button URL.
     *
     * @return string
     */
    public function getButtonUrl()
    {
        if ($this->hasButtonUrl()) {
            return $this->_getData('button_url');
        }

        $baseUrl = $this->getBaseUrl();
        $url = $baseUrl . Mage::getStoreConfig(self::BUTTON_URL_PATH_XPATH);

        $url .= '?publicId=' . $this->getPublicWebshopId();

        $this->setButtonUrl($url);
        return $url;
    }

    /**
     * Gets the URL for the disabled button.
     *
     * @return string
     */
    public function getDisabledButtonUrl()
    {
        if ($this->hasDisabledButtonUrl()) {
            return $this->_getData('disabled_button_url');
        }

        $buttonUrl = $this->getButtonUrl();
        $buttonUrl .= '&disabled=true';

        $this->setDisabledButtonUrl($buttonUrl);
        return $buttonUrl;
    }

    /**
     * Checks if debug mode is allowed. Debug mode is enabled if the PostNl extension's debug mode is set to 'full'.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $debugMode = $helper->getDebugMode();

        if ($debugMode > 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current customer may login using MijnPakket.
     *
     * @return string
     */
    protected function _tohtml()
    {
        /** @var TIG_PostNL_Helper_Mijnpakket $helper */
        $helper = Mage::helper('postnl/mijnpakket');
        if (!$helper->canLoginWithMijnpakket()) {
            return '';
        }

        return parent::_toHtml();
    }
}
