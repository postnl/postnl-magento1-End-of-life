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
 * @todo Cache the available delivery options in the checkout session. That way we only recalculate them if the quote
 *       has changed.
 */
class TIG_PostNL_Helper_DeliveryOptions extends TIG_PostNL_Helper_Checkout
{
    /**
     * Xpath to delivery options enabled config setting.
     */
    const XPATH_DELIVERY_OPTIONS_ACTIVE = 'postnl/delivery_options/delivery_options_active';

    /**
     * Xpaths to various possible delivery option settings.
     */
    const XPATH_ENABLE_PAKJEGEMAK               = 'postnl/delivery_options/enable_pakjegemak';
    const XPATH_ENABLE_PAKJEGEMAK_EXPRESS       = 'postnl/delivery_options/enable_pakjegemak_express';
    const XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS = 'postnl/delivery_options/enable_pakketautomaat_locations';
    const XPATH_ENABLE_DELIVERY_DAYS            = 'postnl/delivery_options/enable_delivery_days';
    const XPATH_ENABLE_TIMEFRAMES               = 'postnl/delivery_options/enable_timeframes';
    const XPATH_ENABLE_EVENING_TIMEFRAMES       = 'postnl/delivery_options/enable_evening_timeframes';

    /**
     * Xpaths to various business rule settings.
     */
    const XPATH_SHOW_OPTIONS_FOR_BACKORDERS        = 'postnl/delivery_options/show_options_for_backorders';
    const XPATH_ALLOW_SUNDAY_SORTING               = 'postnl/cif_labels_and_confirming/allow_sunday_sorting';
    const XPATH_SHOW_OPTIONS_FOR_BUSPAKJE          = 'postnl/delivery_options/show_options_for_buspakje';
    const XPATH_SHOW_ALL_OPTIONS_FOR_BUSPAKJE      = 'postnl/delivery_options/show_all_options_for_buspakje';
    const XPATH_ENABLE_DELIVERY_DAYS_FOR_BUSPAKJE  = 'postnl/delivery_options/enable_delivery_days_for_buspakje';
    const XPATH_ENABLE_PAKJEGEMAK_FOR_BUSPAKJE     = 'postnl/delivery_options/enable_pakjegemak_for_buspakje';
    const XPATH_ENABLE_PAKKETAUTOMAAT_FOR_BUSPAKJE = 'postnl/delivery_options/enable_pakketautomaat_for_buspakje';

    /**
     * Xpaths to extra fee config settings.
     */
    const XPATH_EVENING_TIMEFRAME_FEE  = 'postnl/delivery_options/evening_timeframe_fee';
    const XPATH_PAKJEGEMAK_EXPRESS_FEE = 'postnl/delivery_options/pakjegemak_express_fee';

    /**
     * Xpath for shipping duration setting.
     */
    const XPATH_SHIPPING_DURATION  = 'postnl/cif_labels_and_confirming/shipping_duration';
    const XPATH_CUTOFF_TIME        = 'postnl/cif_labels_and_confirming/cutoff_time';
    const XPATH_SUNDAY_CUTOFF_TIME = 'postnl/cif_labels_and_confirming/sunday_cutoff_time';

    /**
     * The time we consider to be the start of the evening.
     */
    const EVENING_TIME = 1900;

    /**
     * @var array
     */
    protected $_validTypes = array(
        'Overdag',
        'Avond',
        'PG',
        'PGE',
        'PA',
    );

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @return array
     */
    public function getValidTypes()
    {
        return $this->_validTypes;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote) {
            return $this->_quote;
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->_quote = $quote;
        return $quote;
    }

    /**
     * Get the fee charged for evening time frames.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getEveningFee($formatted = false, $includingTax = true, $convert = true)
    {
        $storeId = Mage::app()->getStore()->getId();

        $eveningFee = (float) Mage::getStoreConfig(self::XPATH_EVENING_TIMEFRAME_FEE, $storeId);

        $price = $this->getPriceWithTax($eveningFee, $includingTax, $formatted, false);

        if ($price > 2) {
            $price = 0;
        }

        if ($convert) {
            $quote = $this->getQuote();
            $store = $quote->getStore();

            $price = $store->convertPrice($price, $formatted, false);
        }

        return $price;
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     */
    public function getExpressFee($formatted = false, $includingTax = true, $convert = true)
    {
        $storeId = Mage::app()->getStore()->getId();

        $expressFee = (float) Mage::getStoreConfig(self::XPATH_PAKJEGEMAK_EXPRESS_FEE, $storeId);

        $price = $this->getPriceWithTax($expressFee, $includingTax, $formatted, false);

        if ($price > 2) {
            $price = 0;
        }

        if ($convert) {
            $quote = $this->getQuote();
            $store = $quote->getStore();

            $price = $store->convertPrice($price, $formatted, false);
        }

        return $price;
    }

