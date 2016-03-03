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
class TIG_PostNL_AddressValidationController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var TIG_PostNL_Model_AddressValidation_Cendris
     */
    protected $_cendrisModel;

    /**
     * @param TIG_PostNL_Model_AddressValidation_Cendris $cendrisModel
     *
     * @return $this
     */
    public function setCendrisModel($cendrisModel)
    {
        $this->_cendrisModel = $cendrisModel;

        return $this;
    }

    /**
     * @return TIG_PostNL_Model_AddressValidation_Cendris
     */
    public function getCendrisModel()
    {
        $cendrisModel = $this->_cendrisModel;

        if ($cendrisModel) {
            return $cendrisModel;
        }

        /** @var TIG_PostNL_Model_AddressValidation_Cendris $cendris */
        $cendris = Mage::getModel('postnl_addressvalidation/cendris');
        $this->setCendrisModel($cendris);

        return $cendris;
    }

    /**
     * Validates and enriches a postcode/house number combination. This will result in the address's city and street
     * name if valid.
     *
     * @return $this
     */
    public function postcodeCheckAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('missing_data');

            return $this;
        }

        /**
         * Get the address data from the $_POST superglobal
         */
        $data = $this->getRequest()->getPost();
        if (!$data
            || !isset($data['postcode'])
            || !isset($data['housenumber'])
        ) {
            $this->getResponse()
                 ->setBody('missing_data');

            return $this;
        }

        $postcode    = $data['postcode'];
        $housenumber = $data['housenumber'];

        /**
         * Remove spaces from house number and postcode fields.
         */
        $postcode    = str_replace(' ', '', $postcode);
        $postcode    = strtoupper($postcode);
        $housenumber = trim($housenumber);

        /**
         * Validate the parameters.
         */
        if (!$this->validatePostcode($postcode, $housenumber)) {
            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        /**
         * Load the Cendris webservice and perform an getAdresxpressPostcode request
         */
        $cendris = $this->getCendrisModel();
        try {
            $result = $cendris->getAdresxpressPostcode($postcode, $housenumber);
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        if (!$this->validateResult($result)) {
            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        /**
         * Get the city and street name from the response
         */
        $city       = $result->woonplaats;
        $streetname = $result->straatnaam;

        /**
         * Add the resulting city and street name to an array and JSON encode it
         */
        $responseArray = array(
            'city'       => $city,
            'streetname' => $streetname,
        );

        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');
        $response = $coreHelper->jsonEncode($responseArray);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json', true)
             ->setBody($response);

        return $this;
    }

    /**
     * Validates a postcode and house number.
     *
     * @param string $postcode
     * @param int    $housenumber
     *
     * @return boolean
     */
    public function validatePostcode($postcode, $housenumber)
    {
        /**
         * Get validation classes for the postcode and house number values
         */
        $postcodeValidator    = new Zend_Validate_PostCode('nl_NL');
        $housenumberValidator = new Zend_Validate_Digits();

        /**
         * Make sure the input is valid
         */
        if (!$postcodeValidator->isValid($postcode)
            || !$housenumberValidator->isValid($housenumber)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validate the postcode check result.
     *
     * @param StdClass $result
     *
     * @return bool
     */
    public function validateResult($result)
    {
        /**
         * Make sure the required data is present.
         * If not, it means the supplied house number and postcode combination could not be found.
         */
        if (!isset($result->woonplaats)
            || !$result->woonplaats
            || !isset($result->straatnaam)
            || !$result->straatnaam
        ) {
            return false;
        }

        return true;
    }
}
