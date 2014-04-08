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
 */
class TIG_PostNL_Block_Mijnpakket_Js extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/js.phtml';

    /**
     * Available URl's for PostNL's login API.
     */
    const LOGIN_TEST_URL = 'https://tppwscheckout-sandbox.e-id.nl/Checkout2/Login.js';
    const LOGIN_LIVE_URL = 'https://mijnpakket.postnl.nl/Checkout2/Login.js';

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
     * gets the Mijnpakket Login JS URL for either live or test mode.
     *
     * @return string
     */
    public function getLoginJsUrl()
    {
        $isTestMode = $this->getIsTestMode();
        if ($isTestMode) {
            return self::LOGIN_TEST_URL;
        }

        return self::LOGIN_LIVE_URL;
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