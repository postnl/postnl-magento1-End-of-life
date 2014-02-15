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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
     * Check to see if PostNL delivery options are active and available.
     *
     * @return boolean
     */
    protected function _canUseDeliveryOptions()
    {
        $helper = Mage::helper('postnl/deliveryOptions');

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $canUseDeliveryOptions = $helper->canUseDeliveryOptions($quote);

        return $canUseDeliveryOptions;
    }

    /**
     * Get the first possible delivery date based on the shop's cut-off time.
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    // public function getDeliveryDateAction()
    // {
        // /**
         // * This action may only be called using AJAX requests
         // */
        // if (!$this->getRequest()->isAjax()) {
            // $this->_redirect('');
//
            // return $this;
        // }
//
        // if (!$this->_canUseDeliveryOptions()) {
            // $this->getResponse()
                 // ->setBody('not_allowed');
//
            // return $this;
        // }
//
        // $storeId = Mage::app()->getStore()->getId();
//
        // $data = $this->getRequest()->getPost();
        // $postcode = $data['postcode'];
    // }

    /**
     * Get possible evening delivery time frames based on an earliest possible delivery date.
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    public function getDeliveryTimeframesAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->_redirect('');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $params = $this->getRequest()->getParams();
        $params =  array(
            'postcode'    => '1394GA',
            'housenumber' => 43,
        );

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

        if (!is_array($response)) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $timeframes = Mage::helper('core')->jsonEncode($response);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($timeframes);

        return $this;
    }

    /**
     * Get the nearest post office locations based on either a postcode or a longitude and latitude.
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    public function getNearestLocationsAction()
    {
        /**
         * This action may only be called using AJAX requests
         */
        if (!$this->getRequest()->isAjax()) {
            $this->_redirect('');

            return $this;
        }

        if (!$this->_canUseDeliveryOptions()) {
            $this->getResponse()
                 ->setBody('not_allowed');

            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $postData = $this->getRequest()->getPost();

        /**
         * TEMPORARY DEBUG CODE
         */
        $postData = array(
            'postcode' => '1055GH',
        );

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

        if (!is_array($response)) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $locations = Mage::helper('core')->jsonEncode($response);

        /**
         * Return the result as a json response
         */
        $this->getResponse()
             ->setHeader('Content-type', 'application/x-json')
             ->setBody($locations);

        return $this;
    }

    protected function _getTimeframePostData($params)
    {
        /**
         * The getEveningTimeframes action requires a postcode and a housenumber.
         */
        if (!isset($params['postcode']) || !isset($params['housenumber'])) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid arguments supplied. getEveningTimeframes requires a postcode and a housenumber.'
                ),
                'POSTNL-0124'
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
        $postcodeValidator    = new Zend_Validate_PostCode('nl_NL');
        $housenumberValidator = new Zend_Validate_Digits();

        /**
         * Validate the postcode.
         */
        if (!$postcodeValidator->isValid($postcode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid postcode supplied for getEveningTimeframes request: %s Postcodes may only contain 4 numbers and 2 letters.',
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
                    'Invalid housenumber supplied for getEveningTimeframes request: %s Housenumbers may only contain digits.',
                    $housenumber
                ),
                'POSTNL-0126'
            );
        }

        /**
         * Get the delivery date. If it was supplied, we need to validate it. Otherwise we take tomorrow as the delivery day.
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
            $tomorrow = strtotime('tomorrow', Mage::getModel('core/date')->timestamp());
            $deliveryDate = date('d-m-Y', $tomorrow);
        }

        $data = array(
            'postcode'     => $postcode,
            'housenumber'  => $housenumber,
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
         * This action requires either a postcode or a longitude and latitude in order to get the nearest post office locations.
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
         * Get the delivery date. If it was supplied, we need to validate it. Otherwise we take tomorrow as the delivery day.
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
            $tomorrow = strtotime('tomorrow', Mage::getModel('core/date')->timestamp());
            $deliveryDate = date('d-m-Y', $tomorrow);
        }

        /**
         * If a postcode was supplied, validate it and return it as an array.
         */
        if (array_key_exists('postcode', $postData)) {
            $postcode = $postData['postcode'];

            $validator = new Zend_Validate_PostCode('nl_NL');
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
                'postcode'     => $postData['postcode'],
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
            'deliveryDate' => $deliveryDate,
        );

        return $data;
    }
}
