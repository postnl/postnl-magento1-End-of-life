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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
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
    const DOMESTIC_DELIVERY_OPTION           = 'Daytime';
    const EVENING_DELIVERY_OPTION            = 'Evening';
    const SUNDAY_DELIVERY_OPTION             = 'Sunday';
    const SAMEDAY_DELIVERY_OPTION            = 'Sameday';
    const PICKUP_DELIVERY_OPTION             = 'Pickup';

    /**
     * Config options used by the getDeliveryDate service.
     */
    const XPATH_SHIPPING_DURATION       = 'postnl/cif_labels_and_confirming/shipping_duration';
    const XPATH_CUTOFF_TIME             = 'postnl/cif_labels_and_confirming/cutoff_time';
    const XPATH_ALLOW_SUNDAY_SORTING    = 'postnl/delivery_options/allow_sunday_sorting';
    const XPATH_ALLOW_SUNDAY_SORTING_BE = 'postnl/delivery_options_int/allow_sunday_sorting_be';
    const XPATH_SUNDAY_CUTOFF_TIME      = 'postnl/cif_labels_and_confirming/sunday_cutoff_time';
    const XPATH_DELIVERY_DAYS_NUMBER    = 'postnl/delivery_options/delivery_days_number';
    const XPATH_DELIVERY_DAYS_NUMBER_BE = 'postnl/delivery_options_int/delivery_days_number_be';
    const XPATH_ENABLE_SUNDAY_DELIVERY  = 'postnl/delivery_options/enable_sunday_delivery';

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
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $testMode = $helper->isTestMode($storeId);

        return $testMode;
    }

    /**
     * Gets the delivery date based on the shop's cut-off time.
     *
     * @param string                 $postcode
     * @param string                 $country
     * @param Mage_Sales_Model_Quote $quote
     *
     * @param string                 $for delivery or pickup
     *
     * @return string
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryDate($postcode, $country = 'NL', Mage_Sales_Model_Quote $quote, $for = 'delivery')
    {
        if (empty($postcode)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for GetDeliveryDay request.'),
                'POSTNL-0115'
            );
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        $shippingDuration = $helper->getQuoteShippingDuration($quote);

        $date = new DateTime('now', $helper->getStoreTimeZone($quote->getStoreId(), true));
        $date->setTimezone(new DateTimeZone('Europe/Berlin'));

        /**
         * Build CutOffTimes array
         *
         * Day 00 indicates weekdays and saturday, while day 07 indicates sunday
         */
        $CutOffTimes = $this->_getCutOffTimes($quote->getStoreId());

        $options = $this->_getDeliveryDateOptionsArray($shippingDuration, $country, $for);

        $soapParams = array(
            'GetDeliveryDate' => array(
                'PostalCode'                 => $postcode,
                'ShippingDate'               => $date->format('d-m-Y H:i:s'),
                'ShippingDuration'           => $shippingDuration,
                'AllowSundaySorting'         => $this->_getSundaySortingAllowed($country),
                'CutOffTimes'                => $CutOffTimes,
                'Options'                    => $options,
                'CountryCode'                => $country,
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
     * Get evening time frames for the specified postcode and delivery window.
     *
     * @param array  $data
     *
     * @param string $for delivery or pickup
     *
     * @return false|StdClass[]
     * @throws TIG_PostNL_Exception
     */
    public function getDeliveryTimeframes($data, $for = 'delivery')
    {
        if (empty($data)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No data available for request.'),
                'POSTNL-0117'
            );
        }

        $startDate = $data['deliveryDate'];

        /**
         * To calculate the end date we need to number of days we want to display.
         */
        $storeId = Mage::app()->getStore()->getId();

        if ($data['country'] == 'BE') {
            $maximumNumberOfDeliveryDays = (int) Mage::getStoreConfig(self::XPATH_DELIVERY_DAYS_NUMBER_BE, $storeId);
        } else {
            $maximumNumberOfDeliveryDays = (int) Mage::getStoreConfig(self::XPATH_DELIVERY_DAYS_NUMBER, $storeId);

            /**
             * For other countries we need the days minus 1.
             */
            $maximumNumberOfDeliveryDays--;
        }

        $endDate = new DateTime($startDate, new DateTimeZone('UTC'));
        $endDate->add(new DateInterval("P{$maximumNumberOfDeliveryDays}D"));

        $options = $this->_getDeliveryTimeframesOptionsArray($data['country'], $for);

        $soapParams = array(
            'Timeframe' => array(
                'PostalCode'    => $data['postcode'],
                'HouseNr'       => $data['housenumber'],
                'CountryCode'   => $data['country'],
                'StartDate'     => $startDate,
                'EndDate'       => $endDate->format('d-m-Y'),
                'SundaySorting' => $this->_getSundaySortingAllowed($data['country']),
                'Options'       => $options
            ),
            'Message' => $this->_getMessage('')
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'timeframe',
            'GetTimeframes',
            $soapParams
        );

        if (empty($response->Timeframes)) {
            return false;
        }

        if (!isset($response->Timeframes->Timeframe)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid response for getDeliveryTimeframes request: %s',
                    var_export($response, true)
                ),
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
            'Location'    => $location,
            'Message'     => $message,
            'Countrycode' => $data['country']
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
     * Gets post office locations within a specific area, marked by a set of coordinates.
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
            'Location'    => $location,
            'Message'     => $message,
            'Countrycode' => $data['country']

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
     * Gets the regular cut-off time for this storeview.
     *
     * @param $storeId
     *
     * @return string
     */
    protected function _getCutOffTimes($storeId)
    {
        $monSatCutoff = $this->_getCutOffTime();

        $helper = $this->_getHelper('deliveryOptions');
        $sameDayDelivery = Mage::getStoreConfig($helper::XPATH_ENABLE_SAMEDAY_DELIVERY, $storeId);
        if ($sameDayDelivery) {
            $date                  = $helper->getDateTime('now');
            $sameDayDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_SAMEDAY_CUTOFF_TIME, $storeId);
            $regularDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_CUTOFF_TIME, $storeId);

            if ($date->getTimestamp() < $sameDayDeliveryCutoff->getTimestamp()) {
                $monSatCutoff = $sameDayDeliveryCutoff->format('H:i:00');
            } elseif ($date->getTimestamp() > $regularDeliveryCutoff->getTimestamp() && $date->format('N') != 5) {
                $monSatCutoff = $sameDayDeliveryCutoff->format('H:i:00');
            } elseif (
                $helper->quoteIsFood()
                && $helper->getQuoteFoodType() == $helper::FOOD_TYPE_COOL_PRODUCTS
                && $date->format('N') == 5
            ) {
                $monSatCutoff = $sameDayDeliveryCutoff->format('H:i:00');
            }
        }

        $CutOffTimes = array(
            array(
                'Day'   => '00',
                'Time'  => $monSatCutoff,
            ),
            array(
                'Day'   => '07',
                'Time'  => $this->_getSundaySortingCutOffTime(),
            )
        );

        return $CutOffTimes;
    }

    /**
     * Checks whether sunday sorting is allowed for this storeview.
     *
     * @param $country
     *
     * @return string
     */
    protected function _getSundaySortingAllowed($country)
    {
        $storeId = $this->getStoreId();

        $allowSundaySorting = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);
        if ($country == 'BE') {
            $allowSundaySorting  = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING_BE, $storeId);
        }

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
            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getModel('core/date');
            $tomorrow = strtotime('tomorrow', $dateModel->timestamp());
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
         * Add the street if available.
         */
        if (isset($data['street'])) {
            $location['Street'] = $data['street'];
        }

        /**
         * Add the housenumber if available.
         */
        if (isset($data['housenumber'])) {
            $location['HouseNr'] = $data['housenumber'];
        }

        /**
         * Add the city if available.
         */
        if (isset($data['city'])) {
            $location['City'] = $data['city'];
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

        /**
         * Add Options specifying which location timeframes should be returned
         */
        $location['Options'] = array('Daytime', 'Morning');

        /**
         * Add flag to identify if Sunday Sorting is allowed
         */
        $location['AllowSundaySorting'] = $this->_getSundaySortingAllowed($data['country']);

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

        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        if ($helper->canUsePakjeGemak()) {
            $deliveryOptions[] = self::PAKJEGEMAK_DELIVERY_OPTION;

            if ($helper->canUsePakjeGemakExpress()) {
                $deliveryOptions[] = self::PAKJEGEMAK_EXPRESS_DELIVERY_OPTION;
            }
        }

        if ($helper->canUsePakketAutomaat()) {
            $deliveryOptions[] = self::PAKKETAUTOMAAT_DELIVERY_OPTION;
        }

        return $deliveryOptions;
    }

    /**
     * Builds array of time frame options, to be sent in the GetTimeframes request.
     * These options determine which delivery timeframes should be requested.
     *
     * @param $country
     *
     * @return array
     */
    protected function _getDeliveryTimeframesOptionsArray($country)
    {
        $storeId = $this->getStoreId();

        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = $this->_getHelper('deliveryOptions');

        /**
         * In the case of a food delivery, only sameday and evening delivery timeframes should be shown.
         */
        if ($country == 'NL' && $helper->canUseFoodDelivery(true) && $helper->quoteIsFood()) {
            $options = array(
                self::SAMEDAY_DELIVERY_OPTION,
                self::EVENING_DELIVERY_OPTION,
            );

            return $options;
        }

        $options = array(self::DOMESTIC_DELIVERY_OPTION);

        if ($country == 'NL' && $helper->canUseSameDayDelivery()) {
            $options[] = self::SAMEDAY_DELIVERY_OPTION;
            $options[] = self::EVENING_DELIVERY_OPTION;
        }

        if ($country == 'NL' && $helper->canUseEveningTimeframes() && !$helper->canUseSameDayDelivery()) {
            $options[] = self::EVENING_DELIVERY_OPTION;
        }

        if ($country == 'NL' && $helper->canUseSundayDelivery()) {
            $options[] = self::SUNDAY_DELIVERY_OPTION;
        }

        return $options;
    }

    /**
     * Get the best fitting delivery option for the GetDeliveryDate request. In contract to the
     * _getDeliveryTimeframesOptionsArray method, this method will return the options in a different order. This is
     * important to prevent certain dates from being unavailable. The order used in this method is (depending on the
     * extension's config): sunday > daytime > evening.
     *
     * @param null   $shippingDuration
     * @param string $country
     *
     * @param string $for delivery or pickup
     *
     * @return array
     */
    protected function _getDeliveryDateOptionsArray($shippingDuration = null, $country = 'NL', $for = 'delivery')
    {
        $storeId = $this->getStoreId();

        $options = array();
        $helper = $this->_getHelper('deliveryOptions');
        $date = $helper->getDateTime('now');
        $dayOfWeek = $date->format('N');
        $sameDayDelivery = Mage::getStoreConfig($helper::XPATH_ENABLE_SAMEDAY_DELIVERY, $storeId);
        $sameDayDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_SAMEDAY_CUTOFF_TIME, $storeId);
        $regularDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_CUTOFF_TIME, $storeId);

        if ($shippingDuration === null) {
            $shippingDuration = Mage::getStoreConfig($helper::XPATH_SHIPPING_DURATION, $storeId);
        }

        /**
         * Sameday must be combined with evening, and can't be combined with other options.
         */
        if ($country == 'NL' && $sameDayDelivery && $shippingDuration == 0 && $for == 'delivery') {
            if (
                $date->getTimestamp() < $sameDayDeliveryCutoff->getTimestamp() ||
                (
                    $date->getTimestamp() > $regularDeliveryCutoff->getTimestamp() &&
                    $dayOfWeek != 5
                )
            ) {
                $options[] = self::SAMEDAY_DELIVERY_OPTION;
                $options[] = self::EVENING_DELIVERY_OPTION;

                return $options;
            } elseif ($helper->quoteIsFood() && $helper->getQuoteFoodType() == $helper::FOOD_TYPE_COOL_PRODUCTS) {
                $options[] = self::EVENING_DELIVERY_OPTION;

                return $options;
            }
        }

        $sundayCutoffGapOptions = $this->_getSundayCutoffGapOptions();
        if ($country == 'NL' && $sameDayDelivery && $dayOfWeek == 7 && $sundayCutoffGapOptions) {
            return $sundayCutoffGapOptions;
        }

        $sundayDelivery = Mage::getStoreConfig($helper::XPATH_ENABLE_SUNDAY_DELIVERY, $storeId);
        if ($country == 'NL' && $sundayDelivery) {
            $options[] = self::SUNDAY_DELIVERY_OPTION;
        }

        if ($country == 'BE' && $for == 'pickup') {
            $options[] = self::PICKUP_DELIVERY_OPTION;
        } else {
            $options[] = self::DOMESTIC_DELIVERY_OPTION;
        }

        if ($country == 'NL' && $helper->canUseEveningTimeframes()) {
            $options[] = self::EVENING_DELIVERY_OPTION;
        }

        return $options;
    }

    /**
     * @param DateTime $date
     * @param          $xpathCutoffTime
     * @param          $storeId
     *
     * @return DateTime
     */
    protected function _getCutoff(DateTime $date, $xpathCutoffTime, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }

        $time = Mage::getStoreConfig($xpathCutoffTime, $storeId);
        list($hour, $minute) = explode(':', $time);

        $cutoff = clone $date;
        $cutoff->setTime($hour, $minute);

        return $cutoff;
    }

    protected function _getSundayCutoffGapOptions()
    {
        $storeId = $this->getStoreId();

        $helper = $this->_getHelper('deliveryOptions');
        $date = $helper->getDateTime('now');
        $regularDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_CUTOFF_TIME, $storeId);
        $sundayDeliveryCutoff = $this->_getCutoff($date, $helper::XPATH_SUNDAY_CUTOFF_TIME, $storeId);

        if (
            $date->getTimestamp() < $sundayDeliveryCutoff->getTimestamp() ||
            $date->getTimestamp() > $regularDeliveryCutoff->getTimestamp()
        ) {
            return false;
        }

        $sundayDelivery = Mage::getStoreConfig($helper::XPATH_ENABLE_SUNDAY_DELIVERY, $storeId);
        if (!$sundayDelivery) {
            return false;
        }

        if (!$helper->canUseEveningTimeframes()) {
            return false;
        }

        return array(
            self::SUNDAY_DELIVERY_OPTION,
            self::SAMEDAY_DELIVERY_OPTION,
            self::EVENING_DELIVERY_OPTION,
        );
    }
}
