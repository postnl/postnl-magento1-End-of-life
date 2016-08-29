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
     * Xpath to the 'timeout' setting.
     */
    const XPATH_DELIVERY_OPTIONS_TIMEOUT = 'postnl/delivery_options/ajax_timeout';

    /**
     * Shipping method code used by PostNL matrix rate.
     */
    const POSTNL_MATRIX_RATE_CODE = 'postnl_matrixrate';

    /**
     * Default shipping address country.
     */
    const DEFAULT_SHIPPING_COUNTRY = 'NL';

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

        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

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

        /** @var Mage_Customer_Helper_Data $customerHelper */
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
            /** @var Mage_Customer_Model_Session $session */
            $session = Mage::getSingleton('customer/session');
            $shippingAddress = $session->getCustomer()->getDefaultShippingAddress();
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
     * Get the currently selected shipping address's country.
     *
     * @return string
     */
    public function getCountry()
    {
        $country = $this->getShippingAddress()->getCountryId();

        if (!$country) {
            $country = self::DEFAULT_SHIPPING_COUNTRY;
        }

        return $country;
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
        $storeId  = $quote->getStoreId();
        $postcode = $this->getPostcode();
        $country  = $this->getCountry();

        try {
            $deliveryDate = $this->_getDeliveryDate($postcode, $country, $quote);
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Date $helper */
            $helper = Mage::helper('postnl/date');
            $helper->logException($e);

            $deliveryDate = $helper->getDeliveryDate('now' ,$storeId)
                                   ->format('d-m-Y');
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
        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getOptionFee($option, $formatted, $includingTax, $convert);
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
            case 'sunday':
                $fee = $this->getSundayFee(false, $includingTax);
                break;
            case 'sameday':
                $fee = $this->getSameDayFee(false, $includingTax);
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
            case 'sunday':
                $feeText = $this->getSundayFee(true, $includingTax);
                break;
            case 'sameday':
                $feeText = $this->getSameDayFee(true, $includingTax);
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
        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getEveningFee($formatted, $includingTax);
    }

    /**
     * Get the fee charged for sunday delivery.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getSundayFee($formatted = false, $includingTax = true)
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getSundayFee($formatted, $includingTax);
    }

    /**
     * Get the fee charged for same day delivery.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     *
     * @return float
     */
    public function getSameDayFee($formatted = false, $includingTax = true)
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getSameDayFee($formatted, $includingTax);
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
        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getExpressFee($formatted, $includingTax);
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

        /** @var TIG_PostNL_Helper_DeliveryOptions_Fee $helper */
        $helper = Mage::helper('postnl/deliveryOptions_fee');
        return $helper->getPakjeGemakFee($currentRate, $formatted, $includingTax);
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

        /** @var TIG_PostNL_Helper_AddressValidation $helper */
        $helper = Mage::helper('postnl/addressValidation');
        $streetnameField = $helper->getStreetnameField();

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

        /** @var TIG_PostNL_Helper_AddressValidation $helper */
        $helper = Mage::helper('postnl/addressValidation');
        $housenumberField = $helper->getHousenumberField();

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

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        try {
            $streetData = $helper->getStreetData($storeId, $address, false);
        } catch (Exception $e) {
            $helper->logException($e);

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

        /** @var Mage_Tax_Model_Config $config */
        $config = Mage::getSingleton('tax/config');
        $taxDisplayType = $config->getShippingPriceDisplayType();

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
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUsePakjeGemak = $helper->canUsePakjeGemak();
        return $canUsePakjeGemak;
    }

    /**
     * Checks whether PakjeGemak Express locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUsePakjeGemakExpress = $helper->canUsePakjeGemakExpress();
        return $canUsePakjeGemakExpress;
    }

    /**
     * Checks whether Pakketautomaat locations are allowed.
     *
     * @return boolean
     */
    public function canUsePakketAutomaat()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUsePakketAutomaat = $helper->canUsePakketAutomaat();
        return $canUsePakketAutomaat;
    }

    /**
     * Checks whether delivery days are allowed.
     *
     * @return boolean
     */
    public function canUseDeliveryDays()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseDeliveryDays = $helper->canUseDeliveryDays();
        return $canUseDeliveryDays;
    }

    /**
     * Checks whether time frames are allowed.
     *
     * @return boolean
     */
    public function canUseTimeframes()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseTimeframes = $helper->canUseTimeframes();
        return $canUseTimeframes;
    }

    /**
     * Checks whether evening time frames are allowed.
     *
     * @return boolean
     */
    public function canUseEveningTimeframes()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseEveningTimeframes = $helper->canUseEveningTimeframes();
        return $canUseEveningTimeframes;
    }

    /**
     * Checks whether the fallback timeframe should be shown or not.
     *
     * @return boolean
     */
    public function canUseFallBackTimeframe()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseFallBackTimeframe = $helper->canUseFallBackTimeframe();
        return $canUseFallBackTimeframe;
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
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseResponsive = $helper->canUseResponsive();
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
        /** @noinspection PhpUndefinedFieldInspection */
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
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canShowOnlyStatedAddressOptions = $helper->canShowOnlyStatedAddressOption();
        return $canShowOnlyStatedAddressOptions;
    }

    /**
     * Check if the 'only_stated_address' option should be checked.
     *
     * @return bool
     */
    public function isOnlyStatedAddressOptionChecked()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $isOnlyStatedAddressOptionChecked = $helper->isOnlyStatedAddressOptionChecked();
        return $isOnlyStatedAddressOptionChecked;
    }

    /**
     * Check if separate rates should be shown for delivery and pick-up.
     *
     * @return boolean
     */
    public function canShowSeparateRates()
    {
        if (!$this->canUsePakjeGemak()) {
            return false;
        }

        if (abs($this->getPakjeGemakFee()) < 0.01) {
            return false;
        }

        return true;
    }

    /**
     * Check if the sunday sorting (AKA monday delivery) is allowed.
     *
     * @return bool
     */
    public function canUseSundaySorting()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $canUseSundaySorting = $helper->canUseSundaySorting();
        return $canUseSundaySorting;
    }

    /**
     * Get whether this order is a buspakje order.
     *
     * @return bool
     */
    public function getIsBuspakje()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
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
        $quote = $this->getQuote();
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
        /** @var TIG_PostNL_Helper_Data $helper */
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
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');

        $quote = $this->getQuote();
        if ($helper->canUseDeliveryOptions($quote) && $helper->canUseDeliveryOptionsForCountry($quote)) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getDeliveryOptionsAjaxTimeout()
    {
        $timeout = (int) Mage::getStoreConfig(self::XPATH_DELIVERY_OPTIONS_TIMEOUT, Mage::app()->getStore()->getid());

        if ($timeout < 1) {
            $timeout = 5;
        }

        return $timeout;
    }

    /**
     * get the first possible delivery date from PostNL.
     *
     * @param string                 $postcode
     * @param string                 $country
     * @param Mage_Sales_Model_Quote $quote
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getDeliveryDate($postcode, $country, Mage_Sales_Model_Quote $quote) {
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

        if ($country != 'NL' && $country != 'BE') {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid country supplied for GetDeliveryDate request: %s. Only "NL" and "BE" are allowed.',
                    $postcode
                ),
                'POSTNL-0235'
            );
        }

        /** @var TIG_PostNL_Model_DeliveryOptions_Cif $cif */
        $cif = Mage::getModel('postnl_deliveryoptions/cif');
        $response = $cif->setStoreId(Mage::app()->getStore()->getId())
                        ->getDeliveryDate($postcode, $country, $quote);

        /** @var TIG_PostNL_Helper_Date $helper */
        $helper = Mage::helper('postnl/date');

        $dateObject = new DateTime($response, new DateTimeZone('UTC'));
        $correction = $helper->getDeliveryDateCorrection($dateObject);
        $dateObject->add(new DateInterval("P{$correction}D"));

        return $dateObject->format('d-m-Y');
    }

    /**
     * We added addslashes here to escape ' characters.
     *
     * @param mixed $data
     * @param null  $allowedTags
     *
     * @return string
     */
    public function escapeJavascriptHtml($data, $allowedTags = null)
    {
        if (is_string($data)) {
            return addslashes(parent::escapeHtml($data, $allowedTags));
        } else {
            return parent::escapeHtml($data, $allowedTags);
        }
    }
}
