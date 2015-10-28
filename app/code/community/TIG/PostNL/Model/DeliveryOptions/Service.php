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
 * @method boolean                                  hasQuote()
 * @method TIG_PostNL_Model_DeliveryOptions_Service setQuote(Mage_Sales_Model_Quote $quote)
 * @method boolean                                  hasPostnlOrder()
 * @method TIG_PostNL_Model_DeliveryOptions_Service setPostnlOrder(TIG_PostNL_Model_Core_Order $postnlOrder)
 * @method boolean                                  hasShippingDuration()
 * @method TIG_PostNL_Model_DeliveryOptions_Service setShippingDuration(int $duration)
 * @method boolean                                  hasConfirmDate()
 * @method TIG_PostNL_Model_DeliveryOptions_Service setConfirmDate(string $date)
 */
class TIG_PostNL_Model_DeliveryOptions_Service extends Varien_Object
{
    /**
     * Newly added 'pakje_gemak' address type.
     */
    const ADDRESS_TYPE_PAKJEGEMAK = 'pakje_gemak';

    /**
     * Xpaths for shipping settings.
     */
    const XPATH_SHIPPING_DURATION = 'postnl/cif_labels_and_confirming/shipping_duration';
    const XPATH_SHIPPING_DAYS     = 'postnl/cif_labels_and_confirming/shipping_days';

    /**
     * Gets a PostNL Order. If none is set; load one.
     *
     * @return TIG_PostNL_Model_Core_Order
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            $postnlOrder = $this->_getData('postnl_order');

            return $postnlOrder;
        }

        $quote = $this->getQuote();

        $postnlOrder = Mage::getModel('postnl_core/order');
        $postnlOrder->load($quote->getId(), 'quote_id');

        $postnlOrder->setQuoteId($quote->getId());

        $this->setPostnlOrder($postnlOrder);
        return $postnlOrder;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            $quote = $this->_getData('quote');

            return $quote;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->setQuote($quote);
        return $quote;
    }

    /**
     * Gets the shipping duration for a specified delivery day.
     *
     * @param null $deliveryDay
     *
     * @return mixed
     */
    public function getShippingDuration($deliveryDay = null)
    {
        if ($this->hasShippingDuration()) {
            return $this->_getData('shipping_duration');
        }

        $shippingDuration = Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION);
        if ($deliveryDay == 1 && !Mage::helper('postnl/deliveryOptions')->canUseSundaySorting()) {
            $shippingDuration++;
        }

