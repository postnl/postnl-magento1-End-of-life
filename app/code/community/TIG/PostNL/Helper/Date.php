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
 */
class TIG_PostNL_Helper_Date extends TIG_PostNL_Helper_DeliveryOptions
{
    /**
     * Xpath to the 'valid_shipping_duration_days' setting.
     */
    const XPATH_VALID_SHIPPING_DURATION_DAYS = 'postnl/grid/valid_shipping_duration_days';

    /**
     * Constants to define the indices for shipping/delivery day arrays.
     */
    const SUNDAY             = 0;
    const MONDAY             = 1;
    const TUESDAY            = 2;
    const WEDNESDAY          = 3;
    const THURSDAY           = 4;
    const FRIDAY             = 5;
    const SATURDAY           = 6;
    const ALTERNATIVE_SUNDAY = 7; // In certain instances sunday is considered the 7th day, rather than the 0th.

    /**
     * Defines which delivery days are available, used for further calculating shipping and delivery dates.
     *
     * @var array
     */
    protected $_validDeliveryDays = array (
        self::SUNDAY      => 0,
        self::MONDAY      => 0,
        self::TUESDAY     => 0,
        self::WEDNESDAY   => 0,
        self::THURSDAY    => 0,
        self::FRIDAY      => 0,
        self::SATURDAY    => 0,
    );

    /**
     * This can possible be changed to 2. Required for sending from Belgium and defines the delay between
     * offering a parcel to PostNL and PostNL delivering the parcel.
     *
     * @var int
     */
    protected $_postnlDeliveryDelay = 1;

    /**
     * @var bool
     */
    protected $_useFoodCutOffTime = false;

    /**
     * @param int $postnlDeliveryDelay
     *
     * @return $this
     */
    public function setPostnlDeliveryDelay($postnlDeliveryDelay)
    {
        $this->_postnlDeliveryDelay = (int) $postnlDeliveryDelay;

        return $this;
    }

    /**
     * @return boolean
     */
    public function useFoodCutOffTime()
    {
        return $this->_useFoodCutOffTime;
    }

    /**
     * @param boolean $useFoodCutOffTime
     *
     * @return $this
     */
    public function setUseFoodCutOffTime($useFoodCutOffTime)
    {
        $this->_useFoodCutOffTime = $useFoodCutOffTime;

        return $this;
    }

    /**
     * Build an array of valid delivery dates. Used for calculating delivery and shipping dates.
     *
     * @param $storeId
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    public function getValidDeliveryDaysArray($storeId = 0)
    {
        /**
         * Check if the array is available in the cache.
         */
        $cache = $this->getCache();
        if ($cache && $cache->hasValidDeliveryDaysArray()) {
            return $cache->getValidDeliveryDaysArray();
        }