    /**
     * Gets an array of info regarding chosen delivery options from a specified entity.
     *
     * @param Mage_Core_Model_Abstract $entity
     * @param boolean                  $asVarienObject
     *
     * @return array|false
     */
    public function getDeliveryOptionsInfo(Mage_Core_Model_Abstract $entity, $asVarienObject = true)
    {
        $quoteId        = false;
        $orderId        = false;
        $postnlOrder    = false;
        $postnlShipment = false;

        /**
         * Depending on the type of entity we need to load the PostNL order using a different parameter.
         */
        if ($entity instanceof TIG_PostNL_Model_Core_Order) {
            $postnlOrder = $entity;
        } elseif ($entity instanceof TIG_PostNL_Model_Core_Shipment) {
            $postnlOrder    = $entity->getPostnlOrder();
            $postnlShipment = $entity;
        } elseif ($entity instanceof Mage_Sales_Model_Quote) {
            $quoteId = $entity->getId();
        } elseif ($entity instanceof Mage_Sales_Model_Order) {
            $orderId = $entity->getId();
        } elseif ($entity instanceof Mage_Sales_Model_Order_Invoice
            || $entity instanceof Mage_Sales_Model_Order_Creditmemo
        ) {
            $orderId = $entity->getOrderId();
        } elseif ($entity instanceof Mage_Sales_Model_Order_Shipment) {
            $orderId = $entity->getOrderId();

            $postnlShipment = Mage::getModel('postnl_core/shipment')->load($entity->getId(), 'shipment_id');
            if (!$postnlShipment->getId()) {
                $postnlShipment = false;
            }
        }

        /**
         * Load the PostNL order if we don't already have it using either the order or quote ID.
         */
        if (!$postnlOrder && $quoteId) {
            $postnlOrder = Mage::getModel('postnl_core/order')->load($quoteId, 'quote_id');
        } elseif (!$postnlOrder && $orderId) {
            $postnlOrder = Mage::getModel('postnl_core/order')->load($orderId, 'order_id');
        }

        /**
         * If we still don't have a PostNL order nor a PostNL shipment return false as no info is available.
         */
        if (!$postnlOrder && !$postnlShipment) {
            return false;
        }

        /**
         * This is the basic, empty array of delivery options info.
         */
        $deliveryOptionsInfo = array(
            'type'                     => false,
            'shipment_type'            => false,
            'formatted_type'           => false,
            'product_code'             => false,
            'product_option'           => false,
            'shipment_costs'           => false,
            'confirm_date'             => false,
            'delivery_date'            => false,
            'pakje_gemak_address'      => false,
            'confirm_status'           => false,
            'shipping_phase'           => false,
            'formatted_shipping_phase' => false,
        );

        /**
         * If this was a PakjeGemak order, we need to add the PakjeGemak address.
         */
        $pakjeGemakAddress = $postnlOrder->getPakjeGemakAddress();
        if ($pakjeGemakAddress) {
            $deliveryOptionsInfo['pakje_gemak_address'] = $pakjeGemakAddress->getData();
        }

        /**
         * If the order had any additional fees, we need to add them as well.
         */
        $shipmentCosts = $postnlOrder->getShipmentCosts();
        if ($shipmentCosts) {
            $deliveryOptionsInfo['shipment_costs'] = $shipmentCosts;
        }

        /**
         * If we have a PostNL shipment, we can get some accurate data from it. Otherwise we need to get it from the
         * PostNL order.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        if ($postnlShipment) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
             */
            $type = $postnlShipment->getShipmentType();
            $deliveryOptionsInfo['shipment_type'] = $type;

            /**
             * Confirm status and shipping phase are only known if the supplied entity was a shipment.
             */
            $confirmStatus = $postnlShipment->getConfirmStatus();
            $shippingPhase = $postnlShipment->getShippingPhase();

            if ($confirmStatus) {
                $deliveryOptionsInfo['confirm_status'] = $confirmStatus;
            }

            if ($shippingPhase) {
                /**
                 * Get the string representation of the shipping phase.
                 */
                $formattedShippingPhase = $postnlShipment->getFormattedShippingPhase();

                $deliveryOptionsInfo['shipping_phase']           = $shippingPhase;
                $deliveryOptionsInfo['formatted_shipping_phase'] = $formattedShippingPhase;
            }

            $deliveryDate = $postnlShipment->getDeliveryDate();
            $confirmDate  = $postnlShipment->getConfirmDate();
            $productCode  = $postnlShipment->getProductCode();
        } else {
            $type = $postnlOrder->getType();
            $deliveryOptionsInfo['type'] = $type;

            $deliveryDate = $postnlOrder->getDeliveryDate();
            $confirmDate  = $postnlOrder->getConfirmDate();
            $productCode  = $postnlOrder->getProductCode();
        }

        /**
         * Add the delivery date.
         */
        if ($deliveryDate) {
            $deliveryOptionsInfo['delivery_date'] = $deliveryDate;
        }

        /**
         * Add the confirm date.
         */
        if ($confirmDate) {
            $deliveryOptionsInfo['confirm_date'] = $confirmDate;
        }

        /**
         * Add the product code.
         */
        if ($productCode) {
            $deliveryOptionsInfo['product_code'] = $productCode;

            $allProductOptions = Mage::getModel('postnl_core/system_config_source_allProductOptions')
                                     ->getOptions(array(), true);

            if (array_key_exists($productCode, $allProductOptions)) {
                $deliveryOptionsInfo['product_option'] = $allProductOptions[$productCode];
            }
        }

