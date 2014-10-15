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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean                                                  hasPhoneNumber()
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber setPhoneNumber(string $phoneNumber)
 * @method boolean                                                  hasShippingAddress()
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber setShippingAddress(Mage_Sales_Model_Quote_Address $address)
 * @method boolean                                                  hasQuote()
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber setQuote(Mage_Sales_Model_Quote $quote)
 */
class TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber extends TIG_PostNL_Block_DeliveryOptions_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_deliveryoptions_checkout_addphonenumber';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/delivery_options/addphonenumber.phtml';

    /**
     * Gets a phone number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        if ($this->hasPhoneNumber()) {
            return $this->_getData('phone_number');
        }

        $shippingAddress = $this->getShippingAddress();
        $phoneNumber = $shippingAddress->getTelephone();

        /**
         * OSC often replaces missing required fields by a single dash. We want to avoid this behaviour.
         */
        if ($phoneNumber == '-') {
            $phoneNumber = '';
        }

        $this->setPhoneNumber($phoneNumber);
        return $phoneNumber;
    }

    /**
     * Gets a shipping address object.
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        if ($this->hasShippingAddress()) {
            return $this->_getData('shipping_address');
        }

        $quote = $this->getQuote();
        if (!$quote) {
            $shippingAddress = Mage::getModel('sales/quote_address');

            $this->setShippingAddress($shippingAddress);
            return $shippingAddress;
        }

        $shippingAddress = $quote->getShippingAddress();

        $this->setShippingAddress($shippingAddress);
        return $shippingAddress;
    }

    /**
     * Get the current quote.
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            return $this->_getData('quote');
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->setQuote($quote);
        return $quote;
    }
}