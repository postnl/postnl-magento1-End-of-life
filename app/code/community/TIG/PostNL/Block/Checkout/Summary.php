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
 * @method boolean hasQuote()
 * @method TIG_PostNL_Block_Checkout_Summary setQuote(Mage_Sales_Model_Quote $quote)
 */
class TIG_PostNL_Block_Checkout_Summary extends Mage_Sales_Block_Items_Abstract
{
    /**
     * PakjeGemak address type
     */
    const PAKJE_GEMAK_ADDRESS_TYPE = 'pakje_gemak';

    /**
     * Get active or custom quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }

        $quote = Mage::registry('current_quote');

        $this->setQuote($quote);
        return $quote;
    }

    /**
     * Get all visible items in the quote
     *
     * @return array
     */
    public function getItems()
    {
        $quote = $this->getQuote();

        return $quote->getAllVisibleItems();
    }

    /**
     * Gets an optional pakje_gemak address from the quote
     *
     * @return boolean | Mage_Sales_Model_Quote_Address
     */
    public function getPakjeGemakAddress()
    {
        $quote = $this->getQuote();

        $addresses = $quote->getAddressesCollection();

        /** @var Mage_Sales_Model_Quote_Address $address */
        foreach ($addresses as $address) {
            if ($address->getAddressType() == self::PAKJE_GEMAK_ADDRESS_TYPE) {
                $address = Mage::getModel('sales/quote_address')->load($address->getId());
                return $address;
            }
        }

        return false;
    }

    /**
     * Gets the shipping address's shipping description
     *
     * @return string
     */
    public function getShippingDescription()
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $this->getQuote()->getShippingAddress();

        /** @noinspection PhpUndefinedMethodInspection */
        if ($address->hasShippingDescription()) {
            return $address->getShippingDescription();
        }

        $method = $address->getShippingMethod();

        if (!$method) {
            return '';
        }

        $shippingDescription = '';
        /** @var Mage_Sales_Model_Quote_Address_Rate $rate */
        foreach ($address->getAllShippingRates() as $rate) {
            if ($rate->getCode() == $method) {
                $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                $address->setShippingDescription(trim($shippingDescription, ' -'));
                break;
            }
        }

        return $shippingDescription;
    }

    /**
     * @return boolean|string
     */
    public function getPaymentOption()
    {
        $paymentData = Mage::registry('postnl_payment_data');
        if (!isset($paymentData['method']) || !isset($paymentData['option'])) {
            return false;
        }

        $methodCode = $paymentData['method'];
        $optionValue = $paymentData['option'];

        if (!$optionValue) {
            return false;
        }

        /** @var TIG_PostNL_Helper_Checkout $helper */
        $helper = Mage::helper('postnl/checkout');
        $optionConversionArray = $helper->getOptionConversionArray();
        if (!array_key_exists($methodCode, $optionConversionArray)) {
            return $optionValue;
        }

        $methodArray = $optionConversionArray[$methodCode];
        if (!array_key_exists($optionValue, $methodArray)) {
            return $optionValue;
        }

        $convertedOption = $methodArray[$optionValue];
        return $convertedOption;
    }

    /**
     * Gets the PostNL order associated with the current quote
     *
     * @return TIG_PostNL_Model_Core_Order
     */
    public function getPostnlOrder()
    {
        $quote = $this->getQuote();
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');

        return $postnlOrder;
    }
}
