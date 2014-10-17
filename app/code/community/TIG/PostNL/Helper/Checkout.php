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
 */
class TIG_PostNL_Helper_Checkout extends TIG_PostNL_Helper_Data
{
    /**
     * XML path to checkout on/off switch
     */
    const XPATH_CHECKOUT_ACTIVE = 'postnl/checkout/active';

    /**
     * XML path to all PostNL Checkout payment methods.
     * N.B. last part of the XML path is missing.
     */
    const XPATH_CHECKOUT_PAYMENT_METHOD = 'postnl/checkout_payment_methods';

    /**
     * XML path for config options used to determine whether or not PostNL Checkout is available
     */
    const XPATH_SHOW_CHECKOUT_FOR_LETTER     = 'postnl/checkout/show_checkout_for_letter';
    const XPATH_SHOW_CHECKOUT_FOR_BACKORDERS = 'postnl/checkout/show_checkout_for_backorders';

    /**
     * Log filename to log all non-specific PostNL debug messages
     */
    const POSTNL_DEBUG_LOG_FILE = 'TIG_PostNL_Checkout_Debug.log';

    /**
     * Array of payment methods supported by PostNL Checkout.
     * Keys are the names used in system.xml, values are codes used by PostNL Checkout.
     *
     * @var array
     */
    protected $_checkoutPaymentMethods = array(
        'ideal'                  => 'IDEAL',
        'creditcard'             => 'CREDITCARD',
        'checkpay'               => 'CHECKPAY',
        'paypal'                 => 'PAYPAL',
        'directdebit'            => 'MACHTIGING',
        'acceptgiro'             => 'ACCEPTGIRO',
        'vooraf_betalen'         => 'VOORAF',
        'termijnen'              => 'TERMIJNEN',
        'giftcard'               => 'KADOBON',
        'rabobank_internetkassa' => 'RABOINTKASSA',
        'afterpay'               => 'AFTERPAY',
        'klarna'                 => 'KLARNA',
    );

    /**
     * An array of required configuration settings
     *
     * @var array
     */
    protected $_checkoutRequiredFields = array(
        'postnl/checkout/active',
        'postnl/cif/webshop_id',
        'postnl/cif/public_webshop_id',
    );

    /**
     * Array containing conversions between PostNL Checkout payment option fields and those used by Magento payment
     * methods. This array should be extended as time goes on in order to support as many payment methods as possible.
     *
     * @var array
     */
    protected $_optionConversionArray = array(
        'sisow' => array(
            '0031' => '01',
            '0721' => '05',
            '0021' => '06',
            '0751' => '07',
            '0761' => '02',
            '0771' => '08',
            '0091' => '04',
            '0511' => '09',
            '0161' => '10',
         ),
         'buckaroo3extended_ideal' => array(
            '0031' => 'ABNANL2A',
            '0081' => 'FRBKNL2L',
            '0721' => 'INGBNL2A',
            '0021' => 'RABONL2U',
            '0751' => 'SNSBNL2A',
            '0761' => 'ASNBNL21',
            '0771' => 'RBRBNL21',
            '0091' => 'FRBKNL2L',
            '0511' => 'TRIONL2U',
            '0161' => 'FVLBNL22',
         ),
    );

    /**
     * Gets a list of payment methods supported by PostNL Checkout
     *
     * @return array
     */
    public function getCheckoutPaymentMethods()
    {
        $paymentMethods = $this->_checkoutPaymentMethods;
        return $paymentMethods;
    }

    /**
     * Returns an array of configuration settings that must be entered for PostNL Checkout to function
     *
     * @return array
     */
    public function getCheckoutRequiredFields()
    {
        $requiredFields = $this->_checkoutRequiredFields;
        return $requiredFields;
    }

