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
abstract class TIG_PostNL_Model_ExtensionControl_Webservices_Abstract extends Varien_Object
{
    /**
     * Wsdl location
     */
    const WEBSERVICE_WSDL_URL_XPATH = 'postnl/general/webservice_wsdl_url';

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

        if (!extension_loaded('mcrypt')) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The MCrypt extension is not installed. The PostNL extension requires the '
                    . 'MCrypt extension to secure the communications with the PostNL servers.'
                ),
                'POSTNL-0137'
            );
        }

        parent::_construct();
    }

    /**
     * Calls a webservice method
     *
     * @param string $method     The method that will be called
     * @param array  $soapParams An array of parameters to be sent
     *
     * @throws Exception
     * @throws SoapFault
     *
     * @return object
     */
    public function call($method, $soapParams)
    {
        try {
            $wsdl = Mage::getStoreConfig(self::WEBSERVICE_WSDL_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

            /**
             * Array of soap options used when connecting to CIF
             */
            $soapOptions = array(
                'soap_version' => SOAP_1_1,
                'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            );

            /**
             * try to create a new Zend_Soap_Client instance based on the supplied wsdl. If it fails, try again without
             * using the wsdl cache.
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
             * Call the SOAP method.
             */
            $response = $client->$method($soapParams);

            Mage::helper('postnl/webservices')->logWebserviceCall($client);
            return $response;
        } catch(SoapFault $e) {
            /**
             * Only Soap exceptions are caught. Other exceptions must be caught by the caller.
             */
            Mage::helper('postnl/webservices')->logWebserviceException($e);

            throw $e;
        }
    }
}
