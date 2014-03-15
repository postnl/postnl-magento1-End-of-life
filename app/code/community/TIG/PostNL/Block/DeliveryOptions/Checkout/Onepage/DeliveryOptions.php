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
 */
class TIG_PostNL_Block_DeliveryOptions_Checkout_Onepage_DeliveryOptions extends Mage_Core_Block_Template
{
    /**
     * Xpaths to extra fee config settings.
     */
    const XPATH_EVENING_TIMEFRAME_FEE  = 'postnl/delivery_options/evening_timeframe_fee';
    const XPATH_PAKJEGEMAK_EXPRESS_FEE = 'postnl/delivery_options/pakjegemak_express_fee';

    /**
     * Xpath to 'allow_streetview' setting.
     */
    const XPATH_ALLOW_STREETVIEW = 'postnl/delivery_options/allow_streetview';

    /**
     * Currently selected shipping address.
     *
     * @var Mage_Sales_Model_Quote_Address|null
     */
    protected $_shippingAddress = null;

    /**
     * The earliest possible delivery date.
     *
     * @var null|string
     */
    protected $_deliveryDate = null;

    /**
     * Set the currently selected shipping address.
     *
     * @param Mage_Sales_Model_Quote_Address|null $shippingAddress
     *
     * @return TIG_PostNL_Block_DeliveryOptions_Checkout_Onepage_DeliveryOptions
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->_shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->hasData('quote')) {
            return $this->_getData('quote');
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->setData('quote', $quote);
        return $quote;
    }

    /**
     * Get the currently selected shipping address.
     *
     * @return Mage_Sales_Model_Quote_Address|null
     */
    public function getShippingAddress()
    {
        $shippingAddress = $this->_shippingAddress;
        if ($shippingAddress !== null) {
            return $shippingAddress;
        }

        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        /**
         * @todo check if this is needed for OSC
         */
//        if (!$shippingAddress || $shippingAddress->getSameAsBilling()) {
//            $shippingAddress = $quote->getBillingAddress();
//        }

        if (
            (!$shippingAddress->getPostcode() || $shippingAddress->getPostcode() == '-')
            && $shippingAddress->getId()
        ) {
            $shippingAddress = $quote->getCustomer()->getAddressById($shippingAddress->getCustomerAddressId());
        }
        $this->setShippingAddress($shippingAddress);

        return $shippingAddress;
    }

    /**
     * Get the currently selected shipping address's postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        $postcode = $this->getShippingAddress()->getPostcode();

        $postcode = str_replace(' ', '', strtoupper($postcode));

        return $postcode;
    }

    /**
     * Get the earliest possible delivery date.
     *
     * @return null|string
     */
    public function getDeliveryDate()
    {
        $deliveryDate = $this->_deliveryDate;

        if ($deliveryDate !== null) {
            return $deliveryDate;
        }

        $postcode = $this->getPostcode();

        try {
            $deliveryDate = $this->_getDeliveryDate($postcode);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);

            $tomorrow = strtotime('tomorrow', Mage::getModel('core/date')->timestamp());
            $deliveryDate = date('d-m-Y', $tomorrow);
        }