    /**
     * Returns a conversion array used to convert PostNL Checkout payment method fields to those used by Magento payment
     * methods.
     *
     * @return array
     */
    public function getOptionConversionArray()
    {
        $conversionArray = array(
            'conversion_array' => $this->_optionConversionArray
        );

        $conversionObject = new Varien_Object($conversionArray);

        /**
         * You can observe this event in order to add (or modify) conversion options. This prevents you from having to
         * overload this helper if you want to change this functionality.
         */
        Mage::dispatchEvent(
            'postnl_checkout_option_conversion_before',
            array(
                'conversion_object' => $conversionObject,
            )
        );

        return $conversionObject->getConversionArray();
    }

    /**
     * Restores a quote to working order
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function restoreQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setIsActive(true)
              ->save();

        return $quote;
    }

    /**
     * Check if PostNL Checkout may be used for a specified quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param boolean $sendPing
     *
     * @return boolean
     */
    public function canUsePostnlCheckout(Mage_Sales_Model_Quote $quote, $sendPing = false)
    {
        if (Mage::registry('can_use_postnl_checkout') !== null) {
            return Mage::registry('can_use_postnl_checkout');
        }

        $checkoutEnabled = $this->isCheckoutEnabled();
        if (!$checkoutEnabled) {
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }

        /**
         * PostNL Checkout cannot be used for virtual orders
         */
        if ($quote->isVirtual()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0104',
                    'message' => $this->__('The quote is virtual.'),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }

        /**
         * Check if the quote has a valid minimum amount
         */
        if (!$quote->validateMinimumAmount()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0105',
                    'message' => $this->__("The quote's grand total is below the minimum amount required."),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }

        /**
         * Check that dutch addresses are allowed
         */
        if (!$this->canUseStandard()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0106',
                    'message' => $this->__(
                        'No standard product options are enabled. At least 1 option must be active.'
                    ),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }

        $storeId = $quote->getStoreId();

        /**
         * Check if the quote is a letter box parcel.
         */
        $isLetterQuote = $this->quoteIsBuspakje($quote);
        if ($isLetterQuote) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0101',
                    'message' => $this->__(
                        "The quote fits as a letter box parcel."
                    ),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }

