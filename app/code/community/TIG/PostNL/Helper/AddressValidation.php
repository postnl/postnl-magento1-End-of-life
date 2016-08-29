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
 */
class TIG_PostNL_Helper_AddressValidation extends TIG_PostNL_Helper_Data
{
    /**
     * XML paths used to check if postcode check is activated.
     */
    const XPATH_CHECKOUT_EXTENSION = 'postnl/cif_labels_and_confirming/checkout_extension';
    const XPATH_USE_POSTCODE_CHECK = 'postnl/cif_labels_and_confirming/use_postcode_check';

    /**
     * Constants containing XML paths to cif address configuration options.
     */
    const XPATH_SPLIT_STREET                = 'postnl/cif_labels_and_confirming/split_street';
    const XPATH_STREETNAME_FIELD            = 'postnl/cif_labels_and_confirming/streetname_field';
    const XPATH_HOUSENUMBER_FIELD           = 'postnl/cif_labels_and_confirming/housenr_field';
    const XPATH_SPLIT_HOUSENUMBER           = 'postnl/cif_labels_and_confirming/split_housenr';
    const XPATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_labels_and_confirming/housenr_extension_field';

    /**
     * XML paths to flags that determine which environment allows the postcode check functionality.
     */
    const XPATH_POSTCODE_CHECK_IN_CHECKOUT    = 'postnl/cif_labels_and_confirming/postcode_check_in_checkout';
    const XPATH_POSTCODE_CHECK_IN_ADDRESSBOOK = 'postnl/cif_labels_and_confirming/postcode_check_in_addressbook';

    /**
     * XML paths that control some features of postcode check.
     */
    const XPATH_POSTCODE_CHECK_MAX_ATTEMPTS = 'postnl/cif_labels_and_confirming/postcode_check_max_attempts';
    const XPATH_POSTCODE_CHECK_TIMEOUT      = 'postnl/cif_labels_and_confirming/postcode_check_timeout';

    /**
     * Xpath to OSC street fields sort order.
     */
    const XPATH_STREET_FIELD_SORT_ORDER = 'onestepcheckout/sortordering_fields/street';

    /**
     * Log filename to log all cendris exceptions.
     */
    const CENDRIS_EXCEPTION_LOG_FILE = 'TIG_PostNL_Cendris_Exception.log';

    /**
     * Log filename to log cendris calls.
     */
    const CENDRIS_DEBUG_LOG_FILE = 'TIG_PostNL_Cendris_Debug.log';

    /**
     * Street lines used by postcode check.
     */
    const POSTCODE_CHECK_STREETNAME_FIELD             = 1;
    const POSTCODE_CHECK_HOUSE_NUMBER_FIELD           = 2;
    const POSTCODE_CHECK_HOUSE_NUMBER_EXTENSION_FIELD = 3;

    /**
     * XML path to community edition address lines configuration option
     */
    const XPATH_COMMUNITY_STREET_LINES = 'customer/address/street_lines';

    /**
     * Extension code of the PostcodeNL extension.
     */
    const POSTCODE_NL_EXTENSION_CODE = 'PostcodeNl_Api';

    /**
     * Xpath to the PostcodeNL extension's enabled field.
     */
    const XPATH_POSTCODE_NL_EXTENSION_ACTIVE = 'postcodenl_api/config/enabled';

    /**
     * Extension code of the OneStepCheckout extension.
     *
     * @todo move all OSC specific code to a separate OSC-helper class.
     */
    const ONESTEPCHECKOUT_EXTENSION_CODE = 'Idev_OneStepCheckout';

    /**
     * Xpath to the OneStepCheckout extension's enabled field.
     */
    const XPATH_ONESTEPCHECKOUT_EXTENSION_ACTIVE = 'onestepcheckout/general/rewrite_checkout_links';

    /**
     * Xpath to the OneStepCheckout extension's Google Places enabled field.
     */
    const XPATH_ONESTEPCHECKOUT_GOOGLE_PLACES_ENABLED = 'onestepcheckout/googleplaces/enabled';

    /**
     * Xpath to the OneStepCheckout extension's Delivery Date enabled field.
     */
    const XPATH_ONESTEPCHECKOUT_DELIVERY_DATE_ENABLED = 'onestepcheckout/delivery/enabled_date';

    /**
     * @var null|string|int
     */
    protected $_oscStreetFieldSortOrder = null;

    /**
     * @var array
     */
    protected $_lineCount = array();

