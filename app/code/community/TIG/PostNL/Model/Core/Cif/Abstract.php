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
advanced * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
 
 /**
  * Base CIF model. Contains general code for communicating with the CIF API
  */
class TIG_PostNL_Model_Core_Cif_Abstract extends Varien_Object
{
    /**
     * Base URL of wsdl files
     */
    const WSDL_BASE_URL = 'https://service.postnl.com/CIF/';
    
    /**
     * Base URL of sandbox wsdl files
     */
    const TEST_WSDL_BASE_URL = 'https://testservice.postnl.com/CIF_SB/';
    
    /**
     * available wsdl filenames
     */
    const WSDL_BARCODE_NAME         = 'BarcodeWebService';
    const WSDL_CONFIRMING_NAME      = 'ConfirmingWebService';
    const WSDL_LABELLING_NAME       = 'LabellingWebService';
    const WSDL_SHIPPING_STATUS_NAME = 'ShippingStatusWebService';
    const WSDL_CHECKOUT_NAME        = 'WebshopCheckoutWebService';
    
    /**
     * header security namespace. Used for constructing the SOAP headers array
     */
    const HEADER_SECURITY_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    
    /**
     * CIF error namespace.
     */
    const CIF_ERROR_NAMESPACE = 'http://schemas.datacontract.org/2004/07/Tpp.Cif.Services.Services.Exception';
    
    /**
     * XML paths for config options
     */
    const XML_PATH_LIVE_USERNAME              = 'postnl/cif/live_username';
    const XML_PATH_LIVE_PASSWORD              = 'postnl/cif/live_password';
    const XML_PATH_TEST_USERNAME              = 'postnl/cif/test_username';
    const XML_PATH_TEST_PASSWORD              = 'postnl/cif/test_password';
    const XML_PATH_CIF_VERSION_BARCODE        = 'postnl/advanced/cif_version_barcode';
    const XML_PATH_CIF_VERSION_LABELLING      = 'postnl/advanced/cif_version_labelling';
    const XML_PATH_CIF_VERSION_CONFIRMING     = 'postnl/advanced/cif_version_confirming';
    const XML_PATH_CIF_VERSION_SHIPPINGSTATUS = 'postnl/advanced/cif_version_shippingstatus';
    const XML_PATH_CIF_VERSION_CHECKOUT       = 'postnl/advanced/cif_version_checkout';
    
    /**
     * Gets the username from system/config. Test mode determines if live or test username is used.
     * 
     * @param boolean|int $storeId
     * 
     * @return string
     */
    protected function _getUsername($storeId = false)
    {
        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }
        
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        if ($this->isTestMode()) {
            $username = Mage::getStoreConfig(self::XML_PATH_TEST_USERNAME, $storeId);
            return $username;
        }
        