        $this->setShippingDuration($shippingDuration);
        return $shippingDuration;
    }

    /**
     * @param StdClass[] $timeframes
     *
     * @return StdClass[]|false
     */
    public function filterTimeframes($timeframes)
    {
        /**
         * If the time frames are not an array, something has gone wrong.
         */
        if (!is_array($timeframes)) {
            return false;
        }

        $helper = Mage::helper('postnl/deliveryOptions');

        return $helper->filterTimeFrames($timeframes, Mage::app()->getStore()->getId());
    }

    /**
     * Validate if saturday shipping is allowed for the specified shipping date when taking the earliest possible
     * shipping date into consideration.
     *
     * @param array    $shippingDays
     * @param DateTime $shippingDate
     * @param DateTime $earliestShippingDate
     *
     * @return bool
     */
    protected function _validateSaturdayShipping($shippingDays, DateTime $shippingDate, DateTime $earliestShippingDate)
    {
        $shippingDate->modify('last saturday ' . $shippingDate->format('H:i:s'));
        $shippingDay = 6;

        if (!in_array($shippingDay, $shippingDays)) {
            return false;
        }

        $cutOffTime = Mage::helper('postnl/deliveryOptions')->getCutOffTime(null, true, $shippingDate);
        $cutOffTime = explode(':', $cutOffTime);

        $shippingDate->setTime($cutOffTime[0], $cutOffTime[1], $cutOffTime[2]);

        if ($shippingDate < $earliestShippingDate) {
            return false;
        }

        return true;
    }

    /**
     * @param float|int $costs
     *
     * @throws InvalidArgumentException
     *
     * @return TIG_PostNL_Model_DeliveryOptions_Service
     */
    public function saveOptionCosts($costs)
    {
        if (!is_float($costs) && !is_int($costs)) {
            throw new InvalidArgumentException(
                Mage::helper('postnl')->__('Invalid parameter. Expected a float or an int.')
            );
        }

        $quote = $this->getQuote();

        $postnlOrder = $this->getPostnlOrder();
        $postnlOrder->setQuoteId($quote->getId())
                    ->setIsActive(true)
                    ->setShipmentCosts($costs)
                    ->save();

        return $this;
    }

    /**
     * Saves a mobile phone number for a parcel dispenser order.
     *
     * @param string $phoneNumber
     *
     * @return $this
     */
    public function saveMobilePhoneNumber($phoneNumber)
    {
        $postnlOrder = $this->getPostnlOrder();
        $postnlOrder->setMobilePhoneNumber($phoneNumber)
                    ->save();

        return $this;
    }

    /**
     * Save the specified delivery option.
     *
     * @param array $data
     *
     * @return $this
     */
    public function saveDeliveryOption($data)
    {
        /** @var TIG_PostNL_Helper_Date $helper */
        $helper = Mage::helper('postnl/date');

        $quote = $this->getQuote();

        $amsterdamTimeZone = new DateTimeZone('Europe/Amsterdam');
        $utcTimeZone = new DateTimeZone('UTC');

        $deliveryDate = DateTime::createFromFormat('d-m-Y', $data['date'], $amsterdamTimeZone);
        $deliveryDate->setTimezone($utcTimeZone);

        $deliveryDateClone = clone $deliveryDate;
        $confirmDate = $helper->getShippingDateFromDeliveryDate($deliveryDateClone, $quote->getStoreId());

        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = $this->getPostnlOrder();
        $postnlOrder->setQuoteId($quote->getId())
                    ->setOrderId(null)
                    ->setIsActive(true)
                    ->setIsPakjeGemak(false)
                    ->setIsPakketautomaat(false)
                    ->setProductCode(false)
                    ->setMobilePhoneNumber(false, true)
                    ->setType($data['type'])
                    ->setShipmentCosts($data['costs'])
                    ->setDeliveryDate($deliveryDate->format('Y-m-d H:i:s'))
                    ->setConfirmDate($confirmDate->format('Y-m-d H:i:s'))
                    ->setExpectedDeliveryTimeStart(false)
                    ->setExpectedDeliveryTimeEnd(false);

        if ($data['type'] == 'PA') {
            $postnlOrder->setIsPakketautomaat(true)
                        ->setProductCode(3553)
                        ->setMobilePhoneNumber($data['number']);
        } elseif ($data['type'] == 'PG' || $data['type'] == 'PGE') {
            $postnlOrder->setIsPakjeGemak(true);
        }

        /**
         * Set the expected delivery timeframe if available.
         */
        if (isset($data['from'])) {
            $from = DateTime::createFromFormat('H:i:s', $data['from'], $amsterdamTimeZone);
            $from->setTimezone($utcTimeZone);
            $postnlOrder->setExpectedDeliveryTimeStart($from->format('H:i:s'));
        }
        if (isset($data['to'])) {
            $to = DateTime::createFromFormat('H:i:s', $data['to'], $amsterdamTimeZone);
            $to->setTimezone($utcTimeZone);
            $postnlOrder->setExpectedDeliveryTimeEnd($to->format('H:i:s'));
        }

        /**
         * Remove any existing PakjeGemak addresses.
         *
         * @var Mage_Sales_Model_Quote_Address $quoteAddress
         */
        foreach ($quote->getAllAddresses() as $quoteAddress) {
            if ($quoteAddress->getAddressType() == self::ADDRESS_TYPE_PAKJEGEMAK) {
                $quoteAddress->isDeleted(true);
                $quote->removeAddress($quoteAddress->getId());
            }
        }

        /**
         * Add an optional PakjeGemak address.
         */
        if (isset($data['address'])) {
            $address = $data['address'];

            $street = array(
                $address['street'],
                $address['houseNumber']
            );

            if (array_key_exists('houseNumberExtension', $address)) {
                $street[] = $address['houseNumberExtension'];
            }

            $phoneNumber = '-';
            if (array_key_exists('telephone', $address)) {
                $phoneNumber = $address['telephone'];
            }

            $pakjeGemakAddress = Mage::getModel('sales/quote_address');
            $pakjeGemakAddress->setAddressType(self::ADDRESS_TYPE_PAKJEGEMAK)
                              ->setCity($address['city'])
                              ->setCountryId($address['countryCode'])
                              ->setPostcode($address['postcode'])
                              ->setCompany($address['name'])
                              ->setFirstname('-')
                              ->setLastname('-')
                              ->setTelephone($phoneNumber)
                              ->setStreet($street);

            $quote->addAddress($pakjeGemakAddress);
        }

        $quote->save();
        $postnlOrder->save();

        return $this;
    }
}