        /**
         * Determine the formatted order type.
         */
        switch ($type) {
            case 'domestic':
            case 'Overdag':
                $deliveryOptionsInfo['formatted_type'] = 'Overdag';
                break;
            case 'domestic_cod':
                $deliveryOptionsInfo['formatted_type'] = 'Overdag rembours';
                break;
            case 'avond':
            case 'Avond':
                $deliveryOptionsInfo['formatted_type'] = 'Avond';
                break;
            case 'avond_cod':
                $deliveryOptionsInfo['formatted_type'] = 'Avond rembours';
                break;
            case 'pg':
            case 'PG':
                $deliveryOptionsInfo['formatted_type'] = 'PakjeGemak';
                break;
            case 'pg_cod':
                $deliveryOptionsInfo['formatted_type'] = 'PakjeGemak rembours';
                break;
            case 'pge':
            case 'PGE':
                $deliveryOptionsInfo['formatted_type'] = 'PakjeGemak Express';
                break;
            case 'pge_cod':
                $deliveryOptionsInfo['formatted_type'] = 'PakjeGemak Express rembours';
                break;
            case 'pa':
            case 'PA':
                $deliveryOptionsInfo['formatted_type'] = 'Pakketautomaat';
                break;
            case 'eps':
                $deliveryOptionsInfo['formatted_type'] = 'EPS';
                break;
            case 'globalpack':
                $deliveryOptionsInfo['formatted_type'] = 'GlobalPack';
                break;
            case 'buspakje':
                $deliveryOptionsInfo['formatted_type'] = 'Brievenbuspakje';
                break;
            //no default
        }

        if ($asVarienObject) {
            $deliveryOptionsInfo = new Varien_Object($deliveryOptionsInfo);
        }

