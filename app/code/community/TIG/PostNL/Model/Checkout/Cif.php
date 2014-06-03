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
 * @method TIG_PostNL_Model_Checkout_Cif setStoreId(int $value)
 */
class TIG_PostNL_Model_Checkout_Cif extends TIG_PostNL_Model_Core_Cif_Abstract
{
    /**
     * Webshop ID config option path
     */
    const XML_PATH_WEBSHOP_ID = 'postnl/cif/webshop_id';

    /**
     * XML paths for various options
     */
    const XML_PATH_NEWSLETTER_SUBSCRIPTION = 'postnl/checkout/newsletter_subscription';
    const XML_PATH_REMARK                  = 'postnl/checkout/remark';
    const XML_PATH_CONTACT_URL             = 'postnl/checkout/contact_url';
    const XML_PATH_ALLOW_RETAIL_LOCATION   = 'postnl/checkout/allow_retail_location';
    const XML_PATH_ALLOW_FOREIGN_ADDRESS   = 'postnl/checkout/allow_foreign_address';
    const XML_PATH_ALLOW_PRICE_OVERVIEW    = 'postnl/checkout/allow_price_overview';
    const XML_PATH_AGREE_CONDITIONS        = 'postnl/checkout/agree_conditions';
    const XML_PATH_SERVICE_URL             = 'postnl/checkout/service_url';
    const XML_PATH_USE_MOBILE              = 'postnl/checkout/use_mobile';
    const XML_PATH_USE_DOB                 = 'postnl/checkout/use_dob';

    /**
     * XML path to available payment methods.
     * N.B. missing last part so it will return an array of settings.
     */
    const XML_PATH_CHECKOUT_PAYMENT_METHODS = 'postnl/checkout_payment_methods';

    /**
     * XML paths for shipment reference info
     */
    const XML_PATH_SHIPMENT_REFERENCE_TYPE   = 'postnl/cif_labels_and_confirming/shipment_reference_type';
    const XML_PATH_CUSTOM_SHIPMENT_REFERENCE = 'postnl/cif_labels_and_confirming/custom_shipment_reference';

    /**
     * Check if the module is set to test mode
     *
     * @see TIG_PostNL_Helper_Checkout::isTestMode()
     *
     * @param bool $storeId
     *
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        if ($storeId === false) {
            $storeId = $this->getStoreId();
        }

        $testMode = Mage::helper('postnl/checkout')->isTestMode($storeId);

        return $testMode;
    }

    /**
     * Gets the current store Id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->hasStoreId()) {
            return $this->getData('store_id');
        }

        $storeId = Mage::app()->getStore()->getId();

        $this->setStoreId($storeId);
        return $storeId;
    }

    /**
     * Checks if the PostNL service is available
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function ping()
    {
        $response = $this->call(
            'checkout',
            'PingStatus'
        );

        if (!is_object($response)
            || !isset($response->Status)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid PingStatus response: %s', "\n" . var_export($response, true)),
                'POSTNL-0038'
            );
        }

        return $response->Status;
    }

    /**
     * Prepares a new PostNL checkout order
     *
     * @param null|Mage_Sales_Model_Quote $quote
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function prepareOrder($quote = null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        if (!$quote) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No quote available to initiate PostNL Checkout.'),
                'POSTNL-0039'
            );
        }

        $this->setStoreId($quote->getStoreId());

        /**
         * Get all data required to form the SOAP request
         */
        $paymentMethods      = $this->_getPaymentMethods();
        $communictionOptions = $this->_getCommunicationOptions();
        $customer            = $this->_getCustomer();
        $optional            = $this->_getOptional();
        $contact             = $this->_getContact();
        $service             = $this->_getService();
        $order               = $this->_getOrder($quote);
        $restrictions        = $this->_getRestrictions();
        $webshop             = $this->_getWebshop();

        $soapParams = array(
            'Order'   => $order,
            'Webshop' => $webshop,
        );

