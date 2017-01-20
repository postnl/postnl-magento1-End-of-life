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
 * Base CIF model. Contains general code for communicating with the CIF API
 *
 * @method boolean getTestMode()
 *
 * @method TIG_PostNL_Model_Core_Cif_Abstract setHelper(Mage_Core_Helper_Abstract $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setSoapClient(SoapClient $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setTestMode(boolean $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setPassword(string $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setUsername(string $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setStoreId(int $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setWsdlBaseUrl(string $value)
 * @method TIG_PostNL_Model_Core_Cif_Abstract setTestWsdlBaseUrl(string $value)
 *
 * @method boolean hasSoapClient()
 * @method boolean hasHelper()
 * @method boolean hasStoreId()
 * @method boolean hasTestMode()
 * @method boolean hasPassword()
 * @method boolean hasUsername()
 * @method boolean hasWsdlBaseUrl()
 * @method boolean hasTestWsdlBaseUrl()
 *
 * @method TIG_PostNL_Model_Core_Cif_Abstract unsTestMode()
 */
abstract class TIG_PostNL_Model_Core_Cif_Abstract extends Varien_Object
{
    /**
     * Base URL of wsdl files
     */
    const WSDL_BASE_URL_XPATH = 'postnl/cif/wsdl_base_url';

    /**
     * Base URL of sandbox wsdl files
     */
    const TEST_WSDL_BASE_URL_XPATH = 'postnl/cif/test_wsdl_base_url';

    /**
     * Available wsdl filenames.
     */
    const WSDL_BARCODE_NAME        = 'BarcodeWebService';
    const WSDL_CONFIRMING_NAME     = 'ConfirmingWebService';
    const WSDL_LABELLING_NAME      = 'LabellingWebService';
    const WSDL_SHIPPINGSTATUS_NAME = 'ShippingStatusWebService';
    const WSDL_CHECKOUT_NAME       = 'WebshopCheckoutWebService';
    const WSDL_DELIVERYDATE_NAME   = 'DeliveryDateWebService';
    const WSDL_TIMEFRAME_NAME      = 'TimeframeWebService';
    const WSDL_LOCATION_NAME       = 'LocationWebService';

    /**
     * Header security namespace. Used for constructing the SOAP headers array.
     */
    const HEADER_SECURITY_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * CIF error namespace.
     *
     * N.B. Changed in v1.5.0.
     */
    const CIF_ERROR_NAMESPACE = 'http://postnl.nl/cif/services/common/';

    /**
     * XML paths for config options
     */
    const XPATH_LIVE_USERNAME              = 'postnl/cif/live_username';
    const XPATH_LIVE_PASSWORD              = 'postnl/cif/live_password';
    const XPATH_TEST_USERNAME              = 'postnl/cif/test_username';
    const XPATH_TEST_PASSWORD              = 'postnl/cif/test_password';
    const XPATH_CIF_VERSION_BARCODE        = 'postnl/advanced/cif_version_barcode';
    const XPATH_CIF_VERSION_LABELLING      = 'postnl/advanced/cif_version_labelling';
    const XPATH_CIF_VERSION_CONFIRMING     = 'postnl/advanced/cif_version_confirming';
    const XPATH_CIF_VERSION_SHIPPINGSTATUS = 'postnl/advanced/cif_version_shippingstatus';
    const XPATH_CIF_VERSION_CHECKOUT       = 'postnl/advanced/cif_version_checkout';
    const XPATH_CIF_VERSION_DELIVERYDATE   = 'postnl/advanced/cif_version_deliverydate';
    const XPATH_CIF_VERSION_TIMEFRAME      = 'postnl/advanced/cif_version_timeframe';
    const XPATH_CIF_VERSION_LOCATION       = 'postnl/advanced/cif_version_location';

    /**
     * The error number CIF uses for the 'shipment not found' error.
     */
    const SHIPMENT_NOT_FOUND_ERROR_NUMBER = 13;

    /**
     * @var array
     */
    protected $_helpers = array();

    /**
     * @var array
     */
    protected $_dates = array();

    /**
     * Check if the required PHP extensions are installed.
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _construct()
    {
        if (!extension_loaded('soap')) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The SOAP extension is not installed. PostNL requires the SOAP extension to '
                    . 'communicate with PostNL.'
                ),
                'POSTNL-0134'
            );
        }

        if (!extension_loaded('openssl')) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The OpenSSL extension is not installed. The PostNL extension requires the '
                    . 'OpenSSL extension to secure the communications with the PostNL servers.'
                ),
                'POSTNL-0135'
            );
        }

        parent::_construct();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasStoreId()) {
            return $this->_getData('store_id');
        }

        $storeId = Mage::app()->getStore()->getId();

        $this->setStoreId($storeId);
        return $storeId;
    }

    /**
     * @return string
     */
    public function getWsdlBaseUrl()
    {
        if ($this->hasWsdlBaseUrl()) {
            return $this->_getData('wsdl_base_url');
        }

        $wsdlBaseUrl = Mage::getStoreConfig(self::WSDL_BASE_URL_XPATH, $this->getStoreId());

        $this->setWsdlBaseUrl($wsdlBaseUrl);
        return $wsdlBaseUrl;
    }

    /**
     * @return string
     */
    public function getTestWsdlBaseUrl()
    {
        if ($this->hasTestWsdlBaseUrl()) {
            return $this->_getData('test_wsdl_base_url');
        }

        $wsdlBaseUrl = Mage::getStoreConfig(self::TEST_WSDL_BASE_URL_XPATH, $this->getStoreId());

        $this->setTestWsdlBaseUrl($wsdlBaseUrl);
        return $wsdlBaseUrl;
    }

    /**
     * @return TIG_PostNL_Helper_Cif
     */
    public function getHelper()
    {
        if ($this->hasHelper()) {
            return $this->getData('helper');
        }

        $helper = Mage::helper('postnl/cif');

        $this->setHelper($helper);
        return $helper;
    }

    /**
     * Gets the username from system/config. Test mode determines if live or test username is used.
     *
     * @param boolean|int $storeId
     *
     * @return string
     */
    public function getUsername($storeId = false)
    {
        if ($this->hasUsername()) {
            return $this->_getData('username');
        }

        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }

        if ($this->isTestMode()) {
            $username = Mage::getStoreConfig(self::XPATH_TEST_USERNAME, $storeId);
        } else {
            $username = Mage::getStoreConfig(self::XPATH_LIVE_USERNAME, $storeId);
        }

        if (!$username) {
            return false;
        }

        return trim($username);
    }

    /**
     * Gets the password from system/config. Test mode determines if live or test password is used.
     * Passwords will be decrypted using Magento's encryption key and then hashed using sha1
     *
     * @param boolean|int $storeId
     *
     * @return string|boolean
     */
    public function getPassword($storeId = false)
    {
        if ($this->hasPassword()) {
            return $this->_getData('password');
        }

        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }

        if ($this->isTestMode()) {
            $configPassword = Mage::getStoreConfig(self::XPATH_TEST_PASSWORD, $storeId);
        } else {
            $configPassword = Mage::getStoreConfig(self::XPATH_LIVE_PASSWORD, $storeId);
        }

        if (!$configPassword) {
            return false;
        }

        $configPassword = trim($configPassword);
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');
        $decryptedPassword = $coreHelper->decrypt($configPassword);
        $password = sha1($decryptedPassword);

        return $password;
    }

    /**
     * Check if the module is set to test mode
     *
     * @param boolean|int $storeId
     *
     * @return boolean
     *
     * @see TIG_PostNL_Helper_Data::isTestMode()
     */
    public function isTestMode($storeId = false)
    {
        if ($this->hasTestMode()) {
            $testMode = $this->getTestMode();
            return $testMode;
        }

        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }

        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $testMode = $this->getHelper()->isTestMode($storeId);

        return $testMode;
    }

    /**
     * Gets a SoapClient instance for the specified wsdl type.
     *
     * @param string|null $wsdlType
     *
     * @return SoapClient
     */
    public function getSoapClient($wsdlType = null)
    {
        if ($this->hasSoapClient()) {
            return $this->_getData('soap_client');
        }

        $wsdlFile = $this->_getWsdl($wsdlType);

        /**
         * Array of soap options used when connecting to CIF
         */
        $soapOptions = array(
            'soap_version' => SOAP_1_1,
            'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            'trace'        => true
        );

        /**
         * try to create a new SoapClient instance based on the supplied wsdl. if it fails, try again without
         * using the wsdl cache.
         */
        try {
            $client  = new SoapClient(
                $wsdlFile,
                $soapOptions
            );
        } catch (Exception $e) {
            /**
             * Disable wsdl cache and try again
             */
            $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;

            $client  = new SoapClient(
                $wsdlFile,
                $soapOptions
            );
        }

        $this->setSoapClient($client);
        return $client;
    }

    /**
     * Calls a CIF method.
     *
     * @param string $wsdlType   Which wsdl to use
     * @param string $method     The method that will be called
     * @param array  $soapParams An array of parameters to be sent
     *
     * @return object|boolean
     *
     * @throws TIG_PostNL_Exception
     */
    public function call($wsdlType, $method, $soapParams = array())
    {
        $client = null;
        try {
            /**
             * Strip non-printable characters from the SOAP parameters.
             */
            $cifHelper = Mage::helper('postnl/cif');
            array_walk_recursive($soapParams, array($cifHelper, 'stripNonPrintableCharacters'));

            /**
             * @var SoapClient $client
             */
            $client = $this->getSoapClient($wsdlType);

            /**
             * Check if the requested SOAP method is callable.
             */
            if (!is_callable(array($client, $method))) {
                throw new TIG_PostNL_Exception(
                    $cifHelper->__('The specified method "%s" is not callable.', $method),
                    'POSTNL-0136'
                );
            }

            /**
             * Add SOAP header.
             */
            $header = $this->_getSoapHeader();
            $client->__setSoapHeaders($header);

            /**
             * Call the SOAP method.
             */
            $response = $client->$method($soapParams);

            /**
             * Process any warnings that may have occurred.
             */
            $this->_processWarnings($client);

            $this->getHelper()->logCifCall($client);
            return $response;
        } catch(SoapFault $e) {
            /**
             * Only Soap exceptions are caught. Other exceptions must be caught by the caller.
             *
             * @throws TIG_PostNL_Exception
             */
            $this->_handleCifException($e, $client);
        }

        return false;
    }

    /**
     * Returns the URL of the chosen wsdl file based on a wsdl type.
     *
     * Available types are:
     * - barcode
     * - confirming
     * - labelling
     * - shippingstatus
     * - checkout
     * - deliverydate
     * - timeframe
     * - location
     *
     * @param string $wsdlType
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getWsdl($wsdlType)
    {
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;

        /**
         * Check which wsdl file we need for each wsdl type and get the configured WSDl version to use.
         */
        $wsdlType = strtolower($wsdlType);
        switch ($wsdlType) {
            case 'barcode':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_BARCODE, $adminStoreId);
                $wsdlFileName = self::WSDL_BARCODE_NAME;
                break;
            case 'confirming':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_CONFIRMING, $adminStoreId);
                $wsdlFileName = self::WSDL_CONFIRMING_NAME;
                break;
            case 'labelling':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_LABELLING, $adminStoreId);
                $wsdlFileName = self::WSDL_LABELLING_NAME;
                break;
            case 'shippingstatus':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_SHIPPINGSTATUS, $adminStoreId);
                $wsdlFileName = self::WSDL_SHIPPINGSTATUS_NAME;
                break;
            case 'checkout':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_CHECKOUT, $adminStoreId);
                $wsdlFileName = self::WSDL_CHECKOUT_NAME;
                break;
            case 'deliverydate':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_DELIVERYDATE, $adminStoreId);
                $wsdlFileName = self::WSDL_DELIVERYDATE_NAME;
                break;
            case 'timeframe':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_TIMEFRAME, $adminStoreId);
                $wsdlFileName = self::WSDL_TIMEFRAME_NAME;
                break;
            case 'location':
                $wsdlversion  = Mage::getStoreConfig(self::XPATH_CIF_VERSION_LOCATION, $adminStoreId);
                $wsdlFileName = self::WSDL_LOCATION_NAME;
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Chosen wsdl type is not supported: %s', $wsdlType),
                    'POSTNL-0053'
                );
        }

        /**
         * Check if we need the live or the sandbox wsdl.
         */
        if ($this->isTestMode()) {
            $wsdlUrl = $this->getTestWsdlBaseUrl();
        } else {
            $wsdlUrl = $this->getWsdlBaseUrl();
        }

        /**
         * Format the final wsdl URL.
         */
        $wsdlUrl .= $wsdlFileName
                  . '/'
                  . $wsdlversion
                  . '/?wsdl';

        return $wsdlUrl;
    }

    /**
     * Builds soap headers array for CIF authentication.
     *
     * @return SOAPHeader
     */
    protected function _getSoapHeader()
    {
        $username = $this->getUserName();
        $password = $this->getPassWord();

        $namespace = self::HEADER_SECURITY_NAMESPACE;
        $node1     = new SoapVar($username, XSD_STRING, null, null, 'Username', $namespace);
        $node2     = new SoapVar($password, XSD_STRING, null, null, 'Password', $namespace);

        $token     = new SoapVar(array($node1, $node2), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $namespace);

        $security  = new SoapVar(array($token), SOAP_ENC_OBJECT, null, null, 'Security', $namespace);

        $header    = new SOAPHeader($namespace, 'Security', $security, false);

        return $header;
    }

    /**
     * Check if warnings occurred while processing the CIF request. If so, parse and register them
     *
     * @param SoapClient $client
     *
     * @return $this
     */
    protected function _processWarnings(SoapClient $client)
    {
        $responseXML = $client->__getLastResponse();
        $responseDOMDoc = new DOMDocument();
        $responseDOMDoc->loadXML($responseXML);

        /**
         * Search the CIF response for warnings.
         */
        $warnings = $responseDOMDoc->getElementsByTagName('Warning');

        if (!$warnings || $warnings->length < 1) {
            return $this;
        }

        /**
         * Add all warning codes and descriptions to an array.
         *
         * @var DOMDocument $warning
         */
        $n = 0;
        $responseWarnings = array();
        foreach ($warnings as $warning) {
            if ($this->hasEpsCombiLabelWarning($warning)) {
                continue;
            }

            foreach ($warning->getElementsByTagName('Code') as $code) {
                $responseWarnings[$n]['code'] = $code->nodeValue;
            }
            foreach ($warning->getElementsByTagName('Description') as $description) {
                $responseWarnings[$n]['description'] = $description->nodeValue;
            }
            $n++;
        }

        /**
         * Check if old warnings are still present in the registry. If so, merge these with the new warnings.
         */
        if (Mage::registry('postnl_cif_warnings') !== null) {
            $existingWarnings = (array) Mage::registry('postnl_cif_warnings');
            $responseWarnings = array_merge($responseWarnings, $existingWarnings);

            /**
             * Remove the old warnings from the registry
             */
            Mage::unregister('postnl_cif_warnings');
        }

        /**
         * Register the warnings
         */
        Mage::register('postnl_cif_warnings', $responseWarnings);

        return $this;
    }

    /**
     * Check if the supplied warning has an EPS Combi Label warning. We don't want to show it so skip it.
     *
     * @param $warning
     *
     * @return bool
     */
    protected function hasEpsCombiLabelWarning($warning) {
        foreach ($warning->getElementsByTagName('Code') as $code) {
            if ($code->nodeValue == TIG_PostNL_Model_Core_Shipment::EPS_COMBI_LABEL_WARNING_CODE) {
                return true;
            }
        }
    }

    /**
     * Handle a SoapFault thrown by CIF.
     *
     * @param SoapFault  $e
     * @param SoapClient $client
     * @param boolean    $throwException
     *
     * @return $this
     *
     * @throws TIG_PostNL_Model_Core_Cif_Exception
     */
    protected function _handleCifException(SoapFault $e, $client = null, $throwException = true)
    {
        $logException = true;

        /** @var TIG_PostNL_Helper_Cif $cifHelper */
        $cifHelper = Mage::helper('postnl/cif');
        $exception = new TIG_PostNL_Model_Core_Cif_Exception($e->getMessage(), null, $e);

        $requestXML = '';
        $responseXML = '';

        /**
         * Get the request and response XML data
         */
        if ($client) {
            $requestXML  = $cifHelper->formatXml($client->__getLastRequest());
            $responseXML = $cifHelper->formatXml($client->__getLastResponse());
        }

        /**
         * If we got a response, parse it for specific error messages and add these to the exception.
         */
        if (!empty($responseXML)) {
            /**
             * If we received a response, parse it for errors and create an appropriate exception
             */
            $errorResponse = new DOMDocument();
            $errorResponse->loadXML($responseXML);

            /**
             * Get all error messages.
             */
            $errors = $errorResponse->getElementsByTagNameNS(self::CIF_ERROR_NAMESPACE, 'ErrorMsg');
            if ($errors) {
                $message = '';
                foreach($errors as $error) {
                    $message .= $error->nodeValue . PHP_EOL;
                }

                /**
                 * Update the exception.
                 */
                $exception->setMessage($message);
            }

            /**
             * Parse any CIF error numbers we may have received.
             */
            $errorNumbers = $errorResponse->getElementsByTagNameNS(self::CIF_ERROR_NAMESPACE, 'ErrorNumber');
            if ($errorNumbers) {
                foreach ($errorNumbers as $errorNumber) {
                    /**
                     * Error number 13 means that the shipment was not found by PostNL. This error is very common and
                     * can be completely valid. To prevent the log files from filling up extremely quickly, we do not
                     * log this error.
                     */
                    $value = $errorNumber->nodeValue;
                    if ($value == self::SHIPMENT_NOT_FOUND_ERROR_NUMBER) {
                        $logException = false;
                    }

                    $exception->addErrorNumber($value);
                }
            }
        }

        /**
         * Add the response and request data to the exception (to be logged later)
         */
        if (!empty($requestXML) || !empty($responseXML)) {
            $exception->setRequestXml($requestXML)
                      ->setResponseXml($responseXML);
        }

        if ($logException) {
            /**
             * Log the exception and throw it.
             */
            $cifHelper->logCifException($exception);
        }

        if ($throwException) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $helper
     *
     * @return TIG_PostNL_Helper_Data|TIG_PostNL_Helper_DeliveryOptions
     */
    protected function _getHelper($helper = '')
    {
        if ($helper == '') {
            $helper = 'postnl';
        } else {
            $helper = 'postnl/' . $helper;
        }

        if (!array_key_exists($helper, $this->_helpers)) {
            $this->_helpers[$helper] = Mage::helper($helper);
        }

        return $this->_helpers[$helper];
    }
}
