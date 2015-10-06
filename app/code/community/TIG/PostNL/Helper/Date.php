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
 *
 */
class TIG_PostNL_Helper_Date extends TIG_PostNL_Helper_DeliveryOptions
{
    /**
     * Constants to define the indeces for shipping/delivery day arrays.
     */
    const SUNDAY    = 0;
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;

    /**
     * @var array
     */
    protected $_orderDays = array(
        self::SUNDAY,
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
        self::SATURDAY
    );

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
     * @param bool $shippingDuration
     *
     * @return array
     */
    public function getValidDeliveryDaysArray($shippingDuration = false)
    {
        $sundayDelivery          = Mage::getStoreConfig('postnl/delivery_options/enable_sunday_delivery');
        $sundaySorting           = Mage::getStoreConfig('postnl/cif_labels_and_confirming/allow_sunday_sorting');
        $shippingDays            = Mage::getStoreConfig('postnl/cif_labels_and_confirming/shipping_days');
        $defaultShippingDuration = Mage::getStoreConfig('postnl/cif_labels_and_confirming/shipping_duration');

        if ($defaultShippingDuration > $shippingDuration) {
            $shippingDuration = $defaultShippingDuration;
        }

        $shippingDays = explode(',', $shippingDays);
        /**
         * Here we add a 0 to the shipping day array if sunday is available. This is needed to use this array with our
         * 0-based index validshippingdays array.
         */

        foreach($shippingDays as $shippingDay) {
            if (in_array($shippingDay, $shippingDays)) {
                $dayToEnable = ($shippingDay + 1) % 7;
                $this->_validDeliveryDays[$dayToEnable] = 1;
            }
        }

        if (!$sundayDelivery) {
            $this->_validDeliveryDays[self::SUNDAY] = 0;
        }

        if (!$sundaySorting) {
            $this->_validDeliveryDays[self::MONDAY] = 0;
        }

        return $this->_validDeliveryDays;
    }

    /**
     * Gets the Shipping duration Array. If this is not available, build it and then return it.
     *
     * @param int|bool $shippingDuration
     *
     * @return Array
     */
    public function getShippingDurationArray($shippingDuration = false)
    {
        $shippingDurationList = Mage::registry('shippingDurationList');
        if (!isset($shippingDurationList)) {
            $shippingDurationList = $this->buildShippingDurationList($shippingDuration);
        }
        $shippingDurationArray = explode(',', $shippingDurationList);

        return $shippingDurationArray;
    }

    /**
     * Builds an array show how many days should be added to the order date, to calculate the shipping date.
     * This depends on the shipping duration, if sunday delivery is allowed and if sunday sorting is allowed.
     *
     * @param int|bool $shippingDuration
     *
     * @return Array
     */
    public function buildShippingDurationList($shippingDuration = false)
    {
        $sundayDelivery   = Mage::getStoreConfig('postnl/delivery_options/enable_sunday_delivery');
        $sundaySorting    = Mage::getStoreConfig('postnl/cif_labels_and_confirming/allow_sunday_sorting');
        $shippingDays     = Mage::getStoreConfig('postnl/cif_labels_and_confirming/shipping_days');

        /**
         * If no shipping duration is given, retrieve it from the config.
         */
        if (!$shippingDuration) {
            $shippingDuration = Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION);
        }

        /**
         * Initialize empty array
         */
        $shippingDurationArray = array();

        /**
         * Walk through all order days, calculating the duration until the shipping date per order day.
         */
        foreach ($this->_orderDays as $orderDay) {

            $correctedShippingDuration = $shippingDuration - 1;

            /**
             * Get the next day until we find a valid shipping day.
             */
            while (!$this->isValidDay(
                $orderDay,
                $correctedShippingDuration,
                $sundayDelivery,
                $sundaySorting,
                $shippingDays
            )
            ) {
                $correctedShippingDuration++;
            }

            $shippingDurationArray[$orderDay] = $correctedShippingDuration;
        }

        $shippingDurationList = implode(',', $shippingDurationArray);
        Mage::register('shippingDurationList', $shippingDurationList);

