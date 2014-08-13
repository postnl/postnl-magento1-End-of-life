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
 * @method string                                                    getMethodName()
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setStreetnameField(int $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setHousenumberField(int $value)
 * @method boolean                                                   hasTaxDisplayType()
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setTaxDisplayType(int $value)
 */
class TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions extends TIG_PostNL_Block_DeliveryOptions_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_deliveryoptions_checkout_deliveryoptions';

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
     * @return $this
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->_shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Gets the current quote.
     *
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

        $shippingAddress = $this->_getShippingAddress();

        if (!$shippingAddress) {
            $shippingAddress = Mage::getModel('sales/quote_address');
        }

        $this->setShippingAddress($shippingAddress);
        return $shippingAddress;
    }

    /**
     * Gets a shipping address from the current quote or from the customer if the customer is logged in.
     *
     * @return Mage_Customer_Model_Address|Mage_Sales_Model_Quote_Address
     */
    protected function _getShippingAddress()
    {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        /**
         * If we have billing data, the customer may still be entering their address, so we shouldn't do anything else
         * for now.
         */
        if (Mage::app()->getRequest()->getPost('billing')) {
            return $shippingAddress;
        }

        $customerHelper = Mage::helper('customer');

        /**
         * OneStepCheckout sometimes stores a partial shipping address in the quote. This hack is meant to detect this
         * and retrieve the full address from the customer's account if available. This is only done if we have no
         * billing data in the $_POST superglobal to prevent conflicts with customers still entering their address.
         */
        if (
            (
                !$shippingAddress->getPostcode()
                || $shippingAddress->getPostcode() == '-'
                || !$shippingAddress->getStreetFull()
            )
            && $shippingAddress->getId()
            && $shippingAddress->getCustomerAddressId()
            && $customerHelper->isLoggedIn()
        ) {
            $shippingAddress = $quote->getCustomer()->getAddressById($shippingAddress->getCustomerAddressId());
        }

        /**
         * If we still don't have a full address, get the customer's default shipping address if available.
         */
        if (
            (
                !$shippingAddress->getPostcode()
                || $shippingAddress->getPostcode() == '-'
                || !$shippingAddress->getStreetFull()
            )
            && $customerHelper->isLoggedIn()
            && $customerHelper->customerHasAddresses()
        ) {
            $shippingAddress = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShippingAddress();
        }

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

        $quote    = $this->getQuote();
        $postcode = $this->getPostcode();

        try {
            $deliveryDate = $this->_getDeliveryDate($postcode, $quote);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);

            $shippingDuration = Mage::helper('postnl/deliveryOptions')->getDeliveryDate(null, null, true);

            $nextDeliveryDay = new DateTime();
            $nextDeliveryDay->setTimestamp(Mage::getModel('core/date')->timestamp());
            $nextDeliveryDay->add(new DateInterval("P{$shippingDuration}D"));

            $deliveryDate = $nextDeliveryDay->format('d-m-Y');
        }

        $this->setDeliveryDate($deliveryDate);
        return $deliveryDate;
    }

    /**
     * Set the earliest possible delivery date.
     *
     * @param string $deliveryDate
     *
     * @return $this
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
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getEveningFee($formatted = false, $includingTax = true)
    {
        return Mage::helper('postnl/deliveryOptions')->getEveningFee($formatted, $includingTax);
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getExpressFee($formatted = false, $includingTax = true)
    {
        return Mage::helper('postnl/deliveryOptions')->getExpressFee($formatted, $includingTax);
    }

    /**
     * Get either the evening or express fee as a float or int.
     *
     * @param string  $type
     * @param boolean $includingTax
     *
     * @return float|int
     */
    public function getFee($type, $includingTax = false) {
        switch ($type) {
            case 'evening':
                $fee = $this->getEveningFee(false, $includingTax);
                break;
            case 'express':
                $fee = $this->getExpressFee(false, $includingTax);
                break;
            default:
                return 0;
        }

        return $fee;
    }

    /**
     * Get either the evening or the express fee as a currency value.
     *
     * @param string  $type
     * @param boolean $includingTax
     *
     * @return string
     */
    public function getFeeText($type, $includingTax = false)
    {
        switch ($type) {
            case 'evening':
                $feeText = $this->getEveningFee(true, $includingTax);
                break;
            case 'express':
                $feeText = $this->getExpressFee(true, $includingTax);
                break;
            default:
                return 0;
        }

        return $feeText;
    }

    /**
     * Get the field used for the address's streetname.
     *
     * @return int
     */
    public function getStreetnameField()
    {
        if ($this->hasData('streetname_field')) {
            return $this->_getData('streetname_field');
        }

        $streetnameField = Mage::helper('postnl/addressValidation')->getStreetnameField();

        $this->setStreetnameField($streetnameField);
        return $streetnameField;
    }

    /**
     * Get the field used for the address's housenumber.
     *
     * @return int
     */
    public function getHousenumberField()
    {
        if ($this->hasData('housenumber_field')) {
            return $this->_getData('housenumber_field');
        }

        $housenumberField = Mage::helper('postnl/addressValidation')->getHousenumberField();

        $this->setHousenumberField($housenumberField);
        return $housenumberField;
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
     * Gets tax display type.
     *
     * @return int
     */
    public function getTaxDisplayType()
    {
        if ($this->hasTaxDisplayType()) {
            return $this->_getData('tax_display_type');
        }

        $taxDisplayType = Mage::getSingleton('tax/config')->getShippingPriceDisplayType();

        $this->setTaxDisplayType($taxDisplayType);
        return $taxDisplayType;
    }

    /**
     * Checks whether PakjeGemak locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemak()
    {
        $canUsePakjeGemak = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemak();
        return $canUsePakjeGemak;
    }

    /**
     * Checks whether PakjeGemak Express locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress()
    {
        $canUsePakjeGemakExpress = Mage::helper('postnl/deliveryOptions')->canUsePakjeGemakExpress();
        return $canUsePakjeGemakExpress;
    }

    /**
     * Checks whether Pakketautomaat locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakketAutomaat()
    {
        $canUsePakketAutomaat = Mage::helper('postnl/deliveryOptions')->canUsePakketAutomaat();
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether delivery days are allowed.
     *
     * @return boolean
     */
    public function canUseDeliveryDays()
    {
        $canUseDeliveryDays = Mage::helper('postnl/deliveryOptions')->canUseDeliveryDays();
        return $canUseDeliveryDays;
    }

    /**
     * Checks whether time frames are allowed.
     *
     * @return boolean
     */
    public function canUseTimeframes()
    {
        $canUseTimeframes = Mage::helper('postnl/deliveryOptions')->canUseTimeframes();
        return $canUseTimeframes;
    }

    /**
     * Checks whether evening time frames are allowed.
     *
     * @return boolean
     */
    public function canUseEveningTimeframes()
    {
        $canUseEveningTimeframes = Mage::helper('postnl/deliveryOptions')->canUseEveningTimeframes();
        return $canUseEveningTimeframes;
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
     * Checks whether the current theme uses cufon.
     *
     * @return boolean
     */
    public function getUseCufon()
    {
        /**
         * @var Varien_Simplexml_Element $theme
         */
        $theme = $this->getCurrentTheme();
        if (!$theme) {
            return false;
        }

        /**
         * @var Varien_Simplexml_Element $files
         */
        $useCufon = (string) $theme->use_cufon;
        if (!$useCufon) {
            return false;
        }

        if ($useCufon === '1' || strcasecmp($useCufon, 'true') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Get whether this order is a buspakje order.
     *
     * @return bool
     */
    public function getIsBuspakje()
    {
        /**
         * Check if the buspakje calculation mode is set to automatic.
         */
        $helper = Mage::helper('postnl');
        $calculationMode = $helper->getBuspakjeCalculationMode();
        if ($calculationMode != 'automatic') {
            return false;
        }

        /**
         * Check if the current quote fits as a letter box parcel.
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$helper->fitsAsBuspakje($quote->getAllItems())) {
            return false;
        }

        return true;
    }

    /**
     * Checks if debug mode is allowed. Debug mode is enabled if the PostNl extension's debug mode is set to 'full'.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        $helper = Mage::helper('postnl');
        $debugMode = $helper->getDebugMode();

        if ($debugMode > 1) {
            return true;
        }

        return false;
    }

    /**
     * Checks if delivery options are allowed for the current quote.
     *
     * @return bool
     */
    public function canUseDeliveryOptions()
    {
        $helper = Mage::helper('postnl/deliveryOptions');

        $quote = $this->getQuote();
        if ($helper->canUseDeliveryOptions($quote) && $helper->canUseDeliveryOptionsForCountry($quote)) {
            return true;
        }

        return false;
    }

    /**
     * get the first possible delivery date from PostNL.
     *
     * @param string                 $postcode
     * @param Mage_Sales_Model_Quote $quote
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getDeliveryDate($postcode, Mage_Sales_Model_Quote $quote) {
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
                        ->getDeliveryDate($postcode, $quote);

        return $response;
    }
}