        /**
         * Return the data.
         */
        return $deliveryOptionsInfo;
    }

    /**
     * Get the delivery date for a specified order date.
     *
     * @param null|string $orderDate
     * @param null|int    $storeId
     * @param boolean     $asDays
     *
     * @return bool|string|int
     */
    public function getDeliveryDate($orderDate = null, $storeId = null, $asDays = false)
    {
        if (!$orderDate) {
            $orderDate = new DateTime(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        }

        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (is_string($orderDate)) {
            $orderDate = new DateTime($orderDate);
        }

        /**
         * Get the base shipping duration for this order.
         */
        $shippingDuration = Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION, $storeId);
        $deliveryTime = clone $orderDate;
        $deliveryTime->add(new DateInterval("P{$shippingDuration}D"));

        /**
         * Get the cut-off time. This is formatted as H:i:s.
         */
        $cutOffTime = Mage::getStoreConfig(self::XPATH_CUTOFF_TIME, $storeId);
        $orderTime = $orderDate->format('His');

        /**
         * Check if the current time (as His) is greater than the cut-off time.
         */
        if ($orderTime > str_replace(':', '', $cutOffTime)) {
            $deliveryTime->add(new DateInterval('P1D'));
            $shippingDuration++;
        }

        /**
         * Get the delivery day (1-7).
         */
        $deliveryDay = $deliveryTime->format('N');

        /**
         * If the delivery day is a monday, we need to make sure that sunday sorting is allowed. Otherwise delivery on a
         * monday is not possible.
         */
        if ($deliveryDay == 1 && !Mage::helper('postnl/deliveryOptions')->canUseSundaySorting()) {
            $sundayCutOffTime = Mage::getStoreConfig(self::XPATH_SUNDAY_CUTOFF_TIME, $storeId);
            if ($orderTime <= str_replace(':', '', $sundayCutOffTime)) {
                $deliveryTime->add(new DateInterval('P1D'));
                $shippingDuration++;
            }
        }

        if ($asDays) {
            return $shippingDuration;
        }

        $deliveryDate = $deliveryTime->format('Y-m-d');
        return $deliveryDate;
    }

    /**
     * Get the first possible delivery date as determined by PostNL.
     *
     * @param string                      $postcode A valid Dutch postcode (4 numbers and 2 letters).
     * @param bool|Mage_Sales_Model_Quote $quote
     * @param bool                        $throwException
     *
     * @return bool|string
     *
     * @throws Exception
     * @throws TIG_PostNL_Exception
     */
    public function getPostcodeDeliveryDate($postcode, $quote = false, $throwException = false)
    {
        /**
         * Parse the postcode so it is fully uppercase and contains no spaces.
         */
        $postcode = str_replace(' ', '', strtoupper($postcode));

        /**
         * Validate the postcode.
         */
        $validator = new Zend_Validate_PostCode('nl_NL');
        $isValid = $validator->isValid($postcode);
        if (!$isValid && $throwException) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid postcode supplied for GetDeliveryDate request: %s Postcodes may only contain 4 numbers '
                    . 'and 2 letters.',
                    $postcode
                ),
                'POSTNL-0131'
            );
        } elseif (!$isValid) {
            return false;
        }

        /**
         * If no quote was specified, try to load the quote.
         */
        if (!$quote && $this->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } elseif(!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        if (!$quote) {
            return false;
        }

        /**
         * Send a SOAP request to PostNL to get the earliest possible delivery date.
         */
        try {
            $cif      = Mage::getModel('postnl_deliveryoptions/cif');
            $response = $cif->setStoreId(Mage::app()->getStore()->getId())
                            ->getDeliveryDate($postcode, $quote);
        } catch(Exception $e) {
            $this->logException($e);

            if ($this->_canShowErrorDetails()) {
                $this->addExceptionSessionMessage('core/session', $e);
            }

            if ($throwException) {
                throw $e;
            }

            return false;
        }

        return $response;
    }

    /**
     * Gets the shipping duration for the specified quote.
     *
     * @param bool|Mage_Sales_Model_Quote $quote
     *
     * @return int|bool
     *
     * @throws TIG_PostNL_Exception
     */
    public function getShippingDuration(Mage_Sales_Model_Quote $quote = null)
    {
        /**
         * If no quote was specified, try to load the quote.
         */
        if (!$quote && $this->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        } elseif(!$quote) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        if (!$quote) {
            return false;
        }

        $storeId = $quote->getStoreId();

        /**
         * Get the default config duration.
         */
        $configDuration = (int) Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION, $storeId);
        $durationArray  = array($configDuration);

        /**
         * Loop through all products in the quote.
         *
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            /**
             * If the product has a specific shipping duration, add it to the array of durations.
             */
            if ($product->hasData('postnl_shipping_duration')
                && $product->getData('postnl_shipping_duration') !== ''
            ) {
                $durationArray[] = (int) $product->getData('postnl_shipping_duration');
            }
        }

        /**
         * Sort the array and get it's last item. This will be the highest value.
         */
        natsort($durationArray);
        $shippingDuration = end($durationArray);

        /**
         * Make sure the value is between 1 and 14 days.
         */
        if ($shippingDuration > 14 || $shippingDuration < 1) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Invalid shipping duration: %s. Shipping duration must be between 1 and 14 days.',
                    $shippingDuration
                ),
                'POSTNL-0127'
            );
        }

        return $shippingDuration;
    }

    /**
     * Convert a value to a formatted price.
     *
     * @param float   $price
     * @param boolean $includingTax
     * @param boolean $formatted
     * @param boolean $convert
     *
     * @return float
     *
     * @see Mage_Checkout_Block_Onepage_Shipping_Method_Available::getShippingPrice()
     */
    public function getPriceWithTax($price, $includingTax, $formatted = false, $convert = true)
    {
        $quote = $this->getQuote();
        $store = $quote->getStore();

        $shippingPrice  = Mage::helper('tax')->getShippingPrice($price, $includingTax, $quote->getShippingAddress());

        if ($convert) {
            $shippingPrice = $store->convertPrice($shippingPrice, $formatted, false);
        }

        return $shippingPrice;
    }

    /**
     * Mark a set of location results with the 'isEvening' parameter. This will allow the google maps api to easily
     * identify which locations may be filtered out later.
     *
     * @param array  $locations    An array of PostNL location objects
     * @param string $deliveryDate The date on which the package should be delivered.
     *
     * @return array
     */
    public function markEveningLocations($locations, $deliveryDate)
    {
        /**
         * Get the day of the week on which the package should be delivered.
         *
         * date('l') returns the full textual representation of the day of the week (Sunday through Saturday).
         */
        $deliveryDate = new DateTime($deliveryDate);
        $weekDay = $deliveryDate->format('l');

        foreach ($locations as &$location) {
            /**
             * if we don't have any business hours specified for this date, the location is closed.
             */
            if (!isset($location->OpeningHours->$weekDay->string)) {
                $location->isEvening = false;

                continue;
            }

            /**
             * Check if the location is open in the evening and mark it accordingly.
             */
            $businessHours = $location->OpeningHours->$weekDay->string;
            if ($this->_businessHoursIsEvening($businessHours)) {
                $location->isEvening = true;

                continue;
            }

            $location->isEvening = false;

            continue;
        }

        return $locations;
    }

    /**
     * Check if an array of business hours contains a timespan that is considered to be in the evening.
     *
     * @param array $businessHours
     *
     * @return bool
     */
    protected function _businessHoursIsEvening($businessHours)
    {
        foreach ($businessHours as $businessHour) {
            if ($this->_isEvening($businessHour)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specified opening time is considered to be in the evening. Opening times must be formatted as
     * H:i-H:i. The second part of the time (the closing time) will be compared to the self::EVENING_TIME constant to
     * find out if it's in the evening.
     *
     * @param $time
     *
     * @return bool
     */
    protected function _isEvening($time)
    {
        $timeParts = explode('-', $time);

        if (!isset($timeParts[1])) {
            return false;
        }

        $closingTime = str_replace(':', '', $timeParts[1]);

        if ($closingTime >= self::EVENING_TIME) {
            return true;
        }

        return false;
    }

    /**
     * Checks if PakjeGemak is available.
     *
     * @param boolean $storeId
     * @param boolean $checkQuote
     *
     * @return boolean
     */
    public function canUsePakjeGemak($storeId = false, $checkQuote = true)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_pakje_gemak';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if ($checkQuote) {
            /**
             * Check if these options are allowed for this specific quote.
             */
            $canUseForQuote = $this->canUsePakjeGemakForQuote();

            if (!$canUseForQuote) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUsePakjeGemak()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUsePakjeGemak();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $allowed = $this->_canUsePakjeGemak();

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUsePakjeGemak($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Check if PakjeGemak is allowed for the current quote.
     *
     * @return bool
     */
    public function canUsePakjeGemakForQuote()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $quote = $this->getQuote();
        if (!$quote) {
            return true;
        }

        $registryKey = 'can_use_pakje_gemak_for_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If the current quote fits as a letter box parcel and the calculation mode is set to 'automatic', check if
         * these options are available for letter box parcel orders.
         */
        if ($this->isBuspakjeConfigApplicableToQuote($quote)
            && !$this->canShowPakjeGemakForBuspakje($quote)
        ) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if any products in the quote have explicitly disabled PakjeGemak locations.
         *
         * @var Mage_Sales_Model_Quote_item $item
         */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $poLocationsAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $item->getProductId(),
                'postnl_allow_pakje_gemak',
                $item->getStoreId()
            );

            if (!is_null($poLocationsAllowed) && !$poLocationsAllowed) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Checks if PakjeGemak is available.
     *
     * @return boolean
     */
    protected function _canUsePakjeGemak()
    {
        $storeId = Mage::app()->getStore()->getId();

        /**
         * Check if PakjeGemak has ben enabled in the configuration.
         */
        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKJEGEMAK, $storeId);
        if (!$enabled) {
            return false;
        }

        /**
         * The parent canUsePakjeGemak() method will check if any PakjeGemak product options are available.
         */
        $allowed = parent::canUsePakjeGemak($storeId);

        return $allowed;
    }

    /**
     * Checks if PakjeGemak Express is available.
     *
     * @return boolean
     */
    public function canUsePakjeGemakExpress()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_pakje_gemak_express';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If PakjeGemak is not allowed, neither is PakjeGemak Express.
         */
        if (!$this->canUsePakjeGemak()) {
            Mage::register($registryKey, false);
            return false;
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUsePakjeGemakExpress()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUsePakjeGemakExpress();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $allowed = $this->_canUsePakjeGemakExpress();

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUsePakjeGemakExpress($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Checks if PakjeGemak Express is available.
     *
     * @return boolean
     */
    protected function _canUsePakjeGemakExpress()
    {
        $storeId = Mage::app()->getStore()->getId();

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKJEGEMAK_EXPRESS, $storeId);
        if (!$enabled) {
            return false;
        }

        /**
         * Check if any PGE product options are available.
         */
        $pgeOptions = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions')
                          ->getAvailablePgeOptions($storeId);

        $allowed = false;
        if (!empty($pgeOptions)) {
            $allowed = true;
        }

        return $allowed;
    }

    /**
     * Checks if 'pakketautomaat' is available.
     *
     * @param boolean $checkQuote
     *
     * @return boolean
     */
    public function canUsePakketAutomaat($checkQuote = true)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_pakketautomaat';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if ($checkQuote) {
            /**
             * Check if these options are allowed for this specific quote.
             */
            $canUseForQuote = $this->canUsePakketAutomaatForQuote();

            if (!$canUseForQuote) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUsePakketAutomaat()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUsePakketAutomaat();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $allowed = $this->_canUsePakketAutomaat();

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUsePakketAutomaat($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Check if 'pakketautomaat' is allowed for the current quote.
     *
     * @return bool
     */
    public function canUsePakketAutomaatForQuote()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $quote = $this->getQuote();
        if (!$quote) {
            return true;
        }

        $registryKey = 'can_use_pakketautomaat_for_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If the current quote fits as a letter box parcel and the calculation mode is set to 'automatic', check if
         * these options are available for letter box parcel orders.
         */
        if ($this->isBuspakjeConfigApplicableToQuote($quote)
            && !$this->canShowPakketAutomaatForBuspakje($quote)
        ) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if any product in the quote has explicitly disabled pakketautomaat.
         *
         * @var Mage_Sales_Model_Quote_item $item
         */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $pakketautomaatAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $item->getProductId(),
                'postnl_allow_pakketautomaat',
                $item->getStoreId()
            );

            if ($pakketautomaatAllowed === '0') {
                Mage::register($registryKey, false);
                return false;
            }
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Checks if 'pakketautomaat' is available.
     *
     * @return boolean
     */
    protected function _canUsePakketAutomaat()
    {
        $storeId = Mage::app()->getStore()->getId();

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_PAKKETAUTOMAAT_LOCATIONS, $storeId);
        if (!$enabled) {
            return false;
        }

        /**
         * Check if any pakketautomaat product options are available.
         */
        $pakketautomaatOptions = Mage::getModel('postnl_core/system_config_source_pakketautomaatProductOptions')
                                     ->getAvailableOptions();

        $allowed = false;
        if (!empty($pakketautomaatOptions)) {
            $allowed = true;
        }

        return $allowed;
    }

    /**
     * Checks if delivery days are available.
     *
     * @param bool $checkQuote
     *
     * @return bool
     */
    public function canUseDeliveryDays($checkQuote = true)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_delivery_days';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if ($checkQuote) {
            /**
             * Check if these options are allowed for this specific quote.
             */
            $canUseForQuote = $this->canUseDeliveryDaysForQuote();

            if (!$canUseForQuote) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUseDeliveryDays()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUseDeliveryDays();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $storeId = Mage::app()->getStore()->getId();

        $allowed = Mage::getStoreConfigFlag(self::XPATH_ENABLE_DELIVERY_DAYS, $storeId);

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUseDeliveryDays($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Check if delivery days are allowed for the current quote.
     *
     * @return bool
     */
    public function canUseDeliveryDaysForQuote()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $quote = $this->getQuote();
        if (!$quote) {
            return true;
        }

        $registryKey = 'can_use_delivery_days_for_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If the current quote fits as a letter box parcel and the calculation mode is set to 'automatic', check if
         * these options are available for letter box parcel orders.
         */
        if ($this->isBuspakjeConfigApplicableToQuote($quote)
            && !$this->canShowDeliveryDaysForBuspakje($quote)
        ) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * @var Mage_Sales_Model_Quote_item $item
         */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $deliveryDaysAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $item->getProductId(),
                'postnl_allow_delivery_days',
                $item->getStoreId()
            );

            if ($deliveryDaysAllowed === '0') {
                Mage::register($registryKey, false);
                return false;
            }
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Checks if time frames are available.
     *
     * @param boolean $checkQuote
     *
     * @return boolean
     */
    public function canUseTimeframes($checkQuote = true)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_timeframes';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if (!$this->canUseDeliveryDays()) {
            Mage::register($registryKey, false);
            return false;
        }

        if ($checkQuote) {
            /**
             * Check if these options are allowed for this specific quote.
             */
            $canUseForQuote = $this->canUseTimeframesForQuote();

            if (!$canUseForQuote) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUseTimeframes()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUseTimeframes();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        if ($quote) {
            $storeId = $quote->getStoreId();
        } else {
            $storeId = Mage::app()->getStore()->getId();
        }

        $allowed = Mage::getStoreConfigFlag(self::XPATH_ENABLE_TIMEFRAMES, $storeId);

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUseTimeframes($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Check if time frames are allowed for the current quote.
     *
     * @return bool
     */
    public function canUseTimeframesForQuote()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $quote = $this->getQuote();
        if (!$quote) {
            return true;
        }

        $registryKey = 'can_use_timeframes_for_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If the current quote fits as a letter box parcel and the calculation mode is set to 'automatic', check if
         * these options are available for letter box parcel orders.
         */
        if ($this->isBuspakjeConfigApplicableToQuote($quote)
            && !$this->canShowAllDeliveryOptionsForBuspakje($quote)
        ) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * @var Mage_Sales_Model_Quote_item $item
         */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $timeframesAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $item->getProductId(),
                'postnl_allow_timeframes',
                $item->getStoreId()
            );

            if ($timeframesAllowed === '0') {
                Mage::register($registryKey, false);
                return false;
            }
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Checks if evening time frames are available.
     *
     * @return boolean
     */
    public function canUseEveningTimeframes()
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_evening_timeframes';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if (!$this->canUseTimeframes()) {
            Mage::register($registryKey, false);
            return false;
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUseEveningTimeframes()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsCanUseEveningTimeframes();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $allowed = $this->_canUseEveningTimeframes();

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUseEveningTimeframes($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Checks if evening time frames are available.
     *
     * @return boolean
     */
    protected function _canUseEveningTimeframes()
    {
        $storeId = Mage::app()->getStore()->getId();

        $enabled = Mage::getStoreConfigFlag(self::XPATH_ENABLE_EVENING_TIMEFRAMES, $storeId);
        if (!$enabled) {
            return false;
        }

        $eveningOptions = Mage::getModel('postnl_core/system_config_source_standardProductOptions')
                              ->getAvailableAvondOptions($storeId);

        $allowed = false;
        if (!empty($eveningOptions)) {
            $allowed = true;
        }

        return $allowed;
    }

    /**
     * Checks if sunday sorting is allowed.
     *
     * @return bool
     */
    public function canUseSundaySorting()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsCanUseSundaySorting()) {
            return $cache->getPostnlDeliveryOptionsCanUseSundaySorting();
        }

        $storeId = Mage::app()->getStore()->getId();

        $allowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsCanUseSundaySorting($allowed)
                  ->saveCache();
        }

        return $allowed;
    }

    /**
     * Check if PostNL delivery options may be used based on a quote.
     *
     * @param Mage_Sales_Model_Quote|boolean $quote
     *
     * @return boolean
     */
    public function canUseDeliveryOptions($quote = false)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_delivery_options';
        if ($quote && $quote->getId()) {
            $registryKey .= '_quote_id_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        Mage::unregister('postnl_delivery_options_can_use_delivery_options_errors');

        $deliveryOptionsEnabled = $this->isDeliveryOptionsEnabled();
        if (!$deliveryOptionsEnabled) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check that dutch addresses are allowed
         */
        if (!$this->canUseStandard()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0106',
                    'message' => $this->__(
                        'No standard product options are enabled. At least 1 option must be active.'
                    ),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * If we have no quote, we have no further checks to perform.
         */
        if (!$quote) {
            Mage::register($registryKey, true);
            return true;
        }

        $canUseDeliveryOptionsForQuote = $this->canUseDeliveryOptionsForQuote($quote);

        Mage::register($registryKey, $canUseDeliveryOptionsForQuote);
        return $canUseDeliveryOptionsForQuote;
    }

    /**
     * Check if delivery options are allowed for the specified quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canUseDeliveryOptionsForQuote(Mage_Sales_Model_Quote $quote)
    {
        Mage::unregister('postnl_delivery_options_can_use_delivery_options_errors');

        /**
         * PostNL delivery options cannot be used for virtual orders
         */
        if ($quote->isVirtual()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0104',
                    'message' => $this->__('The quote is virtual.'),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
            return false;
        }

        /**
         * Check if the quote has a valid minimum amount
         */
        if (!$quote->validateMinimumAmount()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0105',
                    'message' => $this->__("The quote's grand total is below the minimum amount required."),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
            return false;
        }

        /**
         * Check if the current quote is a letter box parcel order and if so, if delivery options are allowed for letter
         * box parcel orders.
         */
        if ($this->isBuspakjeConfigApplicableToQuote($quote)
            && !$this->canShowDeliveryOptionsForBuspakje($quote)
        ) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0190',
                    'message' => $this->__('Delivery options are not allowed for letter box parcel orders.'),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
            return false;
        }

        $storeId = $quote->getStoreId();

        /**
         * Check if PostNL delivery options may be used for out-og-stock orders and if not, whether the quote has any
         * such products.
         */
        $showDeliveryOptionsForBackorders = Mage::getStoreConfigFlag(self::XPATH_SHOW_OPTIONS_FOR_BACKORDERS, $storeId);
        if (!$showDeliveryOptionsForBackorders) {
            $containsOutOfStockItems = $this->quoteHasOutOfStockItems($quote);
            if ($containsOutOfStockItems) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0102',
                        'message' => $this->__('One or more items in the cart are out of stock.'),
                    )
                );
                Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
                return false;
            }
        }

        /**
         * Check if the quote contains a product for which delivery options are not allowed.
         *
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($quote->getAllVisibleItems() as $item) {
            $productId = $item->getProductId();
            $allowDeliveryOptions = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $productId,
                'postnl_allow_delivery_options',
                $item->getStoreId()
            );

            if ($allowDeliveryOptions === '0') {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0161',
                        'message' => $this->__('Delivery options are not allowed for product #%s.', $productId),
                    )
                );
                Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the delivery options may be used for the currently chosen shipping destination.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return boolean
     */
    public function canUseDeliveryOptionsForCountry(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_use_delivery_options_for_country_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If no shipping address is available, we have nothing to check and delivery options will not be allowed.
         */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Delivery options are only available when shipping to the Netherlands.
         */
        if ($shippingAddress->getCountry() != 'NL') {
            Mage::register($registryKey, false);
            return false;
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Checks if the buspakje-specific configuration is applicable to the current quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function isBuspakjeConfigApplicableToQuote(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'is_buspakje_config_applicable_to_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If the buspakje calculation mode is set to 'manual', no further checks are required as the regular delivery
         * option rules will apply.
         */
        if ($this->getBuspakjeCalculationMode() != 'automatic') {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if the current quote would fit as a letter box parcel.
         */
        $quoteItems = $quote->getAllItems();

        $fits = $this->fitsAsBuspakje($quoteItems);

        Mage::register($registryKey, $fits);
        return $fits;
    }

    /**
     * Checks if delivery options are disabled for letter box parcel orders.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowDeliveryOptionsForBuspakje(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_options_for_buspakje_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * Check if showing delivery options for letter box parcel orders is allowed in the configuration.
         */
        $showDeliveryOptions = Mage::getStoreConfigFlag(
            self::XPATH_SHOW_OPTIONS_FOR_BUSPAKJE,
            $quote->getStoreId()
        );

        Mage::register($registryKey, $showDeliveryOptions);
        return $showDeliveryOptions;
    }

    /**
     * Checks whether all delivery options are allowed for letter box parcel orders.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowAllDeliveryOptionsForBuspakje(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_all_options_for_buspakje_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If we can't show any delivery options for letter box parcels, return false.
         */
        if (!$this->canShowDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if showing all delivery options is allowed in the configuration.
         */
        $canShowAllOptions = Mage::getStoreConfigFlag(
            self::XPATH_SHOW_ALL_OPTIONS_FOR_BUSPAKJE,
            $quote->getStoreId()
        );

        Mage::register($registryKey, $canShowAllOptions);
        return $canShowAllOptions;
    }

    /**
     * Determine whether delivery days are allowed for letter box parcel orders.This method will return true if the
     * buspakje calculation mode is set to manual, the order isn't a letter box parcel order or if all delivery options
     * are allowed for letter box parcel orders.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowDeliveryDaysForBuspakje(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_delivery_days_for_buspakje_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If we can't show any delivery options for letter box parcels, return false.
         */
        if (!$this->canShowDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * If all delivery options are allowed for letter box parcels, return true.
         */
        if ($this->canShowAllDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, true);
            return true;
        }

        /**
         * Check the configuration to see if delivery days are allowed for letter box parcels.
         */
        $deliveryDaysEnabledForBuspakje = Mage::getStoreConfigFlag(
            self::XPATH_ENABLE_DELIVERY_DAYS_FOR_BUSPAKJE,
            $quote->getStoreId()
        );

        Mage::register($registryKey, $deliveryDaysEnabledForBuspakje);
        return $deliveryDaysEnabledForBuspakje;
    }

    /**
     * Determine whether PakjeGemak locations (including parcel dispensers) are allowed for letter box parcel orders.
     * This method will return true if the buspakje calculation mode is set to manual, the order isn't a letter box
     * parcel order or if all delivery options are allowed for letter box parcel orders.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowPakjeGemakForBuspakje(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_pakje_gemak_for_buspakje_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If we can't show any delivery options for letter box parcels, return false.
         */
        if (!$this->canShowDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * If all delivery options are allowed for letter box parcels, return true.
         */
        if ($this->canShowAllDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, true);
            return true;
        }

        /**
         * Check the configuration to see if PakjeGemak is allowed for letter box parcels.
         */
        $pakjeGemakEnabledForBuspakje = Mage::getStoreConfigFlag(
            self::XPATH_ENABLE_PAKJEGEMAK_FOR_BUSPAKJE,
            $quote->getStoreId()
        );

        Mage::register($registryKey, $pakjeGemakEnabledForBuspakje);
        return $pakjeGemakEnabledForBuspakje;
    }

    /**
     * Determine whether PakjeGemak locations (including parcel dispensers) are allowed for letter box parcel orders.
     * This method will return true if the buspakje calculation mode is set to manual, the order isn't a letter box
     * parcel order or if all delivery options are allowed for letter box parcel orders.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowPakketAutomaatForBuspakje(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_pakketautomaat_for_buspakje_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * If we can't show any delivery options for letter box parcels, return false.
         */
        if (!$this->canShowDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * If all delivery options are allowed for letter box parcels, return true.
         */
        if ($this->canShowAllDeliveryOptionsForBuspakje($quote)) {
            Mage::register($registryKey, true);
            return true;
        }

        /**
         * Check the configuration to see if 'pakketautomaat' is allowed for letter box parcels.
         */
        $pakketautomaatEnabledForBuspakje = Mage::getStoreConfigFlag(
            self::XPATH_ENABLE_PAKKETAUTOMAAT_FOR_BUSPAKJE,
            $quote->getStoreId()
        );

        Mage::register($registryKey, $pakketautomaatEnabledForBuspakje);
        return $pakketautomaatEnabledForBuspakje;
    }

    /**
     * Check if the module is set to test mode
     *
     * @param bool $storeId
     *
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry('delivery_options_test_mode') !== null) {
            return Mage::registry('delivery_options_test_mode');
        }

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $testMode = Mage::getStoreConfigFlag(self::XPATH_TEST_MODE, $storeId);

        Mage::register('delivery_options_test_mode', $testMode);
        return $testMode;
    }

    /**
     * Checks if PostNL delivery options are enabled.
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isDeliveryOptionsEnabled($storeId = null)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'is_delivery_options_enabled';

        $quote = $this->getQuote();
        if ($quote) {
            $registryKey .= '_' . $quote->getId();
        }

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlDeliveryOptionsIsEnabled()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getPostnlDeliveryOptionsIsEnabled();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        /**
         * Calculate if the delivery options are enabled.
         */
        $isEnabled = $this->_isDeliveryOptionsEnabled($storeId);

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setPostnlDeliveryOptionsIsEnabled($isEnabled)
                  ->saveCache();
        }

        Mage::register($registryKey, $isEnabled);
        return $isEnabled;
    }

    /**
     * Checks if PostNL delivery options are enabled.
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    protected function _isDeliveryOptionsEnabled($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        Mage::unregister('postnl_delivery_options_is_enabled_errors');

        /**
         * Check if the PostNL extension is enabled.
         */
        $isPostnlEnabled = $this->isEnabled($storeId);
        if ($isPostnlEnabled === false) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0107',
                    'message' => $this->__('You have not yet enabled the PostNL extension.'),
                )
            );
            Mage::register('postnl_delivery_options_is_enabled_errors', $errors);
            return false;
        }

        /**
         * Check if delivery options have been enabled in the config.
         */
        $isDeliveryOptionsActive = $this->isDeliveryOptionsActive($storeId);
        if (!$isDeliveryOptionsActive) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0133',
                    'message' => $this->__('You have not yet enabled PostNL delivery options.'),
                )
            );
            Mage::register('postnl_delivery_options_is_enabled_errors', $errors);
            return false;
        }

        return true;
    }

    /**
     * Checks if PostNL delivery options is active.
     *
     * @param null|int $storeId
     *
     * @return boolean
     */
    public function isDeliveryOptionsActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $isActive = Mage::getStoreConfigFlag(self::XPATH_DELIVERY_OPTIONS_ACTIVE, $storeId);

        return $isActive;
    }
}
