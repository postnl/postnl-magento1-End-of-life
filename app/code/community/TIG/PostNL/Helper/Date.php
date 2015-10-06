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

    protected $_validDeliveryDays = array (
        self::SUNDAY      => 0,
        self::MONDAY      => 0,
        self::TUESDAY     => 0,
        self::WEDNESDAY   => 0,
        self::THURSDAY    => 0,
        self::FRIDAY      => 0,
        self::SATURDAY    => 0,
    );

    protected $_postnlDeliveryDelay = 1;

    /**
     *
     *
     * @return array
     */
    public function getValidDeliveryDaysArray()
    {
        $sundayDelivery          = Mage::getStoreConfig(self::XPATH_ENABLE_SUNDAY_DELIVERY);
        $sundaySorting           = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING);
        $shippingDays            = Mage::getStoreConfig(self::XPATH_SHIPPING_DAYS);

        $shippingDays = explode(',', $shippingDays);

        foreach($shippingDays as $shippingDay) {
            if (in_array($shippingDay, $shippingDays)) {
                $dayToEnable = ($shippingDay + $this->_postnlDeliveryDelay) % 7;
                $this->_validDeliveryDays[$dayToEnable] = 1;
            }
        }

        if (!$sundayDelivery) {
            $this->_validDeliveryDays[self::SUNDAY] = 0;
        }

        if ($sundaySorting) {
            if (in_array(self::SATURDAY, $shippingDays)) {
                $this->_validDeliveryDays[self::MONDAY] = 1;
            }
        } else {
            $this->_validDeliveryDays[self::MONDAY] = 0;
        }

        return $this->_validDeliveryDays;
    }

    /**
     * Checks if the current day is a valid day for delivery
     *
     * @param int    $weekDay
     * @param Array  $validDeliveryDays
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
     * @param mixed  $date
     * @param int    $storeId
     *
     * @return DateTime
     */
    public function getDeliveryDate($date, $storeId)
    {
        $orderDateObject = $this->getUtcDateTime($date, $storeId);

        /**
         * If the time is past the cutoff time of the store, we need to treat this date as the next day
         */
        if ($this->isPastCutOff($orderDateObject, $storeId)) {
            $orderDateObject->add(new DateInterval('P1D'));
        }

        /**
         * Get the current weekday, configured shipping duration, and array of valid delivery days.
         */
        $weekday = $orderDateObject->format('N');
        $shippingDuration = $this->getQuoteShippingDuration();
        $validDeliveryDays = $this->getValidDeliveryDaysArray();

        /**
         * Check if the calculated day is a valid shipping day. If this is not the case, check the next weekday, and
         * add 1 day to the total shipping duration.
         */
        $checkValidDay = ((int) $weekday + $shippingDuration) % 7;
        while (!$this->isValidDay($checkValidDay, $validDeliveryDays)) {
            $checkValidDay = ($checkValidDay + 1) % 7;
            $shippingDuration++;
        }

        /**
         * Add the calculated total shipping duration to the order date, to get the Delivery Date.
         */
        $deliveryDate = $orderDateObject->add(new DateInterval('P'.$shippingDuration.'D'));
        return $deliveryDate;
    }

    /**
     * Calculates the date an order needs te be shipped, based on the order date
     *
     * @param DateTime $date
     * @param int      $storeId
     *
     * @return DateTime
     */
    public function getShippingDate($date, $storeId)
    {
        $dateObject = $this->getTomDeliveryDate($date, $storeId);
        $sundaySorting = Mage::getStoreConfig(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);

        if($dateObject->format('N') == self::MONDAY) {
            $validDeliveryDays = $this->getValidDeliveryDaysArray();
            if($sundaySorting && $validDeliveryDays[self::SUNDAY] == 0) {
                $dateObject->sub(new DateInterval("P1D"));
            }
        }

        $dateObject->sub(new DateInterval("P{$this->_postnlDeliveryDelay}D"));
        return $dateObject;
    }

    /**
     * Returns an UTC DateTime object built from the orderdate.
     *
     * @param mixed $date
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
     * Calculates if the orderDate is past the configured cutoff time.
     *
     * @param DateTime  $orderDateObject
     * @param int       $storeId
     *
     * @return boolean
     */
    public function isPastCutOff($orderDateObject, $storeId)
    {
        $weekDay = $orderDateObject->format('N');

        $forSunday = false;
        if ($weekDay == 7) {
            $forSunday = true;
        }

        $cutoff = $this->getCutOff($storeId, $forSunday);
        $orderTime = $orderDateObject->format("H:i:s");

        return ($cutoff < $orderTime);
    }

    /**
     * Gets the cut off time for the given store. When $forSunday is set to true,
     * will return sunday cut off time instead.
     *
     * @param int   $storeId
     * @param bool  $forSunday
     *
     * @return DateTime
     */
    public function getCutOff($storeId = 0, $forSunday = false)
    {
        $xpathToUse = self::XPATH_CUTOFF_TIME;
        if ($forSunday) {
            $xpathToUse = self::XPATH_SUNDAY_CUTOFF_TIME;
        }
        $cutoff = Mage::getStoreConfig($xpathToUse, $storeId);
        $correctedCutOff = $this->getUtcDateTime($cutoff, $storeId)->format('H:i:s');
        return $correctedCutOff;
    }


}