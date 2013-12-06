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
class TIG_PostNL_Model_ExtensionControl_Webservices_Abstract extends Varien_Object
{
    /**
     * Wsdl location
     */
    const WEBSERVICE_WSDL_URL = 'http://api.tigpostnl.nl/soap?wsdl';

    /**
     * Calls a webservice method
     * 
     * @param string
     * @param string $method The method that will be called
     * @param array $soapParams An array of parameters to be sent
     * 
     * @return object
     * 
     * @throws TIG_PostNL_Exception
     */
    public function call($method, $soapParams)
    {
        try {
            $wsdl = self::WEBSERVICE_WSDL_URL;
            
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
                    $wsdl, 
                    $soapOptions
                );
            } catch (Exception $e) {
                /**
                 * Disable wsdl cache and try again
                 */
                $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;
                
                $client  = new Zend_Soap_Client(
                    $wsdl, 
                    $soapOptions
                );
            }
            
            /**
             * Call the SOAP method
             */
            $response = $client->__call(
                $method,
                array(
                    $method => $soapParams,
                )
            );
            
            Mage::helper('postnl/webservices')->logWebserviceCall($client);
            return $response;
        } catch(SoapFault $e) {
            /**
             * Only Soap exceptions are caught. Other exceptions must be caught by the caller
             */
            Mage::helper('postnl/webservices')->logWebserviceException($e);
            
            throw $e;
        }
    }
}
