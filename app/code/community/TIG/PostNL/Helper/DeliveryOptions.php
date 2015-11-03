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
    const XPATH_STOCK_OPTIONS                      = 'postnl/delivery_options/stock_options';
    const XPATH_ALLOW_SUNDAY_SORTING               = 'postnl/delivery_options/allow_sunday_sorting';
    const XPATH_SHOW_OPTIONS_FOR_BUSPAKJE          = 'postnl/delivery_options/show_options_for_buspakje';
    const XPATH_SHOW_ALL_OPTIONS_FOR_BUSPAKJE      = 'postnl/delivery_options/show_all_options_for_buspakje';
    const XPATH_ENABLE_DELIVERY_DAYS_FOR_BUSPAKJE  = 'postnl/delivery_options/enable_delivery_days_for_buspakje';
    const XPATH_ENABLE_PAKJEGEMAK_FOR_BUSPAKJE     = 'postnl/delivery_options/enable_pakjegemak_for_buspakje';
    const XPATH_ENABLE_PAKKETAUTOMAAT_FOR_BUSPAKJE = 'postnl/delivery_options/enable_pakketautomaat_for_buspakje';
    const XPATH_STATED_ADDRESS_ONLY_OPTION         = 'postnl/delivery_options/stated_address_only_option';
    const XPATH_ENABLE_SUNDAY_DELIVERY             = 'postnl/delivery_options/enable_sunday_delivery';

    /**
     * Xpaths to extra fee config settings.
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    const XPATH_EVENING_TIMEFRAME_FEE   = TIG_PostNL_Helper_DeliveryOptions_Fee::XPATH_EVENING_TIMEFRAME_FEE;
    /**
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    const XPATH_PAKJEGEMAK_EXPRESS_FEE  = TIG_PostNL_Helper_DeliveryOptions_Fee::XPATH_PAKJEGEMAK_EXPRESS_FEE;
    /**
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    const XPATH_ONLY_STATED_ADDRESS_FEE = TIG_PostNL_Helper_DeliveryOptions_Fee::XPATH_ONLY_STATED_ADDRESS_FEE;

    /**
     * Xpath for shipping duration setting.
     */
    const XPATH_SHIPPING_DURATION  = 'postnl/cif_labels_and_confirming/shipping_duration';
    const XPATH_CUTOFF_TIME        = 'postnl/cif_labels_and_confirming/cutoff_time';
    const XPATH_SUNDAY_CUTOFF_TIME = 'postnl/cif_labels_and_confirming/sunday_cutoff_time';
    const XPATH_SHIPPING_DAYS      = 'postnl/cif_labels_and_confirming/shipping_days';

    /**
     * Xpath to the 'stated_address_only_checked' setting.
     */
    const XPATH_STATED_ADDRESS_ONLY_CHECKED = 'postnl/delivery_options/stated_address_only_checked';

    /**
     * Xpath to the responsive design setting.
     */
    const XPATH_RESPONSIVE = 'postnl/delivery_options/responsive';

    /**
     * Xpath to the Google Maps API key.
     */
    const XPATH_GOOGLE_MAPS_API_KEY = 'postnl/google_maps/api_key';

    /**
     * The time we consider to be the start of the evening.
     */
    const EVENING_TIME = 1900;

    /**
     * The maximum fee amount allowed for evening and early delivery options.
     * @deprecated deprecated since version 1.7.0
     */
    const MAX_FEE = 2;

    /**
     * @var array
     */
    protected $_validTypes = array(
        'Overdag',
        'Avond',
        'PG',
        'PGE',
        'PA',
        'Sunday'
    );

    /**
     * @var null
     */
    protected $_configManageStock = null;

    /**
     * @var null
     */
    protected $_configBackorders = null;

    /**
     * @var null
     */
    protected $_configMinQty = null;

    /**
     * @return int
     */
    public function getConfigBackorders()
    {
        $configBackorders = $this->_configBackorders;

        if (is_null($configBackorders)) {
            $configBackorders  = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);

            $this->setConfigBackorders($configBackorders);
        }

        return $configBackorders;
    }

    /**
     * @param int $configBackorders
     *
     * @return $this
     */
    public function setConfigBackorders($configBackorders)
    {
        $this->_configBackorders = $configBackorders;

        return $this;
    }

    /**
     * @return bool
     */
    public function getConfigManageStock()
    {
        $configManageStock = $this->_configManageStock;

        if (is_null($configManageStock)) {
            $configManageStock  = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);

            $this->setConfigManageStock($configManageStock);
        }

        return $configManageStock;
    }

    /**
     * @param bool $configManageStock
     *
     * @return $this
     */
    public function setConfigManageStock($configManageStock)
    {
        $this->_configManageStock = $configManageStock;

        return $this;
    }

    /**
     * @return int
     */
    public function getConfigMinQty()
    {
        $configMinQty = $this->_configManageStock;

        if (is_null($configMinQty)) {
            $configMinQty  = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);

            $this->setConfigMinQty($configMinQty);
        }

        return $configMinQty;
    }

    /**
     * @param int $configMinQty
     *
     * @return $this
     */
    public function setConfigMinQty($configMinQty)
    {
        $this->_configMinQty = $configMinQty;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidTypes()
    {
        return $this->_validTypes;
    }

    /**
     * Get the fee charged for evening time frames.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getEveningFee($formatted = false, $includingTax = true, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getEveningFee($formatted, $includingTax, $convert);
    }

    /**
     * Get the fee charged for PakjeGemak Express.
     *
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getExpressFee($formatted = false, $includingTax = true, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getExpressFee($formatted, $includingTax, $convert);
    }

    /**
     * Get the fee for PakjeGemak locations. This is only applicable to buspakje orders.
     *
     * @param float   $currentRate
     * @param boolean $formatted
     * @param boolean $includingTax
     * @param boolean $convert
     *
     * @return float|int
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getPakjeGemakFee($currentRate, $formatted = false, $includingTax = true, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getPakjeGemakFee($currentRate, $formatted, $includingTax, $convert);
    }

    /**
     * Get the fee charged for possible options saved to the PostNL order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param bool                        $formatted
     * @param bool                        $includingTax
     * @param bool                        $convert
     *
     * @return float|int
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getOptionsFee(TIG_PostNL_Model_Core_Order $postnlOrder, $formatted = false, $includingTax = true, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getOptionsFee($postnlOrder, $formatted, $includingTax, $convert);
    }

    /**
     * Gets the configured fee for a specified option.
     *
     * @param string $option
     * @param bool  $formatted
     * @param bool  $includingTax
     * @param bool  $convert
     *
     * @return float|int
     *
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getOptionFee($option, $formatted = false, $includingTax = true, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getOptionFee($option, $formatted, $includingTax, $convert);
    }

    /**
     * Gets an array of info regarding chosen delivery options from a specified entity.
     *
     * @param Mage_Core_Model_Abstract $entity
     * @param boolean                  $asVarienObject
     *
     * @return array|Varien_Object|false
     *
     * @todo refactor to reduce cyclomatic complexity
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
            'type'                      => false,
            'shipment_type'             => false,
            'formatted_type'            => false,
            'product_code'              => false,
            'product_option'            => false,
            'shipment_costs'            => false,
            'confirm_date'              => false,
            'delivery_date'             => false,
            'store_confirm_date'        => false,
            'store_delivery_date'       => false,
            'pakje_gemak_address'       => false,
            'confirm_status'            => false,
            'shipping_phase'            => false,
            'formatted_shipping_phase'  => false,
            'delivery_time_start'       => false,
            'delivery_time_end'         => false,
            'store_delivery_time_start' => false,
            'store_delivery_time_end'   => false,
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
         * Get the time zone used by the store in which the order was placed.
         */
        $storeTimezone = $this->getStoreTimeZone($postnlOrder->getStoreId(), true);
        $utcTimeZone = new DateTimeZone('UTC');

        /**
         * If the customer chose a specific delivery time, add that to the array.
         */
        if ($postnlOrder->hasExpectedDeliveryTimeStart()) {
            $startTime = new DateTime($postnlOrder->getExpectedDeliveryTimeStart(), $utcTimeZone);

            $storeStartTime = new DateTime($postnlOrder->getExpectedDeliveryTimeStart(), $utcTimeZone);
            $storeStartTime->setTimezone($storeTimezone);

            $deliveryOptionsInfo['delivery_time_start'] = $startTime->format('H:i');
            $deliveryOptionsInfo['store_delivery_time_start'] = $storeStartTime->format('H:i');


            /**
             * In the case of PakjeGemak shipments there is only a start time and no end time.
             */
            if ($postnlOrder->hasExpectedDeliveryTimeEnd()) {
                $endTime = new DateTime($postnlOrder->getExpectedDeliveryTimeEnd(), $utcTimeZone);

                $storeEndTime = new DateTime($postnlOrder->getExpectedDeliveryTimeEnd(), $utcTimeZone);
                $storeEndTime->setTimezone($storeTimezone);

                $deliveryOptionsInfo['delivery_time_end'] = $endTime->format('H:i');
                $deliveryOptionsInfo['store_delivery_time_end'] = $storeEndTime->format('H:i');
            }
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
            $deliveryDate = new DateTime($deliveryDate, $utcTimeZone);

            $deliveryOptionsInfo['delivery_date'] = $deliveryDate->format('Y-m-d H:i:s');
            $deliveryOptionsInfo['store_delivery_date'] = $deliveryDate->setTimezone($storeTimezone)
                                                                       ->format('Y-m-d H:i:s');
        }

        /**
         * Add the confirm date.
         */
        if ($confirmDate) {
            $confirmDate = new DateTime($confirmDate, $utcTimeZone);

            $deliveryOptionsInfo['confirm_date'] = $confirmDate->format('Y-m-d H:i:s');
            $deliveryOptionsInfo['store_confirm_date'] = $confirmDate->setTimezone($storeTimezone)
                                                                     ->format('Y-m-d H:i:s');
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
     * Filters the given timeFrames, only returning the valid ones.
     *
     * $helper TIG_PostNL_Helper_Date
     *
     * @param $storeId
     * @param StdClass[] $timeframes
     *
     * @return StdClass[]|false
     */
    public function filterTimeFrames($timeframes, $storeId) {
        /** @var TIG_PostNL_Helper_Date $helper */
        $helper = Mage::helper('postnl/date');

        $deliveryDateArray = $helper->getValidDeliveryDaysArray($storeId);

        foreach($timeframes as $key => $timeFrame) {
            $timeFrameDate = new DateTime($timeFrame->Date, new DateTimeZone('UTC'));
            $timeFrameDay = $timeFrameDate->format('N');
            $correctedTimeFrameDay = $timeFrameDay % 7;
            if ($deliveryDateArray[$correctedTimeFrameDay] == 0) {
                unset($timeframes[$key]);
            }
        }

        return array_values($timeframes);
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
     * Alias for getQuoteShippingDuration() provided for backwards compatibility reasons.
     *
     * @param null|Mage_Sales_Model_Quote $quote
     *
     * @return bool|int
     *
     * @deprecated
     */
    public function getShippingDuration(Mage_Sales_Model_Quote $quote = null)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return $this->getQuoteShippingDuration($quote);
    }

    /**
     * Gets the shipping duration for the specified quote.
     *
     * @param null|Mage_Sales_Model_Quote $quote
     *
     * @return int|false
     *
     * @throws TIG_PostNL_Exception
     */
    public function getQuoteShippingDuration(Mage_Sales_Model_Quote $quote = null)
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

        /**
         * Get all items in the quote, so we can check the corresponding products.
         *
         * @var Mage_Sales_Model_Quote_Item $item
         */
        $items = $quote->getItemsCollection();
        foreach ($items as $key => $item) {
            if ($item->isDeleted() || $item->getParentItemId()) {
                $items->removeItemByKey($key);
            }
        }
        $productIds = $items->getColumnValues('product_id');

        return $this->_getShippingDuration($configDuration, $productIds, $storeId);
    }

    /**
     * Gets the shipping duration for the specified order.
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment $entity
     *
     * @return int|false
     *
     * @throws TIG_PostNL_Exception
     */
    public function getOrderShippingDuration($entity)
    {
        if (!($entity instanceof Mage_Sales_Model_Order_Shipment || $entity instanceof Mage_Sales_Model_Order)) {
            throw new TIG_PostNL_Exception(
                $this->__('Parameter 1 needs to be an instance of Mage_Sales_Model_Order_Shipment or
                Mage_Sales_Model_Order, ' . get_class($entity) . ' was given')
            );
        }

        $storeId = $entity->getStoreId();

        /**
         * Get the default config duration.
         */
        $configDuration = (int) Mage::getStoreConfig(self::XPATH_SHIPPING_DURATION, $storeId);

        /**
         * Get all items in the order, so we can check the corresponding products.
         *
         * @var Mage_Sales_Model_Resource_Order_Item_Collection $items
         * @var Mage_Sales_Model_Order_Item $item
         */
        $items = $entity->getItemsCollection(array(), true);
        $productIds = $items->getColumnValues('product_id');

        return $this->_getShippingDuration($configDuration, $productIds, $storeId);
    }

    /**
     * Calculate the shipping duration for the specified products and default duration.
     *
     * @param int   $defaultDuration
     * @param array $productIds
     * @param null  $storeId
     *
     * @return int|false
     * @throws TIG_PostNL_Exception
     */
    protected function _getShippingDuration($defaultDuration, $productIds = array(), $storeId = null)
    {
        if (empty($productIds)) {
            $duration = new Varien_Object(
                array(
                    'duration'   => $defaultDuration,
                    'productIds' => $productIds
                )
            );

            Mage::dispatchEvent(
                'postnl_delivery_options_getshippingduration',
                array(
                    'duration' => $duration
                )
            );
            return $duration->getData('duration');
        }

        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $durationArray = $this->_getProductsShippingDuration($productIds, $defaultDuration, $storeId);

        if (empty($durationArray)) {
            $durationArray = array($defaultDuration);
        }

        /**
         * Sort the array and get it's last item. This will be the highest value.
         */
        natsort($durationArray);
        $duration = new Varien_Object(
            array(
                'duration'   => end($durationArray),
                'productIds' => $productIds
            )
        );

        Mage::dispatchEvent(
            'postnl_delivery_options_getshippingduration',
            array(
                'duration' => $duration
            )
        );

        $shippingDuration = $duration->getData('duration');

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
     * Get the shipping duration for an array of product IDs.
     *
     * @param array $productIds
     * @param int   $configDuration
     * @param null  $storeId
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function _getProductsShippingDuration(array $productIds, $configDuration = 1, $storeId = null)
    {
        /**
         * Get all products.
         */
        $products = Mage::getResourceModel('catalog/product_collection')
                        ->setStoreId($storeId)
                        ->addFieldToFilter('entity_id', array('in' => $productIds))
                        ->addAttributeToSelect('postnl_shipping_duration');

        /**
         * Get the shipping duration of all products.
         */
        $durationArray  = array();
        foreach ($products as $product) {
            if ($product->hasData('postnl_shipping_duration')
                && $product->getData('postnl_shipping_duration') !== ''
                && (int) $product->getData('postnl_shipping_duration') > 0
            ) {
                $durationArray[] = (int) $product->getData('postnl_shipping_duration');
            } else {
                $durationArray[] = $configDuration;
            }
        }

        return $durationArray;
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
     * @deprecated deprecated since version 1.7.0
     * @see TIG_PostNL_Helper_DeliveryOptions_Fee
     */
    public function getPriceWithTax($price, $includingTax, $formatted = false, $convert = true)
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return Mage::helper('postnl/deliveryOptions_fee')->getPriceWithTax($price, $includingTax, $formatted, $convert);
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
        $deliveryDate = new DateTime($deliveryDate, new DateTimeZone('UTC'));
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
     * Check whether the responsive design may be used for delivery options.
     *
     * @return bool
     */
    public function canUseResponsive()
    {
        $cache = $this->getCache();
        if ($cache && $cache->hasCanUseResponsiveDeliveryOptions()) {
            return $cache->getCanUseResponsiveDeliveryOptions();
        }

        $canUseResponsive = Mage::getStoreConfigFlag(self::XPATH_RESPONSIVE, Mage::app()->getStore()->getId());

        if ($cache) {
            $cache->setCanUseResponsiveDeliveryOptions($canUseResponsive);
        }

        return $canUseResponsive;
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
        if ($this->quoteIsBuspakje($quote)
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
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $poLocationsAllowed = $this->bundleCheckAllowedForSimpleProducts($item, 'postnl_allow_pakje_gemak');
            } else {
                $poLocationsAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                    $item->getProductId(),
                    'postnl_allow_pakje_gemak',
                    $item->getStoreId()
                );
            }

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
         * PakjeGemak is only available when sending from the Netherlands.
         */
        $senderCountry = Mage::getStoreConfig(self::XPATH_SENDER_COUNTRY, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($senderCountry != 'NL') {
            return false;
        }

        /**
         * The parent canUsePakjeGemak() method will check if any PakjeGemak product options are available.
         */
        $allowed = parent::canUsePakjeGemak();

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
        if ($this->quoteIsBuspakje($quote)
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
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $pakketautomaatAllowed = $this->bundleCheckAllowedForSimpleProducts($item, 'postnl_allow_pakketautomaat');
            } else {
                $pakketautomaatAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                    $item->getProductId(),
                    'postnl_allow_pakketautomaat',
                    $item->getStoreId()
                );
            }

            if (!is_null($pakketautomaatAllowed) && !$pakketautomaatAllowed) {
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
         * Pakketautomaat is only available when sending from the Netherlands.
         */
        $senderCountry = Mage::getStoreConfig(self::XPATH_SENDER_COUNTRY, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($senderCountry != 'NL') {
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

        if ($this->getDomesticCountry() != 'NL') {
            $allowed = false;
        } else {
            $storeId = Mage::app()->getStore()->getId();

            $allowed = Mage::getStoreConfigFlag(self::XPATH_ENABLE_DELIVERY_DAYS, $storeId);
        }

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
        if ($this->quoteIsBuspakje($quote)
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
            /**
             * If the product is a bundled product, check if the delivey options are allowed for all underlying
             * simple products. Else just check the given product, since this will point correctly to the simple product.
             */
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $deliveryDaysAllowed = $this->bundleCheckAllowedForSimpleProducts($item, 'postnl_allow_delivery_days');
            } else {
                $deliveryDaysAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                    $item->getProductId(),
                    'postnl_allow_delivery_days',
                    $item->getStoreId()
                );
            }

            if (!is_null($deliveryDaysAllowed) && !$deliveryDaysAllowed) {
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
         * If the current quote fits as a letter box parcel and the calculation mode is set to 'automatic', timeframes
         * are not allowed.
         */
        if ($this->quoteIsBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * @var Mage_Sales_Model_Quote_item $item
         */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {

            /**
             * If the product is a bundled product, check if the delivey options are allowed for all underlying
             * simple products. Else just check the given product, since this will point correctly to the simple product.
             */
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $timeframesAllowed = $this->bundleCheckAllowedForSimpleProducts($item, 'postnl_allow_timeframes');
            } else {
                $timeframesAllowed = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                    $item->getProductId(),
                    'postnl_allow_timeframes',
                    $item->getStoreId()
                );
            }

            if (!is_null($timeframesAllowed) && !$timeframesAllowed) {
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

        if ($this->getDomesticCountry() != 'NL') {
            $allowed = false;
        } else {
            $allowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_SUNDAY_SORTING, $storeId);
        }

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

        Mage::unregister($registryKey);
        Mage::unregister('postnl_delivery_options_can_use_delivery_options_errors');

        /**
         * Delivery options are only available when shipping from the Netherlands.
         */
        if ($this->getDomesticCountry() != 'NL') {
            Mage::register($registryKey, false);
            return false;
        }

        $deliveryOptionsEnabled = $this->isDeliveryOptionsEnabled();
        if (!$deliveryOptionsEnabled) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0237',
                    'message' => $this->__(
                        'Delivery options are only available when shipping from the Netherlands.'
                    ),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);

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
         * Check if the current quote is a letter box parcel order and if so, if delivery options are allowed for letter
         * box parcel orders.
         */
        if ($this->quoteIsBuspakje($quote)
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

        /**
         * Check if delivery options may be shown for the stock level of the current products.
         */
        if (!$this->canShowDeliveryOptionsForStock($quote)) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0121',
                    'message' => $this->__(
                                     'Delivery options are not allowed for one or more itme sin the cart based on the' .
                                     ' configured stock options.'
                                 ),
                )
            );
            Mage::register('postnl_delivery_options_can_use_delivery_options_errors', $errors);
            return false;
        }

        /**
         * Check if the quote contains a product for which delivery options are not allowed.
         *
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($quote->getAllVisibleItems() as $item) {
            $productId = $item->getProductId();
            $option    = $item->getOptionByCode('simple_product');
            if ($option) {
                $parentProductId = $productId;
                $productId = $option->getProduct()->getId();
            }

            /**
             * If the product is a bundled product, check if the delivey options are allowed for all underlying
             * simple products. Else just check the given product, since this will point correctly to the simple product.
             */
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $allowDeliveryOptions = $this->bundleCheckAllowedForSimpleProducts($item, 'postnl_allow_delivery_options');
            } else {
                $allowDeliveryOptions = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                    $productId,
                    'postnl_allow_delivery_options',
                    $item->getStoreId()
                );

                if ($option) {
                    $allowParentDeliveryOptions = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                        $parentProductId,
                        'postnl_allow_delivery_options',
                        $item->getStoreId()
                    );

                    if (!is_null($allowParentDeliveryOptions) && !$allowParentDeliveryOptions) {
                        $allowDeliveryOptions = $allowParentDeliveryOptions;
                    }
                }
            }

            if (!is_null($allowDeliveryOptions) && !$allowDeliveryOptions) {
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
     * Wrapper method for _canShowDeliveryOptionsForStock() to allow observers to influence the result.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function canShowDeliveryOptionsForStock(Mage_Sales_Model_Quote $quote)
    {
        /**
         * Get the configured stock option.
         */
        $stockOption = Mage::getStoreConfig(self::XPATH_STOCK_OPTIONS, $quote->getStoreId());

        Mage::dispatchEvent(
            'postnl_deliveryoptions_can_show_delivery_options_for_quote_before',
            array(
                'quote'        => $quote,
                'stock_option' => $stockOption,
                'helper'       => $this,
            )
        );

        $result = $this->_canShowDeliveryOptionsForStock($quote, $stockOption);

        $transport = new Varien_Object(array('result' => $result));

        Mage::dispatchEvent(
            'postnl_deliveryoptions_can_show_delivery_options_for_quote_after',
            array(
                'quote'        => $quote,
                'stock_option' => $stockOption,
                'helper'       => $this,
                'transport'    => $transport,
            )
        );

        return (bool) $transport->getData('result');
    }

    /**
     * Check if delivery options may be shown for the products in the quote based on their stock level and the
     * configured stock option.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $stockOption
     *
     * @return bool
     */
    protected function _canShowDeliveryOptionsForStock(Mage_Sales_Model_Quote $quote, $stockOption)
    {
        /**
         * If out of stock products is allowed, there is nothing to check.
         */
        if ($stockOption == 'out_of_stock') {
            return true;
        }

        /** @var Mage_Sales_Model_Quote_Item $item */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            /**
             * The stock check only applies to simple products.
             *
             * @todo add stock check to physicial gift cards.
             */
            $productType = $item->getProductType();
            if ($productType != 'simple') {
                continue;
            }

            $product = $item->getProduct();

            /** @var Mage_CatalogInventory_Model_Stock_item $stockItem */
            /** @noinspection PhpUndefinedMethodInspection */
            $stockItem = $product->getStockItem();

            if (!$stockItem) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            }

            $available = false;
            switch ($stockOption) {
                case 'in_stock':
                    $available = $this->_isStockItemInStock($stockItem, false, $item->getQty());
                    break;
                case 'backordered':
                    $available = $this->_isStockItemInStock($stockItem, true, $item->getQty());
                    break;
            }

            if (true !== $available) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the stock item is in stock.
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     * @param bool                                   $allowBackorders
     * @param int                                    $requiredQty
     *
     * @return bool
     */
    protected function _isStockItemInStock(Mage_CatalogInventory_Model_Stock_Item $stockItem, $allowBackorders = false,
                                           $requiredQty = 1)
    {
        /**
         * Get several config values determining stock status.
         */
        $configMinQty      = $this->getConfigMinQty();
        $configBackorders  = $this->getConfigBackorders();
        $configManageStock = $this->getConfigManageStock();

        /**
         * If stock is not managed for this product, it is always in stock.
         */
        if ($stockItem->getUseConfigManageStock() && !$configManageStock) {
            return true;
        } elseif (!$stockItem->getManageStock()) {
            return true;
        }

        /**
         * If backorders are allowed for this product, it is always in stock.
         */
        if ($allowBackorders && $stockItem->getUseConfigBackorders() && $configBackorders > 0) {
            return true;
        } elseif ($allowBackorders && $stockItem->getBackorders() > 0) {
            return true;
        }

        /**
         * Get the minimum quantity for this product.
         */
        if (!$stockItem->getUseConfigMinQty()) {
            $minQty = $stockItem->getMinQty();
        } else {
            $minQty = $configMinQty;
        }

        /**
         * Check if the product has the required qty available.
         */
        if (($stockItem->getQty() - $minQty) < $requiredQty) {
            return false;
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
         *
         * Delivery options in Belgium are currently unstable and therefor not yet fully supported. Expect this to be
         * added in a later release.
         *
         * @todo add Belgium as a valid country for PostNL delivery options.
         */
        if ($shippingAddress->getCountry() != 'NL' /*&& $shippingAddress->getCountry() != 'BE'*/) {
            Mage::register($registryKey, false);
            return false;
        }

        Mage::register($registryKey, true);
        return true;
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
     * Check whether showing the 'only_stated_address' option is allowed.
     *
     * @param boolean $checkQuote
     *
     * @return boolean
     */
    public function canShowOnlyStatedAddressOption($checkQuote = true)
    {
        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_only_stated_address_option';

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
            $canUseForQuote = $this->_canShowOnlyStatedAddressOptionForQuote();

            if (!$canUseForQuote) {
                Mage::register($registryKey, false);
                return false;
            }
        }

        $cache = $this->getCache();

        if ($cache && $cache->hasCanShowOnlyStatedAddressOption()) {
            /**
             * Check if the result of this method has been cached in the PostNL cache.
             */
            $allowed = $cache->getCanShowOnlyStatedAddressOption();

            Mage::register($registryKey, $allowed);
            return $allowed;
        }

        $allowed = $this->_canShowOnlyStatedAddressOption();

        if ($cache) {
            /**
             * Save the result in the PostNL cache.
             */
            $cache->setCanShowOnlyStatedAddressOption($allowed)
                  ->saveCache();
        }

        Mage::register($registryKey, $allowed);
        return $allowed;
    }

    /**
     * Check if the 'only_stated_address' option can be shown for the current quote.
     *
     * @return bool
     */
    protected function _canShowOnlyStatedAddressOptionForQuote()
    {
        $quote = $this->getQuote();

        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'can_show_only_stated_address_option_for_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * This option is only available for Dutch shipments.
         */
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getCountryId() != 'NL') {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * This shipment cannot be used for buspakje shipments.
         */
        if ($this->quoteIsBuspakje($quote)) {
            Mage::register($registryKey, false);
            return false;
        }

        Mage::register($registryKey, true);
        return true;
    }

    /**
     * Check if the 'only_stated_address' option can be shown for the current config.
     *
     * @return bool
     */
    protected function _canShowOnlyStatedAddressOption()
    {
        $showOption = Mage::getStoreConfigFlag(
            self::XPATH_STATED_ADDRESS_ONLY_OPTION,
            Mage::app()->getStore()->getId()
        );

        if (!$showOption) {
            return false;
        }

        /**
         * Check if any valid product options are available.
         */
        $statedAddressOnlyOptions = Mage::getSingleton('postnl_core/system_config_source_allProductOptions')
                                        ->getOptions(array('statedAddressOnly' => true), true, true);

        if (empty($statedAddressOnlyOptions)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the 'only_stated_address' option should be checked by default.
     *
     * @return bool
     */
    public function isOnlyStatedAddressOptionChecked()
    {
        $isOnlyStatedAddressOptionChecked = Mage::getStoreConfigFlag(
            self::XPATH_STATED_ADDRESS_ONLY_CHECKED,
            Mage::app()->getStore()->getId()
        );

        return $isOnlyStatedAddressOptionChecked;
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

        $checkoutExtension = Mage::getStoreConfig(
            TIG_PostNL_Helper_AddressValidation::XPATH_CHECKOUT_EXTENSION,
            $storeId
        );

        if (!$checkoutExtension || $checkoutExtension == 'other') {
            return false;
        }

        $isActive = Mage::getStoreConfigFlag(self::XPATH_DELIVERY_OPTIONS_ACTIVE, $storeId);

        return $isActive;
    }

    /**
     * Check if the GoMage LightCheckout delivery date functionality conflicts with PostNl delivery options.
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function checkGoMageDeliveryDateConflicts($storeId = null)
    {
        /**
         * Check if the GoMage LightCheckout extension is installed and is active.
         */
        $goMageLightCheckoutIsInstalled = $this->isModuleEnabled('GoMage_Checkout');
        if (!$goMageLightCheckoutIsInstalled) {
            return false;
        }

        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        /**
         * Check if the GoMage LightCheckout extension is enabled.
         */
        $goMageLightCheckoutIsEnabled = Mage::getStoreConfigFlag('gomage_checkout/general/enabled', $storeId);
        if (!$goMageLightCheckoutIsEnabled) {
            return false;
        }

        /**
         * Check if the GoMage LightCheckout extension's delivery date functionality is enabled.
         */
        $goMageDeliveryDateIsEnabled = Mage::getStoreConfigFlag('gomage_checkout/deliverydate/deliverydate', $storeId);
        if (!$goMageDeliveryDateIsEnabled) {
            return false;
        }

        /**
         * Check for which shipping methods the GoMage LightCheckout extension's delivery date functionality is enabled.
         */
        $goMageDeliveryDateShippingMethods = Mage::getStoreConfig(
            'gomage_checkout/deliverydate/shipping_methods',
            $storeId
        );
        $goMageDeliveryDateShippingMethods = explode(',', $goMageDeliveryDateShippingMethods);

        /**
         * Check if this intersects with the PostNL shipping methods.
         *
         * N.B. The PostNL shipping methods are not dependant on the selected store view.
         */
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

        $conflictingShippingMethods = array_intersect($goMageDeliveryDateShippingMethods, $postnlShippingMethods);
        if (empty($conflictingShippingMethods)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @param string $attribute
     *
     * @return bool
     */
    public function bundleCheckAllowedForSimpleProducts($item, $attribute)
    {
        $bundleItems = $item->getQtyOptions();

        /**
         * By default, the delivery options can be shown for the quote, unless a product states otherwise.
         */
        $allow = true;
        /** @var Mage_Sales_Model_Quote_Item_Option $bundleItem */
        foreach ($bundleItems as $bundleItem) {
            $productId = $bundleItem->getProductId();
            $allowDeliveryOptionsForBundleItem = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $productId,
                $attribute,
                $item->getStoreId()
            );
            if ($allowDeliveryOptionsForBundleItem == 0) {
                $allow = false;
            }
        }

        return $allow;

    }
}