        /**
         * Check if PostNL Checkout may be used for out-og-stock orders and if not, whether the quote has any such
         * products.
         */
        $showCheckoutForBackorders = Mage::getStoreConfigFlag(self::XPATH_SHOW_CHECKOUT_FOR_BACKORDERS, $storeId);
        if (!$showCheckoutForBackorders) {
            $containsOutOfStockItems = $this->quoteHasOutOfStockItems($quote);
            if ($containsOutOfStockItems) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0102',
                        'message' => $this->__('One or more items in the cart are out of stock.'),
                    )
                );
                Mage::register('postnl_checkout_is_enabled_errors', $errors);
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
        }

        if ($sendPing === true) {
            /**
             * Send a ping request to see if the PostNL Checkout service is available
             */
            try {
                $cif = Mage::getModel('postnl_checkout/cif');
                $result = $cif->ping();
            } catch (Exception $e) {
                $this->logException($e);
                $result = false;
            }

            if ($result !== 'OK') {
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
        }

        Mage::register('can_use_postnl_checkout', true);
        return true;
    }

    /**
     * Checks if a quote is a letter.
     * For now it only checks if the total weight of the quote is less than 2 KG
     *
     * @param mixed $quoteItems Either a quote object, or an array or collection of quote items
     * @param null|int $storeId
     *
     * @return boolean
     *
     * @deprecated 1.3.2 This method has been replaced by TIG_PostNL_Helper_Data::isBuspakjeConfigApplicableToQuote()
     */
    public function quoteIsLetter($quoteItems, $storeId = null)
    {
        if ($quoteItems instanceof Mage_Sales_Model_Quote) {
            $quoteItems = $quoteItems->getAllItems();
        }

        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $totalWeight = 0;
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quoteItems as $item) {
            $totalWeight += ($item->getWeight() * $item->getQty());
        }

        $kilograms = $this->standardizeWeight($totalWeight, $storeId);

        if ($kilograms < self::MAX_LETTER_BOX_PARCEL_WEIGHT) {
            return true;
        }

        return false;
    }

    /**
     * Check if a quote has out of stock products
     *
     * @param mixed $quoteItems Either a quote object, or an array or collection of quote items
     *
     * @return boolean
     */
    public function quoteHasOutOfStockItems($quoteItems)
    {
        if ($quoteItems instanceof Mage_Sales_Model_Quote) {
            $quoteItems = $quoteItems->getAllItems();
        }

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();

            if (!$product->isInStock()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if PostNL Checkout is active
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isCheckoutActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $isActive = Mage::getStoreConfigFlag(self::XPATH_CHECKOUT_ACTIVE, $storeId);
        return $isActive;
    }

    /**
     * Check if PostNL checkout is enabled
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isCheckoutEnabled($storeId = null)
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCheckoutIsEnabled()) {
            return $cache->getPostnlCheckoutIsEnabled();
        }

        $isEnabled = $this->_isCheckoutEnabled($storeId);

        if ($cache) {
            $cache->setPostnlCheckoutIsEnabled($isEnabled)
                  ->saveCache();
        }

        return $isEnabled;
    }

    /**
     * Checks if PostNl Checkout is enabled by running various checks including a check to see if the required
     * configuration fields are filled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function _isCheckoutEnabled($storeId)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $isPostnlEnabled = $this->isEnabled($storeId);
        if ($isPostnlEnabled === false) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0107',
                    'message' => $this->__('You have not yet enabled the PostNL extension.'),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            return false;
        }

        $isCheckoutActive = $this->isCheckoutActive($storeId);
        if (!$isCheckoutActive) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0027',
                    'message' => $this->__('You have not yet enabled PostNL Checkout.'),
                )
            );
            Mage::register('postnl_checkout_is_enabled_errors', $errors);
            return false;
        }

        $isConfigured = $this->isCheckoutConfigured($storeId);
        if (!$isConfigured) {
            return false;
        }

        return true;
    }

    /**
     * Check if all required fields are entered
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isCheckoutConfigured($storeId = null)
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCheckoutIsConfigured()) {
            return $cache->getPostnlCheckoutIsConfigured();
        }

        $isConfigured = $this->_isCheckoutConfigured($storeId);

        if ($cache) {
            $cache->setPostnlCheckoutIsConfigured($isConfigured)
                  ->saveCache();
        }

        return $isConfigured;
    }

    /**
     * Checks if all required fields are configured. If not, returns an array of errors.
     *
     * @param int $storeId
     *
     * @return array
     */
    protected function _isCheckoutConfigured($storeId)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        /**
         * First check if all required configuration settings are entered
         */
        $requiredFields = $this->getCheckoutRequiredFields();

        $errors = $this->_getFieldsConfiguredErrors($requiredFields, $storeId);

        /**
         * If any errors were detected, add them to the registry and return false
         */
        if (!empty($errors)) {
            Mage::register('postnl_checkout_is_configured_errors', $errors);
            return false;
        }

        /**
         * Go through each supported payment method. At least one of them must be activated.
         */
        $paymentMethods = $this->getCheckoutPaymentMethods();
        $paymentMethodSettings = Mage::getStoreConfig(self::XPATH_CHECKOUT_PAYMENT_METHOD, $storeId);
        foreach ($paymentMethods as $methodCode => $method) {
            if (array_key_exists($methodCode, $paymentMethodSettings)
                && $paymentMethodSettings[$methodCode] === '1'
            ) {
                return true;
            }
        }

        /**
         * If no payment method was activated the extension is not configured properly
         */
        $errors = array(
            array(
                'code'    => 'POSTNL-0028',
                'message' => $this->__('You need to enable at least one payment method.'),
            )
        );

        if ($this->isAdmin()) {
            $this->saveConfigState(array('postnl_checkout_payment_methods' => 1));
        }

        Mage::register(
            'postnl_checkout_is_configured_errors',
            $errors
        );
        return false;
    }
}
