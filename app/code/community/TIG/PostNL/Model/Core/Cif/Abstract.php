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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
 
 /**
  * Base CIF model. Contains general code for communicating with the CIF API
  */
class TIG_PostNL_Model_Core_Cif_Abstract extends Varien_Object
{
    /**
     * directory containing wsdl files
     * 
     * @var string
     */
    const WSDL_DIRECTORY_NAME = 'wsdl';
    
    /**
     * Subdirectory containing test mode wsdl files
     * 
     * @var string
     */
    const TEST_WSDL_DIRECTORY_NAME = 'test';
    
    /**
     * available wsdl filenames
     * 
     * @var string
     */
    const WSDL_BARCODE_NAME         = 'BarcodeWebService_1.wsdl';
    const WSDL_CONFIRMING_NAME      = 'ConfirmingWebService_1.wsdl';
    const WSDL_LABELLING_NAME       = 'LabellingWebService_1.wsdl';
    const WSDL_SHIPPING_STATUS_NAME = 'ShippingStatusWebService_1.wsdl';
    
    /**
     * header security namespace. Used for constructing the SOAP headers array
     * 
     * @var string
     */
    const HEADER_SECURITY_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    
    /**
     * CIF error namespace.
     * 
     * @var string
     */
    const CIF_ERROR_NAMESPACE = 'http://schemas.datacontract.org/2004/07/Tpp.Cif.Services.Services.Exception';
    
    /**
     * XML paths for config options
     * 
     * @var string
     */
    const XML_PATH_LIVE_USERNAME = 'postnl/cif/live_username';
    const XML_PATH_LIVE_PASSWORD = 'postnl/cif/live_password';
    const XML_PATH_TEST_USERNAME = 'postnl/cif/test_username';
    const XML_PATH_TEST_PASSWORD = 'postnl/cif/test_password';
    const XML_PATH_TEST_MODE     = 'postnl/cif/mode';
    
    /**
     * Checks if the module is set to testmode
     * 
     * @return boolean
     */
    public function getTestMode()
    {
        if ($this->getData('test_mode')) {
            return $this->getData('test_mode');
        }
        
        $testMode = (bool) Mage::getStoreConfig(self::XML_PATH_TEST_MODE, 0);
        
        $this->setData('test_mode', $testMode);
        return $testMode;
    }
    
    /**
     * Gets the username from system/config. Test mode determines if live or test username is used.
     * 
     * @return string
     */
    protected function _getUsername()
    {
        if ($this->isTestMode()) {
            $username = Mage::getStoreConfig(self::XML_PATH_TEST_USERNAME, 0);
            return $username;
        }
        
        $username = Mage::getStoreConfig(self::XML_PATH_LIVE_USERNAME, 0);
        return $username;
    }
    
    /**
     * Gets the password from system/config. Test mode determines if live or test password is used.
     * Passwords will be decrypted using Magento's encryption key and then re-encrypted using sha1
     * 
     * @return string
     */
    protected function _getPassword()
    {
        if ($this->isTestMode()) {
            $password = Mage::getStoreConfig(self::XML_PATH_TEST_PASSWORD, 0);
            $password = sha1(Mage::helper('core')->decrypt($password));
            return $password;
        }
        
        $password = Mage::getStoreConfig(self::XML_PATH_LIVE_PASSWORD, 0);
        $password = sha1(Mage::helper('core')->decrypt($password));
        return $password;
    }
    
    /**
     * Alias for getTestMode()
     * 
     * @see getTestMode()
     * 
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->getTestMode();
    }

    /**
     * Calls a CIF method
     * 
     * @param string $wsdlType Which wsdl to use
     * @param string $method The method that will be called
     * @param array $soapParams An array of parameters to be sent
     * 
     * @return object
     * 
     * @throws TIG_PostNL_Exception
     */
    public function call($wsdlType, $method, $soapParams)
    {
        try {
            if (!$this->_getUserName() || !$this->_getPassword()) {
                throw Mage::exception('TIG_PostNL', 'No username or password set.');
            }
            
            $wsdlFile = $this->_getWsdl($wsdlType);
            $client  = new SoapClient(
                $wsdlFile, 
                array(
                    'trace' => 1,
                )
            );
            $headers = $this->_getSoapHeaders();

            $client->__setSoapHeaders($headers);

            $response = $client->__soapCall(
                $method,
                array(
                    $method => $soapParams,
                )
            );
            
            return $response;
        } catch(SoapFault $e) {
            /**
             * Only Soap exceptions are caught. Other exceptions must be caught by the caller
             */
            $this->_handleCifException($e, $client);
        }
    }

    /**
     * returns the path to the chosen wsdl file based on a wsdl type.
     * 
     * available types are:
     * - barcode
     * - confirming
     * - labelling
     * - shipping_status
     * 
     * @param string $wsdlType
     * 
     * @return string
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _getWsdl($wsdlType)
    {
        $wsdlType = strtolower($wsdlType);
        switch ($wsdlType) {
            case 'barcode':
                $wsdlFileName = self::WSDL_BARCODE_NAME;
                break;
            case 'confirming':
                $wsdlFileName = self::WSDL_CONFIRMING_NAME;
                break;
            case 'labelling':
                $wsdlFileName = self::WSDL_LABELLING_NAME;
                break;
            case 'shipping_status':
                $wsdlFileName = self::WSDL_SHIPPING_STATUS_NAME;
                break;
            default:
                throw Mage::exception('TIG_PostNL', 'Chosen wsdl type is not supported: ' . $wsdlType);
        }
        
        $wsdlPath = Mage::helper('postnl')->getModuleDir(self::WSDL_DIRECTORY_NAME, 'TIG_PostNL');
        
        if ($this->isTestMode()) {
            $wsdlPath .= DS . self::TEST_WSDL_DIRECTORY_NAME;
        }
        
        $wsdlPath .= DS . $wsdlFileName;
        
        
        return $wsdlPath;
    }
    
    /**
     * Builds soap headers array for CIF authentication
     * 
     * @return array
     */
    protected function _getSoapHeaders()
    {
        $headers = array();

        $namespace = self::HEADER_SECURITY_NAMESPACE;
        $node1     = new SoapVar($this->_getUserName(), XSD_STRING,      null, null, 'Username',      $namespace);
        $node2     = new SoapVar($this->_getPassWord(), XSD_STRING,      null, null, 'Password',      $namespace);
        $token     = new SoapVar(array($node1, $node2), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $namespace);
        $security  = new SoapVar(array($token),         SOAP_ENC_OBJECT, null, null, 'Security',      $namespace);
        $headers[] = new SOAPHeader($namespace, 'Security', $security, false);

        return $headers;
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
            $requestXML  = $cifHelper->formatXml($client->__getLastRequest());
            $responseXML = $cifHelper->formatXml($client->__getLastResponse());
        }
        
        if ($responseXML) {
            /**
             * If we recieved a response, parse it for errors and create an appropriate exception
             */
            $errorResponse = new DOMDocument();
            $errorResponse->loadXML($responseXML);
            $errors = $errorResponse->getElementsByTagNameNS(self::CIF_ERROR_NAMESPACE, 'ErrorMsg');
            if ($errors) {
                $message = '';
                foreach($errors as $error) {
                    $message .= $error->nodeValue . '<br/>';
                }
                
                $exception = Mage::exception('TIG_PostNL_Model_Core_Cif', $message);
            }
        } else {
            /**
             * Create a general exception
             */
            $exception = Mage::exception('TIG_PostNL_Model_Core_Cif', $e->getMessage());
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
