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
class TIG_PostNL_Block_Mijnpakket_OrderSuccessJs extends TIG_PostNL_Block_Checkout_Cart_Js
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_mijnpakket_ordersuccessjs';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/mijnpakket/order_success_js.phtml';

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

        return TIG_PostNL_Block_Core_Template::_toHtml();
    }
}