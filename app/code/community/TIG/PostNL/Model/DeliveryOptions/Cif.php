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
 *
 * @method TIG_PostNL_Model_DeliveryOptions_Cif setStoreId(int $value)
 * @method int                                  getStoreId()
 */
class TIG_PostNL_Model_DeliveryOptions_Cif extends TIG_PostNL_Model_Core_Cif
{
    /**
     * Delivery option codes.
     */
    const PAKJEGEMAK_DELIVERY_OPTION         = 'PG';
    const PAKJEGEMAK_EXPRESS_DELIVERY_OPTION = 'PGE';
    const PAKKETAUTOMAAT_DELIVERY_OPTION     = 'PA';

    /**
     * Config options used by the getDeliveryDate service.
     */
    const XPATH_SHIPPING_DURATION    = 'postnl/delivery_options/shipping_duration';
    const XPATH_CUTOFF_TIME          = 'postnl/delivery_options/cutoff_time';
    const XPATH_ALLOW_SUNDAY_SORTING = 'postnl/delivery_options/allow_sunday_sorting';
    const XPATH_SUNDAY_CUTOFF_TIME   = 'postnl/delivery_options/sunday_cutoff_time';
    const XPATH_DELIVERY_DAYS_NUMBER = 'postnl/delivery_options/delivery_days_number';

    /**
     * Check if the module is set to test mode
     *
     * @param bool $storeId
     *
     * @return boolean
     *
     * @see TIG_PostNL_Helper_Checkout::isTestMode()
     */
    public function isTestMode($storeId = false)
    {
        $testMode = Mage::helper('postnl/cif')->isTestMode($storeId);

        return $testMode;
    }

    /**
     * Gets the delivery date based on the shop's cut-off time.
     *
     * @param string                 $postcode
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryDate($postcode, Mage_Sales_Model_Quote $quote)
    {
        if (empty($postcode)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for GetDeliveryDay request.'),
                'POSTNL-0115'
            );
        }

        $shippingDuration = Mage::helper('postnl/deliveryoptions')->getShippingDuration($quote);

        $soapParams = array(
            'GetDeliveryDate' => array(
                'Postalcode'                 => $postcode,
                'ShippingDate'               => date('d-m-Y H:i:s', Mage::getModel('core/date')->timestamp()),
                'ShippingDuration'           => $shippingDuration,
                'CutOffTime'                 => $this->_getCutOffTime(),
                'AllowSundaySorting'         => $this->_getSundaySortingAllowed(),
                'CutOffTimeForSundaySorting' => $this->_getSundaySortingCutOffTime(),
            ),
            'Message' => $this->_getMessage('')
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'deliverydate',
            'GetDeliveryDate',
            $soapParams
        );

        if (!is_object($response)
            || !isset($response->DeliveryDate)
            || !is_string($response->DeliveryDate)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid GetDeliveryDate response: %s', "\n" . var_export($response, true)),
                'POSTNL-0116'
            );
        }

        return $response->DeliveryDate;
    }

    /**
     * Get evening timeframes for the specified postcode and delivery window.
     *
     * @param array $data
     *
     * @return StdClass
     *
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryTimeframes($data)
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for request.'),
                'POSTNL-0117'
            );
        }

        $startDate = $data['deliveryDate'];

        /**
         * To calculate the end date we need to number of days we want to display minus 1.
         */
        $storeId = Mage::app()->getStore()->getId();
        $maximumNumberOfDeliveryDays = (int) Mage::getStoreConfig(self::XPATH_DELIVERY_DAYS_NUMBER, $storeId);
        $maximumNumberOfDeliveryDays--;

        $endDate = date('d-m-Y', strtotime("+{$maximumNumberOfDeliveryDays} days", strtotime($startDate)));

        $soapParams = array(
            'Timeframe' => array(
                'PostalCode'  => $data['postcode'],
                'HouseNumber' => $data['housenumber'],
                'StartDate'   => $startDate,
                'EndDate'     => $endDate,
            ),
            'Message' => $this->_getMessage('')
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'timeframe',
            'GetDeliveryTimeframes',
            $soapParams
        );

