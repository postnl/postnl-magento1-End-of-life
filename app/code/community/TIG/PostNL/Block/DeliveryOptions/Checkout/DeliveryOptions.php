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
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setStreetnameField(int $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setHousenumberField(int $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setTaxDisplayType(int $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setMethodName(string $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setMethodRate(float $value)
 * @method TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions setMethodCode(string $value)
 *
 * @method boolean                                                   hasTaxDisplayType()
 * @method boolean                                                   hasMethodName()
 * @method boolean                                                   hasMethodRate()
 * @method boolean                                                   hasMethodCode()
 *
 * @method Mage_Sales_Model_Quote_Address_Rate                       getRate()
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
     * Xpath to the 'stated_address_only_checked' setting.
     */
    const XPATH_STATED_ADDRESS_ONLY_CHECKED = 'postnl/delivery_options/stated_address_only_checked';

    /**
     * Shipping method code used by PostNL matrix rate.
     */
    const POSTNL_MATRIX_RATE_CODE = 'postnl_matrixrate';

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
     * Get the current shipping method's name.
     *
     * @return string
     */
    public function getMethodName()
    {
        if ($this->hasMethodName()) {
            return $this->_getData('method_name');
        }

        $rate = $this->getRate();
        if (!$rate) {
            return '';
        }

        $methodCode = $this->getMethodCode();
        $methodName = 's_method_' . $methodCode;

        $this->setMethodName($methodName);
        return $methodName;
    }

    /**
     * Get the current shipping method's rate.
     *
     * @return float|int
     */
    public function getMethodRate()
    {
        if ($this->hasMethodRate()) {
            return $this->_getData('method_rate');
        }

        $rate = $this->getRate();
        if (!$rate) {
            return 0;
        }

        $methodRate = $rate->getPrice();

        $this->setMethodRate($methodRate);
        return $methodRate;
    }

    /**
     * Get the current shipping method's code.
     *
     * @return string
     */
    public function getMethodCode()
    {
        if ($this->hasMethodCode()) {
            return $this->_getData('method_code');
        }

        $rate = $this->getRate();
        if (!$rate) {
            return '';
        }

        $methodCode = $rate->getCode();

        $this->setMethodCode($methodCode);
        return $methodCode;
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
     * Check if a fee is set for this option.
     *
     * @param string $option
     *
     * @return bool
     */
    public function hasOptionFee($option)
    {
        $fee = $this->getOptionFee($option);

        if ($fee > 0) {
            return true;
        }

        return false;
    }

    /**
     * Gets the configured fee for a specified option.
     *
     * @param string $option
     * @param bool  $formatted
     * @param bool  $includingTax
     * @param bool  $convert
     *
     * @return float|int
     */
    public function getOptionFee($option, $formatted = false, $includingTax = true, $convert = true)
    {
        return Mage::helper('postnl/deliveryOptions')->getOptionFee($option, $formatted, $includingTax, $convert);
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
            case 'pakje_gemak':
                $fee = $this->getPakjeGemakFee(false, $includingTax);
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
            case 'pakje_gemak':
                $feeText = $this->getPakjeGemakFee(true, $includingTax);
                break;
            default:
                return 0;
        }

        return $feeText;
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
     * Get the fee for PakjeGemak locations. This is the difference between the current shipping rate and the shipping
     * rate specifically for parcel.
     *
     * This method is only applicable for buspakje shipments where PakjeGemak locations are also allowed.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return int
     */
    public function getPakjeGemakFee($formatted = false, $includingTax = true)
    {
        if (!$this->getIsBuspakje()) {
            return 0;
        }

        if (!$this->canUsePakjeGemak()
            && !$this->canUsePakjeGemakExpress()
            && !$this->canUsePakketAutomaat()
        ) {
            return 0;
        }

        $shippingMethod = $this->getMethodCode();
        if (self::POSTNL_MATRIX_RATE_CODE !== $shippingMethod) {
            return 0;
        }

        $currentRate = $this->getMethodRate();

        return Mage::helper('postnl/deliveryOptions')->getPakjeGemakFee($currentRate, $formatted, $includingTax);
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
     * Check if using the responsive design is allowed.
     *
     * @return bool
     */
    public function canUseResponsive()
    {
        $canUseResponsive = Mage::helper('postnl/deliveryOptions')->canUseResponsive();
        return $canUseResponsive;
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
     * Check whether the 'only_stated_address' option can be shown.
     *
     * @return boolean
     */
    public function canShowOnlyStatedAddressOption()
    {
        $canShowOnlyStatedAddressOptions = Mage::helper('postnl/deliveryOptions')->canShowOnlyStatedAddressOption();
        return $canShowOnlyStatedAddressOptions;
    }

    /**
     * Check if the 'only_stated_address' option should be checked.
     *
     * @return bool
     */
    public function isOnlyStatedAddressOptionChecked()
    {
        $isOnlyStatedAddressOptionChecked = Mage::helper('postnl/deliveryOptions')->isOnlyStatedAddressOptionChecked();
        return $isOnlyStatedAddressOptionChecked;
    }

    /**
     * Check if separate rates should be shown for delivery and pick-up.
     *
     * @return boolean
     */
    public function canShowSeparateRates()
    {
        if (!$this->getIsBuspakje()) {
            return false;
        }

        if (!$this->canUsePakjeGemak()) {
            return false;
        }

        if ($this->getPakjeGemakFee() < 0.01) {
            return false;
        }

        return true;
    }

    /**
     * Get whether this order is a buspakje order.
     *
     * @return bool
     */
    public function getIsBuspakje()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if buspakje can be used.
         */
        if (!$helper->canUseBuspakje()) {
            return false;
        }

        /**
         * Check if the current quote fits as a letter box parcel.
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (!$helper->quoteIsBuspakje($quote)) {
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

        $response = Mage::helper('postnl/deliveryOptions')->getValidDeliveryDate($response)->format('d-m-Y');

        return $response;
    }
}