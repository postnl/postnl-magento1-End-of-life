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
     * Xpath for shipping duration setting.
     */
    const XPATH_SHIPPING_DURATION = 'postnl/delivery_options/shipping_duration';

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
     * @param $deliveryDate
     *
     * @return string
     */
    public function getConfirmDate($deliveryDate)
    {
        if ($this->hasConfirmDate()) {
            return $this->_getData('confirm_date');
        }

        $deliveryDate = strtotime($deliveryDate);
        $deliveryDay = date('N');

        $shippingDuration = $this->getShippingDuration($deliveryDay);
        $confirmDate = strtotime("-{$shippingDuration} days", $deliveryDate);

        $confirmDate = date('Y-m-d', $confirmDate);

        $this->setConfirmDate($confirmDate);
        return $confirmDate;
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

        $confirmDate = $this->getConfirmDate($data['date']);

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