        if (!isset($response->Timeframes)
            || !isset($response->Timeframes->Timeframe)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid response for getDeliveryTimeframes request: %s', $response),
                'POSTNL-0122'
            );
        }

        return $response->Timeframes->Timeframe;
    }

    /**
     * Gets nearby post office locations. This service can be based off of a postcode or a set of coordinates. Results may
     * include PakjeGemak, PakjeGemak Express or pakket automaat locations based on the configuration of the extension.
     *
     * @param $data
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function getNearestLocations($data)
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for request.'),
                'POSTNL-0117'
            );
        }

        $location = $this->_getLocation($data);
        $message  = $this->_getMessage('');

        $soapParams = array(
            'Location' => $location,
            'Message'  => $message,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'location',
            'GetNearestLocations',
            $soapParams
        );

        if (!isset($response->GetLocationsResult)
            || !isset($response->GetLocationsResult->ResponseLocation)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid response for GetNearestLocations request: %s', $response),
                'POSTNL-0123'
            );
        }

        return $response->GetLocationsResult->ResponseLocation;
    }

    /**
     * gets post office locations within a specific area, marked by a set of coordinates.
     *
     * @param $data
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function getLocationsInArea($data)
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for request.'),
                'POSTNL-0117'
            );
        }

        $location = $this->_getLocation($data);
        $message  = $this->_getMessage('');

        $soapParams = array(
            'Location' => $location,
            'Message'  => $message,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'location',
            'GetLocationsInArea',
            $soapParams
        );

        if (!isset($response->GetLocationsResult)
            || !isset($response->GetLocationsResult)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid response for getLocationsInArea request: %s', $response),
                'POSTNL-0123'
            );
        }

        return $response->GetLocationsResult->ResponseLocation;
    }

    /**
     * Gets the regular cut-off time for this storeview.
     *
     * @return string
     */
    protected function _getCutOffTime()
    {
        $storeId = $this->getStoreId();

        $cutOffTime = Mage::getStoreConfig(self::XPATH_CUTOFF_TIME, $storeId);
        if (!$cutOffTime) {
            $cutOffTime = '23:59:59';
        }

        return $cutOffTime;
    }

    /**
     * Checks whether sunday sorting is allowed for this storeview.
     *
     * @return string
     */
    protected function _getSundaySortingAllowed()
    {
        $storeId = $this->getStoreId();

        $allowSundaySorting = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);
        if ($allowSundaySorting === true) {
            return 'true';
        }

        return 'false';
    }

    /**
     * Gets the regular cut-off time for this storeview.
     *
     * @return string
     */
    protected function _getSundaySortingCutOffTime()
    {
        $storeId = $this->getStoreId();

        $cutOffTime = Mage::getStoreConfig(self::XPATH_SUNDAY_CUTOFF_TIME, $storeId);
        if (!$cutOffTime) {
            $cutOffTime = '23:59:59';
        }

        return $cutOffTime;
    }

    /**
     * Gets the location SOAP parameter array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _getLocation($data)
    {
        /**
         * Start building the location array by adding the available delivery options.
         */
        $location = array(
            'DeliveryOptions' => $this->_getDeliveryOptions(),
        );

        /**
         * Next we add the desired delivery date. If none is specified, we set it to tomorrow.
         */
        if (isset($data['deliveryDate'])) {
            $location['DeliveryDate'] = $data['deliveryDate'];
        } else {
            $tomorrow = strtotime('tomorrow', Mage::getModel('core/date')->timestamp());
            $location['DeliveryDate'] = date('d-m-Y', $tomorrow);
        }

        /**
         * If an opening time was specified, add that as well.
         */
        if (isset($data['openingTime'])) {
            $location['OpeningTime'] = $data['openingTime'];
        }

        /**
         * Add the postcode if available.
         */
        if (isset($data['postcode'])) {
            $location['Postalcode'] = $data['postcode'];
        }

        /**
         * Add coordinates if both a latitude and longitude are available.
         */
        if (isset($data['lat']) && isset($data['long'])) {
            $location['Coordinates'] = array(
                'Latitude'  => $data['lat'],
                'Longitude' => $data['long'],
            );
        }

        /**
         * Add coordinates for an area marked by two sets of coordinates.
         *
         * Please note that PostNL uses NW and SE, while google maps uses NE and SW.
         */
        if (isset($data['northEast']['lat'])
            && isset($data['northEast']['long'])
            && isset($data['southWest']['lat'])
            && isset($data['southWest']['long'])
        ) {
            $location['CoordinatesNorthWest'] = array(
                'Latitude'  => $data['northEast']['lat'],
                'Longitude' => $data['southWest']['long'],
            );

            $location['CoordinatesSouthEast'] = array(
                'Latitude'  => $data['southWest']['lat'],
                'Longitude' => $data['northEast']['long'],
            );
        }

        return $location;
    }

    /**
     * Gets an array of allowed delivery options.
     *
     * @return array
     */
    protected function _getDeliveryOptions()
    {
        $deliveryOptions = array();

        $helper = Mage::helper('postnl/deliveryOptions');
        if ($helper->canUsePakjeGemak()) {
            $deliveryOptions[] = self::PAKJEGEMAK_DELIVERY_OPTION;
        }

        if ($helper->canUsePakjeGemakExpress()) {
            $deliveryOptions[] = self::PAKJEGEMAK_EXPRESS_DELIVERY_OPTION;
        }

        if ($helper->canUsePakketAutomaat()) {
            $deliveryOptions[] = self::PAKKETAUTOMAAT_DELIVERY_OPTION;
        }

        return $deliveryOptions;
    }
}
