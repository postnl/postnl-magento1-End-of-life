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
advanced * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_AddressValidation_Cendris_Abstract extends Varien_Object
{
    const XPATH_POSTCODE_BASE_URL = 'postnl/cif/postcode_base_url';
    const XPATH_POSTCODE_TEST_BASE_URL = 'postnl/cif/test_postcode_base_url';
    const XPATH_POSTCODE_BASE_URL_VERSION = 'postnl/advanced/cif_version_postcode';
    const ENDPOINT = 'postalcodecheck';

    protected $client;
    protected $cifModel;

    /**
     * Calls a webservice method
     *
     * @param $restParams
     * @return object
     * @throws TIG_PostNL_Exception
     */
    public function call($restParams)
    {
        /** @var TIG_PostNL_Helper_Cif $cifHelper */
        $cifHelper = Mage::helper('postnl/cif');

        /** @var TIG_PostNL_Model_Core_Cif_Abstract $cif */
        $this->cifModel = Mage::getModel('postnl_core/cif');


        $this->setUri();
        $this->setHeaders();
        $this->setParameters($restParams);

        try {
            $response = $this->client->request();
            if ($response->getStatus() != 200) {
                throw new TIG_PostNL_Exception(
                    $response->getBody(),
                    'POSTNL-0247');
            }

            $response = $this->convertResponse($response->getBody());
        } catch (\Zend_Http_Client_Exception $exception) {
            $cifHelper->logCifException($exception);

            return false;
        } catch (TIG_PostNL_Exception $exception) {
            $cifHelper->logCifException($exception);

            return false;
        }

        return (object)array(
            'woonplaats' => $response->city,
            'straatnaam' => $response->streetName
        );
    }

    /**
     *
     */
    protected function setUri()
    {
        $xpath = self::XPATH_POSTCODE_BASE_URL;
        if ($this->cifModel->isTestMode()) {
            $xpath = self::XPATH_POSTCODE_TEST_BASE_URL;
        }

        $url = Mage::getStoreConfig($xpath);
        $version = 'v' . Mage::getStoreConfigFlag(self::XPATH_POSTCODE_BASE_URL_VERSION) . '/';
        $uri = $url . $version . self::ENDPOINT;
        $this->client = new Zend_Http_Client($uri);
    }

    /**
     * @param $response
     * @return object
     * @throws TIG_PostNL_Exception
     */
    public function convertResponse($response)
    {
        if(!isset($response[0])) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Error received getting postcode data from PostNL.'),
                'POSTNL-0247'
            );
        };

        $data = json_decode($response)[0];

        return $data;
    }

    /**
     * Includes the API key into the headers.
     */
    private function setHeaders()
    {
        $apikey = $this->cifModel->getApiKey();

        $this->client->setHeaders(
            array(
                'apikey' => $apikey
            )
        );
    }

    private function setParameters($restParams)
    {
        $this->client->setRawData(json_encode($restParams), 'application/json');
        $this->client->setMethod(Zend_Http_Client::POST);
    }
}