        /**
         * Retrieves required config values.
         */
        $sundayDelivery = Mage::getStoreConfig(self::XPATH_ENABLE_SUNDAY_DELIVERY, $storeId);
        $sundaySorting  = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);
        $shippingDays   = Mage::getStoreConfig(self::XPATH_SHIPPING_DAYS, $storeId);
        $shippingDays   = explode(',', $shippingDays);

        if ($this->isBe($this->getQuote())) {
            $sundaySorting  = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING_BE, $storeId);
        }

        /**
         * Sunday delivery and sunday sorting are not available for letter box parcels.
         */
        if ($this->quoteIsBuspakje(null)) {
            $sundayDelivery = false;
            $sundaySorting  = false;
        }

        /**
         * If a day is configured as shipping day, this day + the PostNL shipping delay is available as delivery day.
         */
        foreach ($shippingDays as $shippingDay) {
            $dayToEnable = ($shippingDay + $this->_postnlDeliveryDelay) % 7;
            $this->_validDeliveryDays[$dayToEnable] = 1;
        }

        /**
         * If sunday delivery is not active, sunday can never be an available delivery date.
         */
        if (!$sundayDelivery) {
            $this->_validDeliveryDays[self::SUNDAY] = 0;

            /**
             * If sunday sorting is active, but sundaydelivery isn't, and saturday is a valid shipping day, monday is a
             * valid delivery day.
             */
            if ($sundaySorting
                && in_array(self::SATURDAY, $shippingDays)
            ) {
                $this->_validDeliveryDays[self::MONDAY] = 1;
            } elseif (!$sundaySorting
                && in_array(self::SATURDAY, $shippingDays)
            ) {
                /**
                 * If sunday sorting is not active, and sunday delivery isn't either, tuesday should be a valid delivery
                 * day and monday shouldn't.
                 */
                $this->_validDeliveryDays[self::MONDAY] = 0;
                $this->_validDeliveryDays[self::TUESDAY] = 1;
            }
        }

        if (!$sundaySorting) {
            $this->_validDeliveryDays[self::MONDAY] = 0;
        }

        /**
         * If no valid delivery day is found, throw an Exception
         */
        if (!in_array(1, $this->_validDeliveryDays)) {
            throw new TIG_PostNL_Exception(
                $this->__('No valid delivery day found.'),
                'POSTNL-0231'
            );
        }

        /**
         * Save this array in the cache
         */
        if ($cache) {
            $cache->setValidDeliveryDaysArray($this->_validDeliveryDays)
                  ->saveCache();
        }

        return $this->_validDeliveryDays;
    }

    /**
     * Checks if the current day is a valid day for delivery, using the validDeliveryDay array built beforehand.
     *
     * @param int    $weekDay
     * @param array  $validDeliveryDays
     *
     * @return       bool
     */
    public function isValidDay($weekDay, $validDeliveryDays)
    {
        return $validDeliveryDays[$weekDay];
    }

    /**
     * Calculates the date an order should be delivered, based on the order date
     *
     * @param mixed $date
     * @param int   $storeId
     * @param bool  $allowSameDay
     *
     * @return DateTime
     */
    public function getDeliveryDate($date, $storeId, $allowSameDay = true)
    {
        $orderDateObject = $this->getUtcDateTime($date, $storeId);

        /**
         * If the time is past the cutoff time of the store, we need to treat this date as the next day
         */
        if ($this->isPastCutOff($orderDateObject, $storeId)) {
            $orderDateObject->add(new DateInterval('P1D'));
        }

        /**
         * Get the current weekday and configured shipping duration.
         */
        $weekday = $orderDateObject->format('N');
        $shippingDuration = $this->getQuoteShippingDuration();

        /**
         * The shipping duration may only be less than 1 when same day is allowed.
         */
        if ($shippingDuration < 1 && !$allowSameDay) {
            $shippingDuration = 1;
        }

        /**
         * Get a possible addition of day(s), if the found deliveryDay is not a valid deliveryday.
         */
        $checkValidDay = ((int) $weekday + $shippingDuration) % 7;
        $correction = $this->getDeliveryDateCorrection($checkValidDay);
        $shippingDuration = $shippingDuration + $correction;

        /**
         * Add the calculated total shipping duration to the order date, to get the Delivery Date.
         */
        $deliveryDate = $this->getShippingDateCorrection($shippingDuration, $orderDateObject, $storeId);
        return $deliveryDate;
    }

    /**
     * Calculates the date an order needs te be shipped, based on the order date.
     *
     * @param mixed    $date
     * @param int      $storeId
     *
     * @return DateTime
     */
    public function getShippingDate($date, $storeId)
    {
        $dateObject = $this->getDeliveryDate($date, $storeId);
        $sundaySorting = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);

        if ($this->isBe($this->getQuote())) {
            $sundaySorting  = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING_BE, $storeId);
        }

        /**
         * If the delivery day is monday, the shipment possibly needs to be sent on saturday, if sundaydelivery is not
         * allowed, and sundaysorting is active.
         */
        if($dateObject->format('N') == self::MONDAY) {
            $validDeliveryDays = $this->getValidDeliveryDaysArray();
            if($sundaySorting && $validDeliveryDays[self::SUNDAY] == 0) {
                $dateObject->sub(new DateInterval("P1D"));
            }
        }

        /**
         * Substract the delivery delay from PostNL.
         */
        $dateObject->sub(new DateInterval("P{$this->_postnlDeliveryDelay}D"));
        return $dateObject;
    }

    /**
     * Gets the shipping date calculated from the supplied deliveryDate.
     *
     * @param      $deliveryDate
     * @param      $storeId
     *
     * @param bool $isPGBE
     *
     * @return DateTime
     * @note In the future this could be refactored to use this:
     *       https://developer.postnl.nl/apis/deliverydate-webservice/documentation#toc-10
     */
    public function getShippingDateFromDeliveryDate($deliveryDate, $storeId, $isPGBE = false)
    {
        /**
         * Get required config values and date object.
         */
        $sundaySorting = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);

        if ($this->isBe($this->getQuote())) {
            $sundaySorting  = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING_BE, $storeId);
        }

        $shippingDays  = Mage::getStoreConfig(self::XPATH_SHIPPING_DAYS, $storeId);
        $shippingDaysArray = explode(',', $shippingDays);
        $dateObject = $this->getUtcDateTime($deliveryDate, $storeId, false);

        /**
         * If the delivery day is monday, the shipment possibly needs to be sent on saturday, if sundaydelivery is not
         * allowed, and sundaysorting is active.
         */
        if($dateObject->format('N') == self::MONDAY) {
            if($sundaySorting && !in_array(self::ALTERNATIVE_SUNDAY, $shippingDaysArray)) {
                $dateObject->sub(new DateInterval("P1D"));
            }
        }

        $deliveryDelay = $this->_postnlDeliveryDelay;

        /**
         * PG BE defaults to a delivery delay of 2 days, no exceptions.
         */
        if ($isPGBE) {
            $deliveryDelay = 2;
        }

        /**
         * Substract the delivery delay from the delivery date to get to the shipping date.
         */
        $dateObject->sub(new DateInterval("P{$deliveryDelay}D"));

        /**
         * If the projected shipping date is not a valid shipping date, substract 1 day and check again.
         */
        while (!in_array($dateObject->format('N'), $shippingDaysArray)) {
            $dateObject->sub(new DateInterval("P1D"));
        }

        return $dateObject;
    }

    /**
     * Returns an UTC DateTime object built from the orderdate.
     *
     * @param mixed $date
     * @param       $storeId
     * @param bool  $convertTimeZone
     *
     * @return DateTime
     */
    public function getUtcDateTime($date, $storeId, $convertTimeZone = true)
    {
        /**
         * If the orderDate is not an object. Make an object using the current store timezone
         */
        if (!is_object($date)) {
            $timeZone = $this->getStoreTimeZone($storeId);
            $date = new DateTime($date, new DateTimeZone($timeZone));
        }

        /**
         * If the orderDate object is not in UTC, change the timezone to UTC.
         */
        if ($convertTimeZone) {
            if ($date->getTimeZone()->getName() != 'UTC') {
                $date->setTimeZone(new DateTimeZone('UTC'));
            }
        }

        return $date;
    }

    /**
     * Calculates if the orderDate is past the configured cutoff time.
     *
     * @param DateTime    $orderDateObject
     * @param int         $storeId
     * @param null|string $type
     *
     * @return bool
     */
    public function isPastCutOff($orderDateObject, $storeId, $type = null)
    {
        if (!$type) {
            if ($this->useFoodCutOffTime()) {
                $type = 'food';
            } else {
                $weekDay = $orderDateObject->format('w');

                /**
                 * If the weekday == 7, we need to check for sunday cutoff time instead.
                 */
                $type = 'weekday';
                if ($weekDay == self::SUNDAY) {
                    $type = 'sunday';
                }
            }
        }

        /**
         * Check if the order time is before the cutoff time, disregarding dates.
         */
        $cutoff = $this->getCutOff($storeId, $type);
        $orderTime = $orderDateObject->format("H:i:s");

        return ($cutoff < $orderTime);
    }

    /**
     * Gets the cut off time for the given store. When $forSunday is set to true,
     * will return sunday cut off time instead.
     *
     * @param int    $storeId
     * @param string $type
     *
     * @return DateTime
     */
    public function getCutOff($storeId = 0, $type = 'weekday')
    {
        switch ($type) {
            case 'sunday':
                $xpathToUse = self::XPATH_SUNDAY_CUTOFF_TIME;
                break;
            case 'sameday':
            case 'food':
                $xpathToUse = self::XPATH_SAMEDAY_CUTOFF_TIME;
                break;
            case 'weekday':
            default:
                $xpathToUse = self::XPATH_CUTOFF_TIME;
                break;
        }

        $cutoff = Mage::getStoreConfig($xpathToUse, $storeId);

        $cutoff = new DateTime($cutoff, new DateTimeZone("Europe/Amsterdam"));
        $correctedCutOff = $this->getUtcDateTime($cutoff, $storeId)->format('H:i:s');
        return $correctedCutOff;
    }

    /**
     * Checks if the found delivery day is valid. If this is not the case, add a day to the delivery day correction,
     * point to the next found day, and repeat this.
     *
     * @param DateTime|int $checkValidDay
     *
     * @return int
     */
    public function getDeliveryDateCorrection($checkValidDay)
    {
        /**
         * If this is not a DateTime object, nor a string, this will get stuck.
         */
        if (!is_object($checkValidDay) && !is_string($checkValidDay) && !is_int($checkValidDay)) {
            return 0;
        }

        if (is_object($checkValidDay)) {
            /** @var DateTime $checkValidDay */
            $checkValidDay = $checkValidDay->format('N');
        }

        /** @var int $checkValidDay */
        $checkValidDay = (int) $checkValidDay;

        /**
         * If the checkValidDay is not found in the valid delivery day array, we will not find what we are looking for.
         */
        $validDeliveryDayArray = $this->getValidDeliveryDaysArray();
        if (!array_key_exists($checkValidDay, $validDeliveryDayArray)) {
            return 0;
        }

        $deliveryDurationCorrection = 0;
        while (!$this->isValidDay($checkValidDay, $validDeliveryDayArray)) {
            $checkValidDay = ($checkValidDay + 1) % 7;
            $deliveryDurationCorrection++;

            /**
             * If we get stuck in an infinite loop, just return 0.
             */
            if ($deliveryDurationCorrection > 8) {
                return 0;
            }
        }

        return $deliveryDurationCorrection;
    }

    /**
     * Correct the delivery dat with the specified shipping duration, taking into account the configured available
     * shipping duration days.
     *
     * @param int      $shippingDuration
     * @param DateTime $deliveryDate
     * @param int      $storeId
     *
     * @return DateTime
     */
    public function getShippingDateCorrection($shippingDuration, DateTime $deliveryDate, $storeId)
    {
        /**
         * Get and array of valid days for the shipping duration calculation.
         */
        $validDurationDays = explode(
            ',',
            Mage::getStoreConfig(self::XPATH_VALID_SHIPPING_DURATION_DAYS, $storeId)
        );

        /**
         * In case no such array is available, use all days of the week.
         */
        if (empty($validDurationDays)) {
            $validDurationDays = array(
                self::MONDAY,
                self::TUESDAY,
                self::WEDNESDAY,
                self::THURSDAY,
                self::FRIDAY,
                self::SATURDAY,
                self::ALTERNATIVE_SUNDAY,
            );
        }

        /**
         * Calculate the delivery date.
         */
        $i = 0;
        while ($i < $shippingDuration) {
            if (in_array($deliveryDate->format('N'), $validDurationDays)) {
                $i++;
            }
            $deliveryDate->add(new DateInterval('P1D'));
        }

        return $deliveryDate;
    }
}