    /**
     * @var array
     *
     * @todo cache this value
     */
    protected $_useSplitStreet = array();

    /**
     * @var array
     *
     * @todo cache this value
     */
    protected $_useSplitHouseNumber = array();

    /**
     * Gets the current street field sort order for OSC.
     *
     * @return int|string
     */
    public function getOscStreetFieldSortOrder()
    {
        if ($this->_oscStreetFieldSortOrder !== null) {
            return $this->_oscStreetFieldSortOrder;
        }

        $storeId = Mage::app()->getStore()->getId();
        $streetFieldOrder = Mage::getStoreConfig(self::XPATH_STREET_FIELD_SORT_ORDER, $storeId);

        $this->_oscStreetFieldSortOrder = $streetFieldOrder;
        return $streetFieldOrder;
    }

    /**
     * Checks whether the given store uses split address lines.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function useSplitStreet($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (isset($this->_useSplitStreet[$storeId])) {
            return $this->_useSplitStreet[$storeId];
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            $this->_useSplitStreet[$storeId] = true;
            return true;
        }

        $addressLines = $this->getAddressLineCount($storeId);
        if ($addressLines < 2) {
            $this->_useSplitStreet[$storeId] = false;
            return false;
        }

        $useSplitStreet = Mage::getStoreConfigFlag(self::XPATH_SPLIT_STREET, $storeId);
        if (!$useSplitStreet) {
            $this->_useSplitStreet[$storeId] = false;
            return false;
        }

        $streetField = $this->getStreetnameField();
        $houseNumberField = $this->getHousenumberField();
        if ($streetField == $houseNumberField) {
            $this->_useSplitStreet[$storeId] = false;
            return false;
        }

        $this->_useSplitStreet[$storeId] = true;
        return true;
    }

    /**
     * Checks whether the given store uses split house number values.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function useSplitHousenumber($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (isset($this->_useSplitHouseNumber[$storeId])) {
            return $this->_useSplitHouseNumber[$storeId];
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            $this->_useSplitHouseNumber[$storeId] = true;
            return true;
        }

        $useSplitHousenumber = Mage::getStoreConfigFlag(self::XPATH_SPLIT_HOUSENUMBER, $storeId);
        if (!$useSplitHousenumber) {
            $this->_useSplitHouseNumber[$storeId] = false;
            return false;
        }

        $houseNumberField = $this->getHousenumberField();
        $houseNumberExtensionField = $this->getHousenumberExtensionField();
        if ($houseNumberField == $houseNumberExtensionField) {
            $this->_useSplitHouseNumber[$storeId] = false;
            return false;
        }

        $this->_useSplitHouseNumber[$storeId] = true;
        return true;
    }

    /**
     * Gets the address field number used for the streetname field.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getStreetnameField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return self::POSTCODE_CHECK_STREETNAME_FIELD;
        }

        $streetnameField = (int) Mage::getStoreConfig(self::XPATH_STREETNAME_FIELD, $storeId);
        return $streetnameField;
    }

    /**
     * Gets the address field number used for the housenumber field.
     *
     * @param int|null $storeId
     *
     * @return int
     */