        $username = Mage::getStoreConfig(self::XML_PATH_LIVE_USERNAME, $storeId);
        return trim($username);
    }
    
    /**
     * Gets the password from system/config. Test mode determines if live or test password is used.
     * Passwords will be decrypted using Magento's encryption key and then hashed using sha1
     * 
     * @param boolean|int $storeId
     * 
     * @return string
     */
    protected function _getPassword($storeId = false)
    {
        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }
        
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        if ($this->isTestMode()) {
            $password = Mage::getStoreConfig(self::XML_PATH_TEST_PASSWORD, $storeId);
            
            $password = trim($password);
            $password = sha1(Mage::helper('core')->decrypt($password));
            
            return $password;
        }
        
        $password = Mage::getStoreConfig(self::XML_PATH_LIVE_PASSWORD, $storeId);
        
        $password = trim($password);
        $password = sha1(Mage::helper('core')->decrypt($password));
        
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
        
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $testMode = Mage::helper('postnl')->isTestMode($storeId);
        
        return $testMode;
    }

    /**
     * Calls a CIF method
     * 
     * @param string $wsdlType Which wsdl to use
     * @param string $method The method that will be called
     * @param array $soapParams An array of parameters to be sent
     * @param boolean|string $username
     * @param boolean|string $password
     * 
     * @return object
     * 
     * @throws TIG_PostNL_Exception
     */
    public function call($wsdlType, $method, $soapParams = null, $username = false, $password = false)
    {
        try {
            if ($username !== false && $password !== false
                && (!$this->_getUserName() || !$this->_getPassword())
            ) {
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('No username or password set.'),
                    'POSTNL-0052'
                );
            }
            
            $wsdlFile = $this->_getWsdl($wsdlType);
            
            /**
             * Array of soap options used when connecting to CIF
             */
            $soapOptions = array(
                'soap_version' => SOAP_1_1,
                'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            );
            
            /**
             * try to create a new Zend_Soap_Client instance based on the supplied wsdl. if it fails, try again without using the
             * wsdl cache.
             */
            try {
                $client  = new Zend_Soap_Client(
                    $wsdlFile, 
                    $soapOptions
                );
            } catch (Exception $e) {
                /**
                 * Disable wsdl cache and try again
                 */
                $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;
                
                $client  = new Zend_Soap_Client(
                    $wsdlFile, 
                    $soapOptions
                );
            }
            
            /**
             * Add SOAP header
             */
            $header = $this->_getSoapHeader($username, $password);
            $client->addSoapInputHeader($header, true); //permanent header
            
            /**
             * Call the SOAP method
             */
            $response = $client->__call(
                $method,
                array(
                    $method => $soapParams,
                )
            );
            
            /**
             * Parse any warnings that may have occurred
             */
            $this->_processWarnings($client);
            
            Mage::helper('postnl/cif')->logCifCall($client);
            return $response;
        } catch(SoapFault $e) {
            /**
             * Only Soap exceptions are caught. Other exceptions must be caught by the caller
             */
            $this->_handleCifException($e, $client);
        }
    }

    /**
     * returns the URL of the chosen wsdl file based on a wsdl type.
     * 
     * available types are:
     * - barcode
     * - confirming
     * - labelling
     * - shippingstatus
     * - checkout
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
         * Check which wsdl file we need for each wsdl type
         * Als get the wsdl version to get
         */
        $wsdlType = strtolower($wsdlType);
        switch ($wsdlType) {
            case 'barcode':
                $wsdlversion  = Mage::getStoreConfig(self::XML_PATH_CIF_VERSION_BARCODE, $adminStoreId);
                $wsdlFileName = self::WSDL_BARCODE_NAME;
                break;
            case 'confirming':
                $wsdlversion  = Mage::getStoreConfig(self::XML_PATH_CIF_VERSION_CONFIRMING, $adminStoreId);
                $wsdlFileName = self::WSDL_CONFIRMING_NAME;
                break;
            case 'labelling':
                $wsdlversion  = Mage::getStoreConfig(self::XML_PATH_CIF_VERSION_LABELLING, $adminStoreId);
                $wsdlFileName = self::WSDL_LABELLING_NAME;
                break;
            case 'shippingstatus':
                $wsdlversion  = Mage::getStoreConfig(self::XML_PATH_CIF_VERSION_SHIPPINGSTATUS, $adminStoreId);
                $wsdlFileName = self::WSDL_SHIPPING_STATUS_NAME;
                break;
            case 'checkout':
                $wsdlversion  = Mage::getStoreConfig(self::XML_PATH_CIF_VERSION_CHECKOUT, $adminStoreId);
                $wsdlFileName = self::WSDL_CHECKOUT_NAME;
                break;
            default:
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Chosen wsdl type is not supported: %s', $wsdlType),
                    'POSTNL-0053'
                );
        }
        
        /**
         * Wsdl version numbers are formatted using an underscore instead of a period. Since many people would use a period, we
         * convert it to CIF specifications.
         */
        $wsdlversion = str_replace('.', '_', $wsdlversion);
        
        /**
         * Check if we need the live or the sandbox wsdl
         */
        if ($this->isTestMode()) {
            $wsdlUrl = self::TEST_WSDL_BASE_URL;
        } else {
            $wsdlUrl = self::WSDL_BASE_URL;
        }
        
        /**
         * Format the final wsdl URL
         */
        $wsdlUrl .= $wsdlFileName
                   . '/'
                   . $wsdlversion
                   . '/?wsdl';
        
        return $wsdlUrl;
    }
    
    /**
     * Builds soap headers array for CIF authentication
     * 
     * @param boolean|string $username
     * @param boolean|string $password
     * 
     * @return array
     */
    protected function _getSoapHeader($username = false, $password = false)
    {
        if ($username === false || $password === false) {
            $username = $this->_getUserName();
            $password = $this->_getPassWord();
        }
        
        $namespace = self::HEADER_SECURITY_NAMESPACE;
        $node1     = new SoapVar($username, XSD_STRING,      null, null, 'Username',      $namespace);
        $node2     = new SoapVar($password, XSD_STRING,      null, null, 'Password',      $namespace);
        $token     = new SoapVar(array($node1, $node2), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $namespace);
        $security  = new SoapVar(array($token),         SOAP_ENC_OBJECT, null, null, 'Security',      $namespace);
        $header   = new SOAPHeader($namespace, 'Security', $security, false);
        
        return $header;
    }
    
    /**
     * Check if warnings occurred while processing the CIF request. If so, parse and register them
     * 
     * @param SoapClient $client
     * 
     * @return TIG_PostNL_Model_Core_Cif_Abstract
     */
    protected function _processWarnings($client)
    {
        $responseXML = $client->getLastResponse();
        $responseDOMDoc = new DOMDocument();
        $responseDOMDoc->loadXML($responseXML);
        
        /**
         * Search the CIF response for warnings
         */
        $warnings = $responseDOMDoc->getElementsByTagName('Warning');
        
        if (!$warnings || $warnings->length < 1) {
            return $this;
        }
        
        /**
         * add all warning codes and descriptions to an array
         */
        $n = 0;
        $responseWarnings = array();
        foreach ($warnings as $warning) {
            foreach ($warning->getElementsByTagName('Code') as $code) {
                $responseWarnings[$n]['code'] = $code->nodeValue;
            }
            foreach ($warning->getElementsByTagName('Description') as $description) {
                $responseWarnings[$n]['description'] = $description->nodeValue;
            }
            $n++;
        }
        
        /**
         * Check if old warnings are still present in the registry. If so, merge these with the new warnings
         */
        if (Mage::registry('postnl_cif_warnings') !== null) {
            $existingWarnings = (array) Mage::registry('postnl_cif_warnings');
            $responseWarnings = array_merge($responseWarnings, $existingWarnings);
            
            /**
             * Remove the old warnings from the registry
             */
            Mage::unRegister('postnl_cif_warnings');
        }
        
        /**
         * Register the warnings
         */
        Mage::register('postnl_cif_warnings', $responseWarnings);
        
        return $this;
    }
    
    /**
     * Handle a SoapFault caused by CIF
     * 
     * @param SoapFault $e
     * @param SoapClient $client
     * 
     * @throws TIG_PostNL_Model_Core_Cif_Exception
     */
    protected function _handleCifException($e, $client = null)
    {
        $cifHelper = Mage::helper('postnl/cif');
        
        /**
         * Get the request and response XML data
         */
        if ($client) {
            $requestXML  = $cifHelper->formatXml($client->getLastRequest());
            $responseXML = $cifHelper->formatXml($client->getLastResponse());
        }
        
        if ($responseXML) {
            /**
             * If we received a response, parse it for errors and create an appropriate exception
             */
            $errorResponse = new DOMDocument();
            $errorResponse->loadXML($responseXML);
            $errors = $errorResponse->getElementsByTagNameNS(self::CIF_ERROR_NAMESPACE, 'ErrorMsg');
            if ($errors) {
                $message = '';
                foreach($errors as $error) {
                    $message .= $error->nodeValue . PHP_EOL;
                }
                
                $exception = new TIG_PostNL_Model_Core_Cif_Exception($message, null, $e);
            }
                
            $errorNumbers = $errorResponse->getElementsByTagNameNS(self::CIF_ERROR_NAMESPACE, 'ErrorNumber');
            if ($exception && $errorNumbers) {
                foreach ($errorNumbers as $errorNumber) {
                    $exception->addErrorNumber($errorNumber->nodeValue);
                }
            }
        } else {
            /**
             * Create a general exception
             */
             $exception = new TIG_PostNL_Model_Core_Cif_Exception($e->getMessage(), null, $e);
        }
        
        /**
         * Add the response and request data to the exception (to be logged later)
         */
        if ($client) {
            $exception->setRequestXml($requestXML)
                      ->setResponseXml($responseXML);
        }
        
        /**
         * Log the exception and throw it
         */      
        $cifHelper->logCifException($exception);
        
        throw $exception;
    }
}
