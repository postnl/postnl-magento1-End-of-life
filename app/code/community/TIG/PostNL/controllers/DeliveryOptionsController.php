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
    public function getDeliveryDateAction()
    {
        if (!$this->_canUseDeliveryOptions()) {
            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $data = $this->getRequest()->getPost();
        $postcode = $data['postcode'];
    }

    /**
     * Get possible evening delivery time frames based on an earliest possible delivery date.
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    public function getEveningTimeframesAction()
    {
        if (!$this->_canUseDeliveryOptions()) {
            return $this;
        }

        $storeId = Mage::app()->getStore()->getId();

        $postData = $this->getRequest()->getPost();
        $postcode = /*$postData['postcode']*/'1043AJ';
        $housenumber = /*$postData['housenumber']*/'8';
        $deliveryDate = /*$postData['deliveryDate']*/date('d-m-Y', Mage::getSingleton('core/date')->timestamp());

        $data = array(
            'postcode' => $postcode,
            'housenumber' => $housenumber,
            'deliveryDate' => $deliveryDate,
        );

        $cif = Mage::getModel('postnl_deliveryoptions/cif');
        $response = $cif->setStoreId($storeId)
                        ->getEveningTimeframes($data);

        echo '<pre>';var_dump($response);
    }

    /**
     * Get the nearest post office locations based on either a postcode or a longitude and latitude.
     *
     * @return TIG_PostNL_DeliveryOptionsController
     */
    public function getNearestLocationsAction()
    {
        if (!$this->_canUseDeliveryOptions()) {
            return $this;
        }

        $postData = $this->getRequest()->getPost();

        /**
         * TEMPORARY DEBUG CODE
         */
        $postData = array(
            'lat' => '52.0130280656751',
            'long' => '5.10134310565209',
        );

        try {
            $data = $this->_getLocationPostData($postData);
        } catch (Exception $e) {
            Mage::helper('postnl/deliveryOptions')->logException($e);

            $this->getResponse()
                 ->setBody('invalid_data');

            return $this;
        }

        $cif = Mage::getModel('postnl_deliveryoptions/cif');
        $response = $cif->setStoreId($storeId)
                        ->getNearestLocations($data);

        echo '<pre>';var_dump($response);
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

        if (array_key_exists('deliveryDate', $postData)) {
            $deliveryDate = $postData['deliveryDate'];

            $validator = new Zend_Validate_Date(array('format' => 'd-m-Y'));
            if (!$validator->isValid($deliveryDate)) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'Invalid delivery date supplied for getNearestLocations request: %s',
                        $deliveryDate
                    ),
                    'POSTNL-0121'
                );
            }
        } else {
            $deliveryDate = date('d-m-Y', Mage::getModel('core/date')->timestamp());
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