        $this->setDeliveryDate($deliveryDate);
        return $deliveryDate;
    }

    /**
     * Set the earliest possible delivery date.
     *
     * @param string $deliveryDate
     *
     * @return TIG_PostNL_Block_DeliveryOptions_Checkout_Onepage_DeliveryOptions
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->_deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * Get the fee charged for evening timeframes.
     *
     * @param boolean $formatted
     *
     * @return float
     */
    public function getEveningFee($formatted = false)
    {
        $storeId = Mage::app()->getStore()->getId();

        $eveningFee = (float) Mage::getStoreConfig(self::XPATH_EVENING_TIMEFRAME_FEE, $storeId);

        $price = $this->getPriceWithTax($eveningFee, true, $formatted);

        return $price;
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     *
     * @return float
     */
    public function getExpressFee($formatted = false)
    {
        $storeId = Mage::app()->getStore()->getId();

        $expressFee = (float) Mage::getStoreConfig(self::XPATH_PAKJEGEMAK_EXPRESS_FEE, $storeId);

        $price = $this->getPriceWithTax($expressFee, true, $formatted);

        return $price;
    }

    /**
     * Convert a value to a formatted price.
     *
     * @param float   $price
     * @param boolean $flag
     * @param boolean $formatted
     *
     * @return float
     *
     * @see Mage_Checkout_Block_Onepage_Shipping_Method_Available::getShippingPrice()
     */
    public function getPriceWithTax($price, $flag, $formatted = false)
    {
        $store = $this->getQuote()->getStore();

        $shippingPrice = Mage::helper('tax')->getShippingPrice($price, $flag, $this->getShippingAddress());
        $convertedPrice = $store->convertPrice($shippingPrice, $formatted, false);

        return $convertedPrice;
    }

    /**
     * Get either the evening or express fee as a float or int.
     *
     * @param string $type
     *
     * @return float|int
     */
    public function getFee($type) {
        switch ($type) {
            case 'evening':
                $fee = $this->getEveningFee();
                break;
            case 'express':
                $fee = $this->getExpressFee();
                break;
            default:
                return 0;
        }

        /**
         * If no fee is entered or an invalid value was entered, return an empty string.
         */
        if (!$fee || $fee > 2 || $fee < 0) {
            return 0;
        }

        return $fee;
    }

    /**
     * Get either the evening or the express fee as a currency value.
     *
     * @param string $type
     *
     * @return string
     */
    public function getFeeText($type)
    {
        switch ($type) {
            case 'evening':
                $feeText = $this->getEveningFee(true);
                break;
            case 'express':
                $feeText = $this->getExpressFee(true);
                break;
            default:
                return 0;
        }

        return $feeText;
    }

    /**
     * Gets an array containing the selected shipping address's streetname, housenumber and housenumber extension.
     *
     * @return array
     */
    public function getStreetData()
    {
        $storeId = Mage::app()->getStore()->getId();
        $address = $this->getShippingAddress();

        try {
            $streetData = Mage::helper('postnl/cif')->getStreetData($storeId, $address, false);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);

            $streetData = array(
                'streetname'           => '',
                'housenumber'          => '',
                'housenumberExtension' => '',
                'fullStreet'           => '',
            );
        }
        return $streetData;
    }

    /**
     * Checks whether PakjeGemak locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemak()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakjeGemak = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemak($storeId);
        return $canUsePakjeGemak;
    }

    /**
     * Checks whether PakjeGemak Express locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakjeGemakExpress = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemakExpress($storeId);
        return $canUsePakjeGemakExpress;
    }

    /**
     * Checks whether Pakket Automaat locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakketAutomaat()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUsePakketAutomaat($storeId);
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether timeframes are allowed.
     *
     * @return boolean
     */
    public function canUseTimeframes()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUseTimeframes($storeId);
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether evening timeframes are allowed.
     *
     * @return boolean
     */
    public function canUseEveningTimeframes()
    {
        $storeId = Mage::app()->getStore()->getId();

        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUseEveningTimeframes($storeId);
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether google streetview is allowed.
     *
     * @return boolean
     */
    public function canUseStreetView()
    {
        $storeId = Mage::app()->getStore()->getId();

        $streetviewAllowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_STREETVIEW, $storeId);
        return $streetviewAllowed;
    }

    /**
     * get the first possible delivery date from PostNL.
     *
     * @param string $postcode
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getDeliveryDate($postcode) {
        $postcode = str_replace(' ', '', strtoupper($postcode));

        $validator = new Zend_Validate_PostCode('nl_NL');
        if (!$validator->isValid($postcode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid postcode supplied for GetDeliveryDate request: %s Postcodes may only contain 4 numbers '
                        . 'and 2 letters.',
                     $postcode
                ),
                'POSTNL-0131'
            );
        }

        $cif = Mage::getModel('postnl_deliveryoptions/cif');
        $response = $cif->setStoreId(Mage::app()->getStore()->getId())
                        ->getDeliveryDate($postcode);

        return $response;
    }
}