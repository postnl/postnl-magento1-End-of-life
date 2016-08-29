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
 * @method boolean                             hasApiKey()
 * @method TIG_PostNL_Block_DeliveryOptions_Js setApiKey($apiKey)
 */
class TIG_PostNL_Block_DeliveryOptions_Js extends TIG_PostNL_Block_DeliveryOptions_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_deliveryoptions_js';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/delivery_options/js.phtml';

    /**
     * Get the configured Google maps API key.
     *
     * @return string
     */
    public function getApiKey()
    {
        if ($this->hasApiKey()) {
            return $this->_getData('api_key');
        }

        $apiKey = Mage::getStoreConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_GOOGLE_MAPS_API_KEY,
            Mage::app()->getStore()->getId()
        );

        $this->setApiKey($apiKey);
        return $apiKey;
    }

    /**
     * Render the template if allowed.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        if (!$helper->isDeliveryOptionsEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