        if (!empty($paymentMethods)) {
            $soapParams['AangebodenBetaalMethoden'] = $paymentMethods;
        }

        if (!empty($communictionOptions)) {
            $soapParams['AangebodenCommunicatieOpties'] = $communictionOptions;
        }

        if (!empty($restrictions)) {
            $soapParams['Restrictions'] = $restrictions;
        }

        if ($customer) {
            $soapParams['Consument'] = $customer;
        }

        if (!empty($optional)) {
            $soapParams['Optional'] = $optional;
        }

        if ($contact) {
            $soapParams['Contact'] = $contact;
        }

        if ($service) {
            $soapParams['Service'] = $service;
        }

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'checkout',
            'PrepareOrder',
            $soapParams
        );

        if (!is_object($response)
            || !isset($response->Checkout)
            || !is_object($response->Checkout)
            || !isset($response->Checkout->OrderToken)
        ) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid PrepareOrder response: %s', "\n" . var_export($response, true)),
                'POSTNL-0040'
            );
        }

        return $response;
    }

    /**
     * Retrieves the data the customer entered for this quote
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @throws TIG_PostNL_Exception
     *
     * @return StdClass
     */
    public function readOrder($quote =  null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        if (!$quote) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('No quote available to initiate PostNL Checkout.'),
                'POSTNL-0039'
            );
        }

        $this->setStoreId($quote->getStoreId());

        $checkout = $this->_getCheckout($quote);
        $webshop  = $this->_getWebshop();

        $soapParams = array(
            'Checkout' => $checkout,
            'Webshop'  => $webshop,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'checkout',
            'ReadOrder',
            $soapParams
        );

        if (!is_object($response)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid ReadOrder response: %s', "\n" . var_export($response, true)),
                'POSTNL-0041'
            );
        }

        return $response;
    }

    /**
     * Confirms the PostNL order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     *
     * @throws TIG_PostNL_Exception
     *
     * @return StdClass
     */
    public function confirmOrder($postnlOrder)
    {
        $checkout = $this->_getCheckout($postnlOrder);
        $order    = $this->_getConfirmOrder($postnlOrder);
        $webshop  = $this->_getWebshop();

        $soapParams = array(
            'Checkout' => $checkout,
            'Order'    => $order,
            'Webshop'  => $webshop,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'checkout',
            'ConfirmOrder',
            $soapParams
        );

        if (!is_object($response)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid ConfirmOrder response: %s', "\n" . var_export($response, true)),
                'POSTNL-0042'
            );
        }

        return $response;
    }

    /**
     * Updates an order with CIF once a shipment has been confirmed in order to link the shipment to the PostNL Checkout order
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param boolean $cancel
     *
     * @return StdClass
     *
     * @throws TIG_PostNL_Exception
     */
    public function updateOrder($postnlOrder, $cancel = false)
    {
        $this->setStoreId($postnlOrder->getOrder()->getStoreId());

        $order   = $this->_getUpdateOrder($postnlOrder, $cancel);
        $webshop = $this->_getWebshop();

        $soapParams = array(
            'Order'   => $order,
            'Webshop' => $webshop,
        );

        /**
         * Send the SOAP request
         */
        $response = $this->call(
            'checkout',
            'UpdateOrder',
            $soapParams
        );

        if (!is_object($response)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid UpdateOrder response: %s', var_export($response, true)),
                'POSTNL-0097'
            );
        }

        return $response;
    }

    /**
     * Gets a list of allowed payment methods
     *
     * @return array
     */
    protected function _getPaymentMethods()
    {
        $storeId = $this->getStoreId();

        /**
         * Get all payment method configuration options as well as an array of all payment method supported by PostNL
         */
        $paymentMethods = Mage::getStoreConfig(self::XML_PATH_CHECKOUT_PAYMENT_METHODS, $storeId);
        $postnlPaymentMethods = Mage::helper('postnl/checkout')->getCheckoutPaymentMethods();

        $allowedMethods = array();
        foreach ($paymentMethods as $method => $value) {
            /**
             * The $postnlPaymentMethods array uses the configuration option names as keys. So if $method exists as a key in
             * $postnlPaymentMethods it's a valid payment method. We then check if it's enabled by checking $value.
             */
            if (!array_key_exists($method, $postnlPaymentMethods) || !$value) {
                continue;
            }

            $allowedMethods[] = array(
                'Code'  => $postnlPaymentMethods[$method],
                'Prijs' => '0.00', //additional fees are not supported
            );
        }

        return $allowedMethods;
    }

    /**
     * Gets a list of allowed communication options
     *
     * @return array
     */
    protected function _getCommunicationOptions()
    {
        $storeId = $this->getStoreId();

        $communicationOptions = array();

        $newsletterSubscription = Mage::getStoreConfigFlag(self::XML_PATH_NEWSLETTER_SUBSCRIPTION, $storeId);
        if ($newsletterSubscription) {
            $communicationOptions[] = array(
                'Code' => 'NEWS',
            );
        }

        $remark = Mage::getStoreConfigFlag(self::XML_PATH_REMARK, $storeId);
        if ($remark) {
            $communicationOptions[] = array(
                'Code' => 'REMARK',
            );
        }

        return $communicationOptions;
    }

    /**
     * Gets the customer ID if the customer is logged in
     *
     * @return boolean | array
     */
    protected function _getCustomer()
    {
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            return false;
        }

        $customerId = $session->getCustomerId();
        $customer = array(
            'ExtRef' => $customerId,
        );

        return $customer;
    }

    /**
     * Gets two optional fields. Both default to false.
     *
     * @return array
     */
    protected function _getOptional()
    {
        $storeId = $this->getStoreId();

        $optional =
            array(
                'MobileNumber' => 'False',
                'BirthDate' => 'False',
            );

        $useMobile = Mage::getStoreConfigFlag(self::XML_PATH_USE_MOBILE, $storeId);
        if ($useMobile) {
            $optional['MobileNumber'] = 'True';
        }

        $useDob = Mage::getStoreConfigFlag(self::XML_PATH_USE_DOB, $storeId);
        if ($useDob) {
            $optional['BirthDate'] = 'True';
        }

        return $optional;
    }

    /**
     * Gets an optional URL of a page where customers can find contact info for this webshop
     *
     * @return boolean | array
     */
    protected function _getContact()
    {
        $storeId = $this->getStoreId();

        $contactUrl = Mage::getStoreConfig(self::XML_PATH_CONTACT_URL, $storeId);
        if (!$contactUrl) {
            return false;
        }

        $contact = array(
            'Url' => $contactUrl,
        );

        return $contact;
    }

    /**
     * Builds the Order soap object based on the current quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    protected function _getOrder(Mage_Sales_Model_Quote $quote)
    {
        /**
         * @var Mage_Sales_Model_Quote_Address $shippingAddress
         */
        $shippingAddress = $quote->getShippingAddress();
        $baseSubtotalIncltax = $shippingAddress->getBaseSubtotalTotalInclTax();
        if ($baseSubtotalIncltax === null) {
            $baseSubtotalIncltax = $shippingAddress->getBaseSubtotalWithDiscount()
                                 + $shippingAddress->getBaseTaxAmount()
                                 - $shippingAddress->getBaseShippingTaxAmount();
        }

        $baseShippingAmount = $shippingAddress->getBaseShippingInclTax();
        if ($baseShippingAmount === null) {
            $baseShippingAmount = $shippingAddress->getBaseShippingAmount()
                                + $shippingAddress->getBaseShippingTaxAmount();
        }

        $extRef        = $quote->getId();
        $orderDate     = date('d-m-Y H:i:s', Mage::getModel('core/date')->timestamp());
        $subtotal      = round($baseSubtotalIncltax, 2);
        $shippingDate  = $orderDate;
        $shippingCosts = round($baseShippingAmount, 2);

        $order = array(
            'ExtRef'        => $extRef,
            'OrderDatum'    => $orderDate,
            'Subtotaal'     => number_format($subtotal, 2, '.', ''),
            'VerzendDatum'  => $shippingDate,
            'VerzendKosten' => number_format($shippingCosts, 2, '.', ''),
        );

        return $order;
    }

    /**
     * Builds the confirmOrder Order soap object based on the current postnl order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     *
     * @return array
     */
    protected function _getConfirmOrder($postnlOrder)
    {
        $order = $postnlOrder->getOrder();

        $paymentTotal      = round($order->getBaseGrandTotal());
        $extRef            = $order->getIncrementId();
        $paymentMethodName = $order->getPayment()->getMethodInstance()->getTitle();

        $confirmOrder = array(
            'PaymentTotal'      => number_format($paymentTotal, 2, '.', ''),
            'ExtRef'            => $extRef,
            'PaymentMethodName' => $paymentMethodName,
        );

        return $confirmOrder;
    }

    /**
     * Builds the updateOrder Order soap object based on the current postnl order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param boolean $cancel
     *
     * @return array
     */
    protected function _getUpdateOrder($postnlOrder, $cancel = false)
    {
        $order = $postnlOrder->getOrder();

        $extRef   = $order->getIncrementId();
        $shipment = $this->_getShipments($postnlOrder);

        $updateOrder = array(
            'ExtRef'  => $extRef,
        );

        if (!empty($shipment)) {
            $updateOrder['Zending'] = $shipment;
        }

        if ($cancel) {
            $updateOrder['Geannuleerd'] = 'true';
        }

        return $updateOrder;
    }

    /**
     * Gets a list of shipments associated with a PostNL order
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     *
     * @return array
     */
    protected function _getShipments($postnlOrder)
    {
        $order = $postnlOrder->getOrder();
        $shipments = $order->getShipmentsCollection();

        $shipmentData = array();
        foreach ($shipments as $shipment) {
            $shipmentData[] = array(
                'ExtRef' => $this->_getReference($shipment),
                'Pakket' => $this->_getParcels($shipment),
            );
        }

        return $shipmentData;
    }

    /**
     * Get a shipment's reference. By default this will be the shipment's increment ID
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     *
     * @todo merge this with TIG_PostNL_Model_Core_Cif::_getReference()
     */
     protected function _getReference($shipment)
     {
         $storeId = $this->getStoreId();
         $referenceType = Mage::getStoreConfig(self::XML_PATH_SHIPMENT_REFERENCE_TYPE, $storeId);

         /**
          * Parse the reference type
          */
         switch ($referenceType) {
             case '': //no break
             case 'none':
                 $reference = '';
                 break;
             case 'shipment_increment_id':
                 $reference = $shipment->getIncrementId();
                 break;
             case 'order_increment_id':
                 $reference = $shipment->getOrder()->getIncrementId();
                 break;
             case 'custom':
                 $reference = Mage::getStoreConfig(self::XML_PATH_CUSTOM_SHIPMENT_REFERENCE, $storeId);
                 break;
             default:
                 throw new TIG_PostNL_Exception(
                     Mage::helper('postnl')->__('Invalid reference type requested: %s', $referenceType),
                     'POSTNL-0043'
                 );
         }

         /**
          * For custom references we need to replace several optional variables
          */
         if ($referenceType == 'custom') {
             $reference = str_replace('{{var shipment_increment_id}}', $shipment->getIncrementId(), $reference);
             $reference = str_replace('{{var order_increment_id}}', $shipment->getOrder()->getIncrementId(), $reference);

             $store = Mage::getModel('core/store')->load($storeId);
             $reference = str_replace('{{var store_frontend_name}}', $store->getFrontendName(), $reference);
         }

         return $reference;
     }

    /**
     * Gets a list of parcels associated with a shipment
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return array
     */
    protected function _getParcels($shipment)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getid(), 'shipment_id');
        $parcelCount = $postnlShipment->getParcelCount();

        $parcelData = array();
        $postcode = $shipment->getShippingAddress()->getPostcode();
        for ($i = 0; $i < $parcelCount; $i++) {
            $parcelData[] = array(
                'Barcode'  => $postnlShipment->getBarcode($i),
                'Postcode' => $postcode,
            );
        }

        return $parcelData;
    }

    /**
     * Builds the Restrictions soap object based on config settings
     *
     * @return array
     */
    protected function _getRestrictions()
    {
        $storeId = $this->getStoreId();

        $restrictions = array();

        $retailLocation  = Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_RETAIL_LOCATION, $storeId);
        $foreignAddress  = Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_FOREIGN_ADDRESS, $storeId);
        $priceOverview   = Mage::getStoreConfigFlag(self::XML_PATH_ALLOW_PRICE_OVERVIEW, $storeId);
        $agreeConditions = Mage::getStoreConfigFlag(self::XML_PATH_AGREE_CONDITIONS, $storeId);

        /**
         * If the module cannot use PakjeGemak, retail locations are not allowed in PostNL Checkout
         */
        if (!$retailLocation
            || !Mage::helper('postnl')->canUsePakjeGemak()
        ) {
            $restrictions['NoRetailLocation'] = 'true';
        } else {
            $restrictions['NoRetailLocation'] = 'false';
        }

        /**
         * If the module cannot use EPS, foreign addresses are not allowed in PostNL Checkout
         */
        if (!$foreignAddress
            || !Mage::helper('postnl')->canUseEps()
        ) {
            $restrictions['NoForeignAddress'] = 'true';
        } else {
            $restrictions['NoForeignAddress'] = 'false';
        }

        if (!$priceOverview) {
            $restrictions['NoPriceOverview'] = 'true';
        } else {
            $restrictions['NoPriceOverview'] = 'false';
        }

        if (!$agreeConditions) {
            $restrictions['NoAgreeConditions'] = 'true';
        } else {
            $restrictions['NoAgreeConditions'] = 'false';
        }

        return $restrictions;
    }

    /**
     * Gets an URL linking to the webshop's service info
     *
     * @return boolean|array
     */
    protected function _getService()
    {
        $storeId = $this->getStoreId();

        $serviceUrl = Mage::getStoreConfig(self::XML_PATH_SERVICE_URL, $storeId);
        if (!$serviceUrl) {
            return false;
        }

        $service = array(
            'Url' => $serviceUrl,
        );

        return $service;
    }

    /**
     * Gets the order token used to identify a PostNL order
     *
     * @param Mage_Sales_Model_Quote|TIG_PostNL_Model_Core_Order $object
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getCheckout($object)
    {
        if ($object instanceof Mage_Sales_Model_Quote) {
            $postnlOrder = Mage::getModel('postnl_core/order')->load($object->getId(), 'quote_id');
        } elseif ($object instanceof TIG_PostNL_Model_Core_Order) {
            $postnlOrder = $object;
        } else {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid object specified: %s', get_class($object)),
                'POSTNL-0044'
            );
        }

        $orderToken = $postnlOrder->getToken();
        if (!$orderToken) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('OrderToken missing for quote #%s', $postnlOrder->getQuoteId()),
                'POSTNL-0045'
            );
        }

        $checkout = array(
            'OrderToken' => $orderToken,
        );

        return $checkout;
    }

    /**
     * Gets the webshop ID for the current store
     *
     * @return array
     */
    protected function _getWebshop()
    {
        $storeId = $this->getStoreId();

        $webshopId = Mage::getStoreConfig(self::XML_PATH_WEBSHOP_ID, $storeId);
        $webshopId = Mage::helper('core')->decrypt($webshopId);

        $webshop = array(
            'IntRef' => $webshopId,
        );

        return $webshop;
    }
}