        return $shippingDurationList;
    }

    /**
     * Checks if the current day is a valid day for delivery
     *
     * @param $weekDay
     * @param $correctedShippingDuration
     * @param $sundayDelivery
     * @param $sundaySorting
     * @param $shippingDays
     *
     * @return bool
     */
    public function isValidDay($weekDay, $correctedShippingDuration, $sundayDelivery, $sundaySorting, $shippingDays)
    {
        /**
         * If sunday delivery is not activated, orders which are supposed to be shipped on saturday should
         * be shipped a day later.
         */
        if (!$sundayDelivery) {
            if (($weekDay + $correctedShippingDuration) % 7 == 6) {
                return false;
            }
        }

        /**
         * If sunday sorting is not activated, orders which are supposed to be shipped on sunday
         * should be shipped a day later.
         */
        if (!$sundaySorting) {
            if (($weekDay + $correctedShippingDuration) % 7 == 0) {
                return false;
            }
        }

        /**
         * If the date is not in the list of valid shipping dates as configured by the user, it is not a valid shipping
         * date.
         */
        if (!in_array(($weekDay + $correctedShippingDuration) % 7, explode(',',$shippingDays))) {
            return false;
        }

        return true;
    }

    /**
     * Builds an array show how many days should be added to the order date, to calculate the delivery date.
     * Simply put, this is the value of shippingDurationArray + 1.
     *
     * @return Array
     */
    public function buildDeliveryDurationList()
    {
        $deliveryDurationArray = $this->getShippingDurationArray();

        foreach ($deliveryDurationArray as $deliveryDayKey => $deliveryDay) {
            $deliveryDurationArray[$deliveryDayKey] = $deliveryDay + 1;
        }

        $deliveryDurationList = implode(',', $deliveryDurationArray);
        Mage::register('deliveryDurationList', $deliveryDurationList);

        return $deliveryDurationList;
    }

    /**
     * Gets the Shipping duration Array. If this is not available, build it and then return it.
     *
     * @return Array
     */
    public function getDeliveryDurationArray()
    {
        $deliveryDurationList = Mage::registry('deliveryDurationList');
        if (!isset($deliveryDurationList)) {
            $deliveryDurationList = $this->buildDeliveryDurationList();
        }

        $deliveryDurationArray = explode(',', $deliveryDurationList);

        return $deliveryDurationArray;
    }

    /**
     * Calculates the date an order needs te be shipped, based on the order date
     *
     * @param DateTime $orderDate
     * @param int      $storeId
     * @param int      $shippingDuration
     *
     * @return DateTime
     */
    public function getTomShippingDate($orderDate, $storeId, $shippingDuration)
    {
        $orderDateObject = $this->getUtcDateTime($orderDate, $storeId);

        /**
         * If the order is past the configured cut off time, we should add 1 day to the calculation
         */
        if ($this->isPastCutoff($orderDateObject)) {
            $orderDateObject->add(new DateInterval('P1D'));
        }

        /**
         * Get the amount of days until the order needs to be shipped, which is calculated per day beforehand
         * $weekday % 7 is used to get the 0 pointer for our 0-based index array for sunday situations.
         */
        $weekday = $orderDateObject->format('N');
        $shippingDurationArray = $this->getShippingDurationArray($shippingDuration);
        $daysUntilShip = $shippingDurationArray[($weekday % 7)];

        $shippingDate = $orderDateObject->add(new DateInterval('P'.$daysUntilShip.'D'));
        return $shippingDate;
    }

    /**
     * Returns an UTC DateTime object built from the orderdate.
     *
     * @param DateTime $orderDate
     * @param $storeId
     *
     * @return DateTime
     */
    public function getUtcDateTime($date, $storeId)
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
        if ($date->getTimeZone()->getName() != 'UTC') {
            $date->setTimeZone(new DateTimeZone('UTC'));
        }

        return $date;
    }

    /**
     * Calculates the date an order should be delivered, based on the order date
     *
     * @param DateTime $orderDate
     * @param int      $storeId
     *
     * @return DateTime
     */
    public function getTomDeliveryDate($orderDate, $storeId)
    {
        $orderDateObject = new DateTime($orderDate);

        if ($this->isPastCutOff($orderDateObject)) {
            $orderDateObject->add(new DateInterval('P1D'));
        }

        /**
         * Get the amount of days until the order should be delivered, which is calculated per day beforehand
         * $weekday % 7 is used to get the 0 pointer for our 0-based index array for sunday situations.
         */
        $weekday = $orderDateObject->format('N');
        $deliveryDurationArray = $this->getDeliveryDurationArray();
        $daysUntilDelivery = $deliveryDurationArray[($weekday % 7)];

        $deliveryDate = $orderDateObject->add(new DateInterval('P'.$daysUntilDelivery.'D'));
        return $deliveryDate;
    }


    /**
     * Calculates if the orderDate is past the configured cutoff time.
     *
     * @param DateTime $orderDateObject
     *
     * @return boolean
     */
    public function isPastCutOff($orderDateObject)
    {
        $weekDay = $orderDateObject->format('N');

        $forSunday = false;
        if ($weekDay == 7) {
            $forSunday = true;
        }

        $cutoff = $this->getCutOff(0, $forSunday);

        $orderTime = $orderDateObject->format("H:i:s");

        return ($cutoff < $orderTime);
    }

    /**
     * Gets the cut off time for the given store. When $forSunday is set to true,
     * will return sunday cut off time instead.
     *
     * @param      $storeId
     * @param bool $forSunday
     *
     * @return bool
     */
    public function getCutOff($storeId = 0, $forSunday = false)
    {
        $xpathToUse = self::XPATH_CUTOFF_TIME;
        if ($forSunday) {
            $xpathToUse = self::XPATH_SUNDAY_CUTOFF_TIME;
        }
        $cutoff = Mage::getStoreConfig($xpathToUse, $storeId);
        $correctedCutOff = $this->getUtcDateTime($cutoff, 0)->format('H:i:s');
        return $correctedCutOff;
    }


}