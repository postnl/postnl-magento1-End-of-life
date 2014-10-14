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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
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
     * Calculate the confirm date for a specified delivery date.
     *
     * @param string $deliveryDate
     *
     * @return DateTime
     */
    public function getConfirmDate($deliveryDate)
    {
        if ($this->hasConfirmDate()) {
            return $this->_getData('confirm_date');
        }

        $deliveryDate = new DateTime($deliveryDate);

        $confirmDate = $deliveryDate->sub(new DateInterval("P1D"));
        $confirmDate = $confirmDate->format('Y-m-d');

        $confirmDate = Mage::helper('postnl/deliveryOptions')->getValidConfirmDate($confirmDate);

        $this->setConfirmDate($confirmDate);
        return $confirmDate;
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

        /**
         * Get the configured shipping days.
         */
        $shippingDays = Mage::getStoreConfig(self::XPATH_SHIPPING_DAYS, Mage::app()->getStore()->getId());
        $shippingDays = explode(',', $shippingDays);

        foreach ($timeframes as $key => $timeframe) {
            /**
             * Get the date of the time frame and calculate the shipping day. The shipping day will be the day before
             * the delivery date, but may not be a sunday.
             */
            $timeframeDate = new DateTime($timeframe->Date);
            $deliveryDay   = (int) $timeframeDate->format('N');
            $shippingDay   = (int) $timeframeDate->sub(new DateInterval('P1D'))->format('N');

            if ($shippingDay < 1 || $shippingDay > 6) {
                $shippingDay = 6;
            }

            /**
             * If the shipping day is not allowed, remove the time frame from the array.
             *
             * For tuesday delivery either saturday or monday needs to be available.
             */
            if ($deliveryDay === 2 && !in_array($shippingDay, $shippingDays)) {
                $shippingDay = 6;
            }

            if (!in_array($shippingDay, $shippingDays)) {
                unset($timeframes[$key]);
            }
        }

        /**
         * Only return the values, as otherwise the array will be JSON encoded as an object.
         */
        return array_values($timeframes);
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
     * @param $data
     *
     * @return $this
     */
    public function saveDeliveryOption($data)
    {
        $quote = $this->getQuote();

        $confirmDate = $this->getConfirmDate($data['date'])->getTimestamp();

        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = $this->getPostnlOrder();
        $postnlOrder->setQuoteId($quote->getId())
                    ->setIsActive(true)
                    ->setIsPakjeGemak(false)
                    ->setIsPakketautomaat(false)
                    ->setProductCode(false)
                    ->setMobilePhoneNumber(false, true)
                    ->setType($data['type'])
                    ->setShipmentCosts($data['costs'])
                    ->setDeliveryDate($data['date'])
                    ->setConfirmDate($confirmDate);

        if ($data['type'] == 'PA') {
            $postnlOrder->setIsPakketautomaat(true)
                        ->setProductCode(3553)
                        ->setMobilePhoneNumber($data['number']);
        } elseif ($data['type'] == 'PG' || $data['type'] == 'PGE') {
            $postnlOrder->setIsPakjeGemak(true);
        }

        /**
         * Remove any existing PakjeGemak addresses.
         *
         * @var Mage_Sales_Model_Quote_Address $quoteAddress
         */
        foreach ($quote->getAllAddresses() as $quoteAddress) {
            if ($quoteAddress->getAddressType() == self::ADDRESS_TYPE_PAKJEGEMAK) {
                $quoteAddress->isDeleted(true);
            }
        }

        /**
         * Add an optional PakjeGemak address.
         */
        if (array_key_exists('address', $data)) {
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

            $quote->addAddress($pakjeGemakAddress)
                  ->save();
        }

        $postnlOrder->save();

        return $this;
    }
}