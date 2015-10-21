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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_DeliveryOptionsController extends Mage_Core_Controller_Front_Action
{
    /**
     * Regular expressions used to validate latitude and longitude coordinates.
     */
    const LATITUDE_REGEX  = '#^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}#';
    const LONGITUDE_REGEX = '#^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,6}#';

    /**
     * Regular expressions to validate various address fields.
     */
    const CITY_NAME_REGEX   = '#^[a-zA-Z]+(?:(?:\\s+|-)[a-zA-Z]+)*$#';
    const STREET_NAME_REGEX = "#^[a-zA-Z0-9\s,'-.]*$#";
    const HOUSENR_EXT_REGEX = "#^[a-zA-Z0-9\s,'-]*$#";

    /**
     * Regular expression to validate dutch phone number.
     */
    const PHONE_NUMBER_REGEX = '#^(((\+31|0|0031)){1}[1-9]{1}[0-9]{8})$#i';

    /**
     * Regular expression to validate dutch mobile phone number.
     */
    const MOBILE_PHONE_NUMBER_REGEX = '#^(((\+31|0|0031)6){1}[1-9]{1}[0-9]{7})$#i';

    /**
     * Regular expression to match a valid PostNL time.
     */
    const TIME_REGEX = '#^[0-9]{2,2}:[0-9]{2,2}(:[0-9]{2,2})?$#';

    /**
     * @var null|array
     */
    protected $_validTypes = null;

    /**
     * @var null|boolean
     */
    protected $_canUseDeliveryOptions = null;

    /**
     * @var null|TIG_PostNL_Model_DeliveryOptions_Service
     */
    protected $_service = null;

    /**
     * Gets valid option types.
     *
     * @return array
     */
    public function getValidTypes()
    {
        if ($this->hasValidTypes()) {
            return $this->_validTypes;
        }

        $validTypes = Mage::helper('postnl/deliveryOptions')->getValidTypes();

        $this->setValidTypes($validTypes);
        return $validTypes;
    }

    /**
     * @param array $validTypes
     *
     * @return $this
     */
    public function setValidTypes($validTypes)
    {
        $this->_validTypes = $validTypes;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasValidTypes()
    {
        if ($this->_validTypes !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCanUseDeliveryOptions()
    {
        return $this->_canUseDeliveryOptions;
    }

    /**
     * @param boolean $canUse
     *
     * @return $this
     */
    public function setCanUseDeliveryOptions($canUse)
    {
        $this->_canUseDeliveryOptions = $canUse;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCanUseDeliveryOptions()
    {
        if ($this->_canUseDeliveryOptions !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return null|TIG_PostNL_Model_DeliveryOptions_Service
     */
    public function getService()
    {
        $service = $this->_service;
        if ($service === null) {
            $service = Mage::getModel('postnl_deliveryoptions/service');

            $this->setService($service);
        }

        return $service;
    }

    /**
     * @param TIG_PostNL_Model_DeliveryOptions_Service $service
     *
     * @return $this
     */
    public function setService(TIG_PostNL_Model_DeliveryOptions_Service $service)
    {
        $this->_service = $service;

        return $this;
    }

    /**
     * Save Extra costs associated with a selected option.
     *
     * @deprecated v1.6.0
     *
     * @return $this
     */
    public function saveOptionCostsAction()
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $params = $this->getRequest()->getPost();

        try {
            $costs = $this->_getSaveOptionCostsPostData($params);

            $this->getService()->saveOptionCosts($costs);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        if (isset($params['isOsc']) && $params['isOsc'] == true) {
            $this->_updateShippingMethod();
        }

        $this->getResponse()
             ->setBody('OK');

        return $this;
    }

    /**
     * Saves a mobile phonenumber for parceldispenser orders.
     *
     * @return $this
     */
    public function savePhoneNumberAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $params = $this->getRequest()->getPost();

        try {
            $phoneNumber = $this->_getSavePhonePostData($params);

            $this->getService()->saveMobilePhoneNumber($phoneNumber);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        $this->getResponse()
             ->setBody('OK');

        return $this;
    }

    /**
     * Saves the selected shipment option.
     *
     * @return $this
     */
    public function saveSelectedOptionAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $params = $this->getRequest()->getPost();

        try {
            $data = $this->_getSaveSelectionPostData($params);
            $this->getService()->saveDeliveryOption($data);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        if (isset($params['isOsc']) && $params['isOsc'] == true) {
            $this->_updateShippingMethod();
        }

        $this->getResponse()
             ->setBody('OK');

        return $this;
    }

    /**
     * Get possible evening delivery time frames based on an earliest possible delivery date.
     *
     * @return $this
     */
    public function getDeliveryTimeframesAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $params = $this->getRequest()->getPost();

        try {
            $data = $this->_getTimeframePostData($params);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        try {
            $cif = Mage::getModel('postnl_deliveryoptions/cif');
            $response = $cif->setStoreId($storeId)
                            ->getDeliveryTimeframes($data);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * Filter out unavailable time frames.
         */
        $timeframes = $this->getService()->filterTimeframes($response);

        if (!$timeframes) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $timeframes = Mage::helper('core')->jsonEncode($timeframes);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json', true)
             ->setBody($timeframes);

        return $this;
    }

    /**
     * Get the nearest post office locations based on either a postcode or a longitude and latitude.
     *
     * @return $this
     */
    public function getNearestLocationsAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $postData = $this->getRequest()->getPost();

        try {
            $data = $this->_getLocationPostData($postData);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        try {
            $cif = Mage::getModel('postnl_deliveryoptions/cif');
            $response = $cif->setStoreId($storeId)
                            ->getNearestLocations($data);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        if (is_null($response)) {
            $this->getResponse()
                 ->setBody('no_result');

            return $this;
        }

        if (!is_array($response)) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $response = Mage::helper('postnl/deliveryOptions')->markEveningLocations($response, $data['deliveryDate']);

        $locations = Mage::helper('core')->jsonEncode($response);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json', true)
             ->setBody($locations);

        return $this;
    }

    /**
     * Get all locations in a given area.
     *
     * @return $this
     */
    public function getLocationsInAreaAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $postData = $this->getRequest()->getPost();

        try {
            $data = $this->_getLocationInAreaPostData($postData);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        try {
            $cif = Mage::getModel('postnl_deliveryoptions/cif');
            $response = $cif->setStoreId($storeId)
                            ->getLocationsInArea($data);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        if (!is_array($response)) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $response = Mage::helper('postnl/deliveryOptions')->markEveningLocations($response, $data['deliveryDate']);

        $locations = Mage::helper('core')->jsonEncode($response);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($locations);

        return $this;
    }

    /**
     * Get the formatted PakjeGemak address if available.
     *
     * @return $this
     */
    public function getFormattedPakjeGemakAddressAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $pakjeGemakAddress = false;
        /** @var Mage_Sales_Model_Quote_Address $address */
        foreach ($quote->getAllAddresses() as $address) {
            if ($address->getAddressType() == 'pakje_gemak') {
                $pakjeGemakAddress = $address;
                break;
            }
        }

        if (!$pakjeGemakAddress) {
            $this->getResponse()
                 ->setBody('not_found');

            return $this;
        }

        /**
         * Format the address.
         */
        $formattedAddress = $pakjeGemakAddress->format('html');
        $this->getResponse()
             ->setBody($formattedAddress);

        return $this;
    }

    /**
     * Check to see if PostNL delivery options are active and available.
     *
     * @return boolean
     */
    protected function _canUseDeliveryOptions()
    {
        if ($this->hasCanUseDeliveryOptions()) {
            return $this->getCanUseDeliveryOptions();
        }

        $helper = Mage::helper('postnl/deliveryOptions');

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $canUseDeliveryOptions = false;
        if ($helper->canUseDeliveryOptions($quote) && $helper->canUseDeliveryOptionsForCountry($quote)) {
            $canUseDeliveryOptions = true;
        }

        $this->setCanUseDeliveryOptions($canUseDeliveryOptions);
        return $canUseDeliveryOptions;
    }

    /**
     * Validates input for the saveOptionCosts action.
     *
     * @param array $params
     *
     * @return float|int
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getSaveOptionCostsPostData($params)
    {
        /**
         * Costs need to be specified in order to save them.
         */
        if (!isset($params['costs'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     "Invalid arguments supplied. The 'costs' parameter is required."
                ),
                'POSTNL-0142'
            );
        }

        $costs = $params['costs'];
        $costs = Mage::helper('core')->jsonDecode($costs);

        /**
         * The costs object should contain an amount incl. VAT and excl. VAT.
         */
        if (!isset($costs['incl']) || !isset($costs['incl'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     "Invalid arguments supplied. The 'costs' parameter requires an amount incl. and excl. VAT."
                ),
                'POSTNL-0151'
            );
        }

        /**
         * Depending on tax calculation settings we need either the costs with or without VAT.
         */
        if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
            $costs = $costs['incl'];
        } else {
            $costs = $costs['excl'];
        }

        $costsValidator      = new Zend_Validate_Float();
        $costsRangeValidator = new Zend_Validate_Between(array('min' => 0, 'max' => 2, 'inclusive' => true));

        /**
         * Validate the costs.
         */
        if (!$costsValidator->isValid($costs) || !$costsRangeValidator->isValid($costs)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid costs supplied: %s Costs have to be a float or int between 0 and 2.',
                     $costs
                ),
                'POSTNL-0139'
            );
        }

        return (float) $costs;
    }

    /**
     * @param $params
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    protected function _getSavePhonePostData($params)
    {
        /**
         * A phone number needs to be specified.
         */
        if (!isset($params['number'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     "Invalid arguments supplied. The 'number' parameter is required."
                ),
                'POSTNL-0148'
            );
        }

        $phoneNumber = $params['number'];
        $phoneNumber = str_replace(array('-', ' '), '', $phoneNumber);

        $phoneValidator = new Zend_Validate_Regex(array('pattern' => self::MOBILE_PHONE_NUMBER_REGEX));

        /**
         * Validate the phone number.
         */
        if (!$phoneValidator->isValid($phoneNumber)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid mobile phone number supplied: %s.',
                     $phoneNumber
                ),
                'POSTNL-0149'
            );
        }

        return $phoneNumber;
    }

    /**
     * Validates the supplied parameters for the saveSelectedOption action.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getSaveSelectionPostData($params)
    {
        /**
         * In order to save the selected option we need a type, delivery date and optional extra costs.
         */
        if (!isset($params['type']) || !isset($params['date']) || !isset($params['costs'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid arguments supplied. In order to save a selected option, a type, delivery date and '
                     . 'optional extra costs are required.'
                ),
                'POSTNL-0138'
            );
        }

        $type  = $params['type'];
        $date  = $params['date'];
        $costs = Mage::helper('core')->jsonDecode($params['costs']);

        $from = false;
        if (!empty($params['from'])) {
            $from = $params['from'];
        }

        $to = false;
        if (!empty($params['to'])) {
            $to = $params['to'];
        }

        /**
         * The costs object should contain an amount incl. VAT and excl. VAT.
         */
        if (!isset($costs['incl']) || !isset($costs['incl'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     "Invalid arguments supplied. The 'costs' parameter requires an amount incl. and excl. VAT."
                ),
                'POSTNL-0151'
            );
        }

        /**
         * Depending on tax calculation settings we need either the costs with or without VAT.
         */
        if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
            $costs = $costs['incl'];
        } else {
            $costs = $costs['excl'];
        }

        /**
         * Get validation classes for the postcode and housenumber values.
         */
        $validTypes = $this->getValidTypes();

        $typeValidator  = new Zend_Validate_InArray(array('haystack' => $validTypes));
        $dateValidator  = new Zend_Validate_Date(array('format' => 'd-m-Y'));
        $costsValidator = new Zend_Validate_Float();
        $timeValidator  = new Zend_Validate_Regex(array('pattern' => self::TIME_REGEX));

        /**
         * Validate the postcode.
         */
        if (!$typeValidator->isValid($type)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid type supplied: %s',
                     $type
                ),
                'POSTNL-0139'
            );
        }

        /**
         * Validate the delivery date.
         */
        if (!$dateValidator->isValid($date)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid delivery date supplied: %s',
                     $date
                ),
                'POSTNL-0121'
            );
        }

        /**
         * Validate the costs.
         */
        if (!$costsValidator->isValid($costs)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid extra costs supplied: %s Extra costs must be supplied as a float.',
                     $costs
                ),
                'POSTNL-0140'
            );
        }

        $data = array(
            'type'  => $type,
            'date'  => $date,
            'costs' => $costs,
        );

        if ($from && $timeValidator->isValid($from)) {
            $data['from'] = $from;
        }

        if ($to && $timeValidator->isValid($to)) {
            $data['to'] = $to;
        }

        if (isset($params['number'])) {
            $phoneNumber = $this->_getSavePhonePostData($params);
            $data['number'] = $phoneNumber;
        }

        if (!array_key_exists('address', $params)) {
            return $data;
        }

        $address = $this->_validateAddress($params['address']);
        $data['address'] = $address;

        return $data;
    }

    /**
     * @param array $addressData
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _validateAddress($addressData)
    {
        $address = Mage::helper('core')->jsonDecode($addressData);

        if (!isset($address['City'])
            || !isset($address['Countrycode'])
            || !isset($address['Street'])
            || !isset($address['HouseNr'])
            || !isset($address['Zipcode'])
            || !isset($address['Name'])
        ) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid argument supplied. A valid PakjeGemak address must contain at least a city, country '
                     . 'code, street, house number and zipcode.'
                ),
                'POSTNL-0141'
            );
        }

        $city        = $address['City'];
        $countryCode = $address['Countrycode'];
        $street      = $address['Street'];
        $houseNumber = $address['HouseNr'];
        $postcode    = str_replace(' ', '', $address['Zipcode']);
        $name        = $address['Name'];

        $countryCodes = Mage::getResourceModel('directory/country_collection')->getColumnValues('iso2_code');

        $cityValidator        = new Zend_Validate_Regex(array('pattern' => self::CITY_NAME_REGEX));
        $countryCodeValidator = new Zend_Validate_InArray(array('haystack' => $countryCodes));
        $streetValidator      = new Zend_Validate_Regex(array('pattern' => self::STREET_NAME_REGEX));
        $housenumberValidator = new Zend_Validate_Digits();

        if (!$cityValidator->isValid($city)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid city supplied: %s.',
                     $city
                ),
                'POSTNL-0142'
            );
        }

        if (!$countryCodeValidator->isValid($countryCode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid country code supplied: %s.',
                     $countryCode
                ),
                'POSTNL-0143'
            );
        }

        if (!$streetValidator->isValid($street)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid street supplied: %s.',
                     $street
                ),
                'POSTNL-0144'
            );
        }

        if (!$housenumberValidator->isValid($houseNumber)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid housenumber supplied: %s.',
                     $houseNumber
                ),
                'POSTNL-0145'
            );
        }

        $postcodeValidator    = new Zend_Validate_PostCode('nl_' . $countryCode);

        if (!$postcodeValidator->isValid($postcode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid postcode supplied: %s.',
                     $postcode
                ),
                'POSTNL-0146'
            );
        }

        /**
         * Names are essentially impossible to build a regex for. Eventually you will run into a name that the regex
         * thinks is 'wrong' and you will have offended someone. Better to just strip any tags to prevent XSS attacks.
         */
        $name = Mage::helper('core')->stripTags($name);

        $data = array(
            'city'        => $city,
            'countryCode' => $countryCode,
            'street'      => $street,
            'houseNumber' => $houseNumber,
            'postcode'    => $postcode,
            'name'        => $name,
        );

        if (isset($address['PhoneNumber']) && !empty($address['PhoneNumber'])) {
            $phoneNumber = $address['PhoneNumber'];
            $phoneNumber = str_replace(array('-', ' '), '', $phoneNumber);
            $phoneNumberValidator = new Zend_Validate_Regex(array('pattern' => self::PHONE_NUMBER_REGEX));

            if (!$phoneNumberValidator->isValid($phoneNumber)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                         'Invalid phone number supplied: %s.',
                             $phoneNumber
                    ),
                    'POSTNL-0154'
                );
            }

            $data['telephone'] = $phoneNumber;
        }

        if (!array_key_exists('HouseNrExt', $address)) {
            return $data;
        }

        $houseNumberExtension = $address['HouseNrExt'];

        $houseNumberExtensionValidator = new Zend_Validate_Regex(array('pattern' => self::HOUSENR_EXT_REGEX));

        if (!$houseNumberExtensionValidator->isValid($houseNumberExtension)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                     'Invalid housenumber extension supplied: %s.',
                     $houseNumberExtension
                ),
                'POSTNL-0147'
            );
        }

        $data['houseNumberExtension'] = $houseNumberExtension;

        return $data;
    }

    /**
     * Parses and validates data for the GetDeliveryTimeframes request.
     *
     * @param array $params
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getTimeframePostData($params)
    {
        /**
         * The GetDeliveryTimeframes action requires a postcode and a housenumber.
         */
        if (!isset($params['postcode']) || !isset($params['housenumber'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid arguments supplied. GetDeliveryTimeframes requires a postcode and a housenumber.'
                ),
                'POSTNL-0124'
            );
        }

        $country = $params['country'];
        if ($country != 'NL' && $country != 'BE') {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid country supplied for GetDeliveryTimeframes request: %s',
                    $country
                ),
                'POSTNL-0233'
            );
        }

        $postcode    = $params['postcode'];
        $housenumber = $params['housenumber'];

        /**
         * Remove spaces from housenumber and postcode fields.
         */
        $postcode    = str_replace(' ', '', $postcode);
        $postcode    = strtoupper($postcode);
        $housenumber = trim($housenumber);

        /**
         * Get validation classes for the postcode and housenumber values.
         */
        $postcodeValidator    = new Zend_Validate_PostCode('nl_' . $country);
        $housenumberValidator = new Zend_Validate_Digits();

        /**
         * Validate the postcode.
         */
        if (!$postcodeValidator->isValid($postcode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid postcode supplied for GetDeliveryTimeframes request: %s Postcodes may only contain 4 '
                    . 'numbers and 2 letters.',
                    $postcode
                ),
                'POSTNL-0125'
            );
        }

        /**
         * Validate the housenumber.
         */
        if (!$housenumberValidator->isValid($housenumber)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid housenumber supplied for GetDeliveryTimeframes request: %s Housenumbers may only contain'
                    . ' digits.',
                    $housenumber
                ),
                'POSTNL-0126'
            );
        }

        /**
         * Get the delivery date. If it was supplied, we need to validate it. Otherwise we take tomorrow as the delivery
         * day.
         */
        if (array_key_exists('deliveryDate', $params)) {
            $deliveryDate = $params['deliveryDate'];

            $validator = new Zend_Validate_Date(array('format' => 'd-m-Y'));
            if (!$validator->isValid($deliveryDate)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Invalid delivery date supplied: %s',
                        $deliveryDate
                    ),
                    'POSTNL-0121'
                );
            }
        } else {
            $timeZone = Mage::helper('postnl')->getStoreTimeZone(Mage::app()->getStore()->getId(), true);
            $deliveryDate = new DateTime('now', $timeZone);
            $deliveryDate->setTimestamp(Mage::getModel('core/date')->timestamp())
                         ->add(new DateInterval('P1D'));
            $deliveryDate = $deliveryDate->format('d-m-Y');
        }

        $data = array(
            'postcode'     => $postcode,
            'housenumber'  => $housenumber,
            'country'      => $country,
            'deliveryDate' => $deliveryDate,
        );

        return $data;
    }

    /**
     * Gets and validates data for the getNearestLocations request.
     *
     * @param array $postData
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getLocationPostData($postData)
    {
        /**
         * This action requires either a postcode or a longitude and latitude in order to get the nearest post office
         * locations.
         */
        if ((!array_key_exists('lat', $postData) || !array_key_exists('long', $postData))
            && !array_key_exists('postcode', $postData)
        ) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid arguments supplied. getNearestLocations requires a postcode or a longitude and latitude.'
                ),
                'POSTNL-0120'
            );
        }

        /**
         * Get the delivery date. If it was supplied, we need to validate it. Otherwise we take tomorrow as the delivery
         * day.
         */
        if (array_key_exists('deliveryDate', $postData)) {
            $deliveryDate = $postData['deliveryDate'];

            $validator = new Zend_Validate_Date(array('format' => 'd-m-Y'));
            if (!$validator->isValid($deliveryDate)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Invalid delivery date supplied: %s',
                        $deliveryDate
                    ),
                    'POSTNL-0121'
                );
            }
        } else {
            $timeZone = Mage::helper('postnl')->getStoreTimeZone(Mage::app()->getStore()->getId(), true);
            $deliveryDate = new DateTime('now', $timeZone);
            $deliveryDate->setTimestamp(Mage::getModel('core/date')->timestamp())
                         ->add(new DateInterval('P1D'));
            $deliveryDate = $deliveryDate->format('d-m-Y');
        }

        $country = $postData['country'];
        if ($country != 'NL' && $country != 'BE') {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid country supplied for getNearestLocations request: %s',
                    $country
                ),
                'POSTNL-0232'
            );
        }

        /**
         * If a postcode was supplied, validate it and return it as an array.
         */
        if (array_key_exists('postcode', $postData)) {
            $postcode = $postData['postcode'];
            $postcode = strtoupper(str_replace(' ', '', $postcode));

            $validator = new Zend_Validate_PostCode('nl_' . $country);
            if (!$validator->isValid($postcode)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Invalid postcode supplied for getNearestLocations request: %s',
                        $postcode
                    ),
                    'POSTNL-0118'
                );
            }

            $data = array(
                'postcode'     => $postcode,
                'country'      => $country,
                'deliveryDate' => $deliveryDate,
            );
            return $data;
        }

        /**
         * If a latitude and longitude was supplied, validate these and return them as an array.
         */
        $lat  = $postData['lat'];
        $long = $postData['long'];

        $latValidator  = new Zend_Validate_Regex(array('pattern' => self::LATITUDE_REGEX));
        $longValidator = new Zend_Validate_Regex(array('pattern' => self::LONGITUDE_REGEX));
        if (!$latValidator->isValid($lat) || !$longValidator->isValid($long)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid coordinates supplied for getNearestLocations request. lat: %s, long: %s',
                    $lat,
                    $long
                ),
                'POSTNL-0119'
            );
        }

        $data = array(
            'lat'          => $postData['lat'],
            'long'         => $postData['long'],
            'country'      => $country,
            'deliveryDate' => $deliveryDate,
        );

        return $data;
    }

    /**
     * Gets and validates data for the getLocationsInArea request.
     *
     * @param array $postData
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getLocationInAreaPostData($postData)
    {
        if (!isset($postData['northEastLat'])
            || !isset($postData['northEastLng'])
            || !isset($postData['southWestLat'])
            || !isset($postData['southWestLng'])
        ) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid arguments supplied. getNearestLocationsInArea requires two sets of coordinates.'
                ),
                'POSTNL-0128'
            );
        }

        $northEastLat = $postData['northEastLat'];
        $northEastLng = $postData['northEastLng'];
        $southWestLat = $postData['southWestLat'];
        $southWestLng = $postData['southWestLng'];

        $latValidator  = new Zend_Validate_Regex(array('pattern' => self::LATITUDE_REGEX));
        $longValidator = new Zend_Validate_Regex(array('pattern' => self::LONGITUDE_REGEX));
        if (!$latValidator->isValid($northEastLat) || !$longValidator->isValid($northEastLng)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid NE coordinates supplied for getLocationsInArea request. lat: %s, long: %s',
                    $northEastLat,
                    $northEastLng
                ),
                'POSTNL-0129'
            );
        }

        if (!$latValidator->isValid($southWestLat) || !$longValidator->isValid($southWestLng)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid SW coordinates supplied for getLocationsInArea request. lat: %s, long: %s',
                    $southWestLat,
                    $southWestLng
                ),
                'POSTNL-0130'
            );
        }

        /**
         * Get the delivery date. If it was supplied, we need to validate it. Otherwise we take tomorrow as the delivery
         * day.
         */
        if (array_key_exists('deliveryDate', $postData)) {
            $deliveryDate = $postData['deliveryDate'];

            $validator = new Zend_Validate_Date(array('format' => 'd-m-Y'));
            if (!$validator->isValid($deliveryDate)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Invalid delivery date supplied: %s',
                        $deliveryDate
                    ),
                    'POSTNL-0121'
                );
            }
        } else {
            $timeZone = Mage::helper('postnl')->getStoreTimeZone(Mage::app()->getStore()->getId(), true);
            $deliveryDate = new DateTime('now', $timeZone);
            $deliveryDate->setTimestamp(Mage::getModel('core/date')->timestamp())
                         ->add(new DateInterval('P1D'));
            $deliveryDate = $deliveryDate->format('d-m-Y');
        }

        $country = $postData['country'];
        if ($country != 'NL' && $country != 'BE') {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid country supplied for getLocationsInArea request: %s',
                    $country
                ),
                'POSTNL-0234'
            );
        }

        $data = array(
            'northEast'    => array(
                'lat'  => $northEastLat,
                'long' => $northEastLng,
            ),
            'southWest'    => array(
                'lat'  => $southWestLat,
                'long' => $southWestLng,
            ),
            'country'      => $country,
            'deliveryDate' => $deliveryDate,
        );

        return $data;
    }

    /**
     * Save new shipping method rate. We need to re-collect the quote's totals as the shipping costs may have changed.
     *
     * @return $this|bool
     */
    protected function _updateShippingMethod()
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->removeAllShippingRates();

        $shippingAddress->setCollectShippingRates(true);

        $quote->collectTotals()
              ->save();

        $shippingAddress->setCollectShippingRates(true);

        return $this;
    }
}