    public function getHousenumberField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return self::POSTCODE_CHECK_HOUSE_NUMBER_FIELD;
        }

        $housenumberField = (int) Mage::getStoreConfig(self::XPATH_HOUSENUMBER_FIELD, $storeId);
        return $housenumberField;
    }

    /**
     * Gets the address field number used for the housenumber extension field.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getHousenumberExtensionField($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($this->isPostcodeCheckEnabled($storeId)) {
            return self::POSTCODE_CHECK_HOUSE_NUMBER_EXTENSION_FIELD;
        }

        $housenumberExtensionField = (int) Mage::getStoreConfig(self::XPATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
        return $housenumberExtensionField;
    }

    /**
     * Gets the number of seconds before postcode check times out.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getPostcodeCheckTimeoutDelay($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $timeout = (int) Mage::getStoreConfig(self::XPATH_POSTCODE_CHECK_TIMEOUT, $storeId);
        if ($timeout < 1) {
            $timeout = 3600; // 1 hour
        }

        return $timeout;
    }

    /**
     * Gets the number of times a customer may attempt to enter their postcode and housenumber before postcode check disables
     * itself.
     *
     * @param int|null $storeId
     *
     * @return string|int
     */
    public function getPostcodeCheckMaxAttempts($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $maxAttempts = (int) Mage::getStoreConfig(self::XPATH_POSTCODE_CHECK_MAX_ATTEMPTS, $storeId);
        if (!$maxAttempts) {
            return 'false';
        }

        return $maxAttempts;
    }

    /**
     * Wrapper for the getAttributeValidationClass method to prevent errors in Magento 1.6.
     *
     * @param $attribute
     *
     * @return string
     */
    public function getAttributeValidationClass($attribute)
    {
        /** @var Mage_Customer_Helper_Address $addressHelper */
        $addressHelper = Mage::helper('customer/address');
        if (is_callable(array($addressHelper, 'getAttributeValidationClass'))) {
            return $addressHelper->getAttributeValidationClass($attribute);
        }

        return '';
    }

    /**
     * Check if the Postcode Check is active.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function isPostcodeCheckActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $checkoutExtension = Mage::getStoreConfig(self::XPATH_CHECKOUT_EXTENSION, $storeId);
        if (!$checkoutExtension || $checkoutExtension == 'other') {
            return false;
        }

        $usePostcodeCheck = Mage::getStoreConfigFlag(self::XPATH_USE_POSTCODE_CHECK, $storeId);
        return $usePostcodeCheck;
    }

    /**
     * Checks if the Postcode Check is enabled and ready for use.
     *
     * @param int|null $storeId
     *
     * @param bool     $environment
     *
     * @return boolean
     */
    public function isPostcodeCheckEnabled($storeId = null, $environment = false)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $postcodeNlExtensionActive = $this->checkPostcodeNlExtensionActive($storeId);
        if (true === $postcodeNlExtensionActive) {
            return false;
        }

        $isPostnlEnabled = $this->isEnabled($storeId);
        if (!$isPostnlEnabled) {
            return false;
        }

        $isPostcodeCheckActive = $this->isPostcodeCheckActive($storeId);
        if (!$isPostcodeCheckActive) {
            return false;
        }

        /**
         * Check to see if the postcode check functionality is allowed for the specified environment.
         */
        $environmentAllowed = false;
        switch ($environment) {
            case 'checkout':
                $environmentAllowed = Mage::getStoreConfigFlag(self::XPATH_POSTCODE_CHECK_IN_CHECKOUT, $storeId);
                break;
            case 'addressbook':
                $environmentAllowed = Mage::getStoreConfigFlag(self::XPATH_POSTCODE_CHECK_IN_ADDRESSBOOK, $storeId);
                break;
            case false:
                $environmentAllowed = true;
                break;
            //no default
        }

        return $environmentAllowed;
    }

    /**
     * Check if the Postcode.NL extension is installed and active.
     *
     * @param int|null $storeId
     *
     * @return boolean
     */
    public function checkPostcodeNlExtensionActive($storeId = null)
    {
        if (!Mage::helper('core')->isModuleEnabled(self::POSTCODE_NL_EXTENSION_CODE)) {
            return false;
        }

        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $extensionEnabled = Mage::getStoreConfigFlag(self::XPATH_POSTCODE_NL_EXTENSION_ACTIVE, $storeId);
        if (true === $extensionEnabled) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the OneStepCheckout extension is installed and active.
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function checkOneStepCheckoutActive($storeId = null)
    {
        if (!Mage::helper('core')->isModuleEnabled(self::ONESTEPCHECKOUT_EXTENSION_CODE)) {
            return false;
        }

        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $oneStepCheckoutEnabled = Mage::getStoreConfigFlag(self::XPATH_ONESTEPCHECKOUT_EXTENSION_ACTIVE, $storeId);
        if (!$oneStepCheckoutEnabled) {
            return false;
        }

        return true;
    }

    /**
     * Check if OneStepCheckout's Google Places functionality is active.
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function checkGooglePlacesActive($storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->checkOneStepCheckoutActive($storeId)) {
            return false;
        }

        $googlePlacesEnabled = Mage::getStoreConfigFlag(self::XPATH_ONESTEPCHECKOUT_GOOGLE_PLACES_ENABLED, $storeId);
        if (!$googlePlacesEnabled) {
            return false;
        }

        return true;
    }

    /**
     * Check if the OSC delivery date functionality is active.
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function checkOscDeliveryDateActive($storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->checkOneStepCheckoutActive($storeId)) {
            return false;
        }

        $oscDeliveryDateEnabled = Mage::getStoreConfigFlag(self::XPATH_ONESTEPCHECKOUT_DELIVERY_DATE_ENABLED, $storeId);
        if (!$oscDeliveryDateEnabled) {
            return false;
        }

        return true;
    }

    /**
     * Get the configured line count for the current, or specified, config scope.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getAddressLineCount($storeId = null)
    {
        if ($storeId) {
            $addressLineCount = $this->_lineCount;
            if (isset($addressLineCount[$storeId])) {
                return $addressLineCount[$storeId];
            }
        }

        if ($this->isEnterprise()) {
            $lineCount = $this->_getEnterpriseAddressLineCount($storeId);
        } else {
            $lineCount = $this->_getCommunityAddressLineCount($storeId);
        }

        if ($storeId) {
            $this->_lineCount[$storeId] = $lineCount;
        }

        return $lineCount;
    }

    /**
     * Get the address line count for Magento Community.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    protected function _getCommunityAddressLineCount($storeId = null)
    {
        /**
         * Get the allowed number of address lines based on the current scope
         */
        if (is_null($storeId)) {
            $request = Mage::app()->getRequest();

            if ($request->getParam('store')) {
                $lineCount = Mage::getStoreConfig(self::XPATH_COMMUNITY_STREET_LINES, $request->getParam('store'));
            } elseif ($request->getParam('website')) {
                /** @var Mage_Core_Model_Website $website */
                $website   = Mage::getModel('core/website')->load($request->getParam('website'), 'code');
                $lineCount = $website->getConfig(self::XPATH_COMMUNITY_STREET_LINES);
            } else {
                $lineCount = Mage::getStoreConfig(
                    self::XPATH_COMMUNITY_STREET_LINES,
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );
            }
            return $lineCount;
        }

        $lineCount = Mage::getStoreConfig(self::XPATH_COMMUNITY_STREET_LINES, $storeId);
        return $lineCount;
    }

    /**
     * Get the address line count for Magento Enterprise.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    protected function _getEnterpriseAddressLineCount($storeId = null)
    {
        if (is_null($storeId)) {
            $request = Mage::app()->getRequest();

            if ($request->getParam('store')) {
                $storeId = $request->getParam('store');
            } elseif ($request->getParam('website')) {
                /** @var Mage_Core_Model_Website $website */
                $website = Mage::getModel('core/website')->load($request->getParam('website'), 'code');
                $storeId = $website->getDefaultStore()->getId();
            } else {
                $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
            }
        }

        /** @var Mage_Customer_Helper_Address $addressHelper */
        $addressHelper = Mage::helper('customer/address');
        $lineCount = $addressHelper->getStreetLines($storeId);
        return $lineCount;
    }

    /**
     * Logs a cendris request and response for debug purposes.
     *
     * @param SoapClient $client
     *
     * @return TIG_PostNL_Helper_Webservices
     *
     * @see Mage::log()
     */
    public function logCendrisCall(SoapClient $client)
    {
        if (!$this->isLoggingEnabled()) {
            return $this;
        }

        $this->createLogDir();

        $requestXml = $this->formatXml($client->__getLastRequest());
        $responseXML = $this->formatXml($client->__getLastResponse());

        $logMessage = 'Request sent:'
                    . PHP_EOL
                    . $requestXml
                    . PHP_EOL
                    . 'Response received:'
                    . PHP_EOL
                    . $responseXML;

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CENDRIS_DEBUG_LOG_FILE;
        $this->log($logMessage, Zend_Log::DEBUG, $file);

        return $this;
    }

    /**
     * Logs a cendris exception in the database and/or a log file
     *
     * @param Mage_Core_Exception|TIG_PostNL_Exception|SoapFault $exception
     * @param SoapClient|boolean $client
     *
     * @return TIG_PostNL_Helper_Webservices
     *
     * @see Mage::logException()
     */
    public function logCendrisException($exception, $client = false)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }

        $logMessage = PHP_EOL . $exception->__toString();

        if ($client && $client instanceof SoapClient) {
            $requestXml = $this->formatXml($client->__getLastRequest());
            $responseXML = $this->formatXml($client->__getLastResponse());

            $logMessage .= PHP_EOL
                         . 'Request sent:'
                         . PHP_EOL
                         . $requestXml
                         . PHP_EOL
                         . 'Response received:'
                         . PHP_EOL
                         . $responseXML;
        }

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CENDRIS_EXCEPTION_LOG_FILE;
        $this->log($logMessage, Zend_Log::ERR, $file, false, true);

        return $this;
    }
}
