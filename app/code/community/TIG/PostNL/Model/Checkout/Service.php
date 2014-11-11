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
 * @method TIG_PostNL_Model_Checkout_Service setQuote(Mage_Sales_Model_Quote $value)
 * @method TIG_PostNL_Model_Checkout_Service setStoreId(int $value)
 * @method int                               getStoreId()
 */
class TIG_PostNL_Model_Checkout_Service extends Varien_Object
{
    /**
     * XML path to public webshop ID setting
     */
    const XPATH_WEBSHOP_ID = 'postnl/cif/webshop_id';

    /**
     * Constants containing XML paths to cif address configuration options
     */
    const XPATH_SPLIT_STREET                = 'postnl/cif_labels_and_confirming/split_street';
    const XPATH_STREETNAME_FIELD            = 'postnl/cif_labels_and_confirming/streetname_field';
    const XPATH_HOUSENUMBER_FIELD           = 'postnl/cif_labels_and_confirming/housenr_field';
    const XPATH_SPLIT_HOUSENUMBER           = 'postnl/cif_labels_and_confirming/split_housenr';
    const XPATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_labels_and_confirming/housenr_extension_field';
    const XPATH_AREA_FIELD                  = 'postnl/cif_labels_and_confirming/area_field';
    const XPATH_BUILDING_NAME_FIELD         = 'postnl/cif_labels_and_confirming/building_name_field';
    const XPATH_DEPARTMENT_FIELD            = 'postnl/cif_labels_and_confirming/department_field';
    const XPATH_DOORCODE_FIELD              = 'postnl/cif_labels_and_confirming/doorcode_field';
    const XPATH_FLOOR_FIELD                 = 'postnl/cif_labels_and_confirming/floor_field';
    const XPATH_REMARK_FIELD                = 'postnl/cif_labels_and_confirming/remark_field';

    /**
     * XML path to all PostNL Checkout payment settings
     * N.B. missing last part os it will return an array of settings
     */
    const XPATH_PAYMENT_METHODS = 'postnl/checkout_payment_methods';

    /**
     * Newly added 'pakje_gemak' address type
     */
    const ADDRESS_TYPE_PAKJEGEMAK = 'pakje_gemak';

    /**
     * Gets the currently used quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->getData('quote')) {
            return $this->getData('quote');
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $this->setQuote($quote);
        return $quote;
    }

    /**
     * Updates a quote with the given PostNL order data. Each part of the data is used to replace the data normally
     * acquired during checkout.
     *
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote | null $quote
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function updateQuoteAddresses($data, $quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        $this->setStoreId($quote->getStoreId());

        $this->_verifyData($data, $quote);

        /**
         * Get consumer data
         */
        $consumer = $data->Consument;
        $email = $consumer->Email;
        $phone = $consumer->TelefoonNummer;
        if (!$phone) {
            $phone = '-';
        } else {
            $phone = preg_replace("/[^0-9]/", '', $phone);
        }

        /**
         * Remove all existing addresses, we're going to add new ones
         */
        $this->_removeAllQuoteAddresses($quote);

        /**
         * Parse the shippingaddresses
         */
        $delivery = $data->Bezorging;
        $shippingAddressData = $delivery->Geadresseerde;

        $shippingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress->setAddressType($shippingAddress::TYPE_SHIPPING)
                        ->setEmail($email)
                        ->setTelephone($phone);

        $shippingAddress = $this->_parseAddress($shippingAddress, $shippingAddressData);

        $shippingMethod = Mage::helper('postnl/carrier')->getCurrentPostnlShippingMethod();
        if (!$shippingAddress->getShippingMethod()) {
            $shippingAddress->setCollectShippingRates(true)
                            ->setShippingMethod($shippingMethod);
        }

        /**
         * Parse the billing address
         */
        $billingAddressData = $data->Facturatie->Adres;
        $billingAddress = Mage::getModel('sales/quote_address');
        $billingAddress->setAddressType($billingAddress::TYPE_BILLING)
                       ->setEmail($email)
                       ->setTelephone($phone);

        $billingAddress = $this->_parseAddress($billingAddress, $billingAddressData);

        /**
         * If a servicelocation was set, add that as a third address
         */
        if (isset($delivery->ServicePunt)) {
            $serviceLocationData = $delivery->ServicePunt;
            $pakjeGemakAddress = Mage::getModel('sales/quote_address');
            $pakjeGemakAddress->setAddressType(self::ADDRESS_TYPE_PAKJEGEMAK)
                              ->setEmail($email)
                              ->setTelephone($phone);

            $pakjeGemakAddress = $this->_parseAddress($pakjeGemakAddress, $serviceLocationData);

            $quote->addAddress($pakjeGemakAddress);

            /**
             * Register that this is a PakjeGemak order
             */
            Mage::register('quote_is_pakje_gemak', 1);
        }

        /**
         * Update the quote's addresses
         */
        $quote->setCustomerEmail($email)
              ->setShippingAddress($shippingAddress)
              ->setBillingAddress($billingAddress)
              ->collectTotals()
              ->save();

        return $this;
    }

    /**
     * Updates a quote with the given payment data (from PostNL or magento).
     *
     * @param mixed $data
     * @param boolean $isOrderdetails Flag whether or not the supplied data was sent by PostNL and not by magento
     * @param boolean $methodOnly Flag whether or not to only set the payment method. If false, all data will be set
     *                            for the chosen payment method.
     * @param Mage_Sales_Model_Quote|null $quote
     *
     * @return $this
     */
    public function updateQuotePayment($data, $isOrderdetails = true, $methodOnly = false, $quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        $this->setStoreId($quote->getStoreId());

        /**
         * If the payment data is sent by PostNL we need to process it accordingly
         */
        if ($isOrderdetails) {
            $this->_verifyData($data, $quote);
            $this->_processPostnlPaymentData($data, $methodOnly, $quote);

            return $this;
        }

        /**
         * Otherwise, we need to process the data as we would with a regular checkout procedure
         */
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        /**
         * shipping totals may be affected by payment method
         */
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        /**
         * Extra checks used by Magento
         *
         * @var $paymentMethodAbstractClass Mage_Payment_Model_Method_Abstract
         *
         * @since Magento v1.13
         */
        $paymentMethodAbstractClass = Mage::getConfig()->getModelClassName('payment/method_abstract');
        if (defined($paymentMethodAbstractClass . '::CHECK_USE_CHECKOUT')
            && defined($paymentMethodAbstractClass . '::CHECK_USE_FOR_COUNTRY')
            && defined($paymentMethodAbstractClass . '::CHECK_USE_FOR_CURRENCY')
            && defined($paymentMethodAbstractClass . '::CHECK_ORDER_TOTAL_MIN_MAX')
            && defined($paymentMethodAbstractClass . '::CHECK_ZERO_TOTAL')
        ) {
            $data['checks'] = $paymentMethodAbstractClass::CHECK_USE_CHECKOUT
                            | $paymentMethodAbstractClass::CHECK_USE_FOR_COUNTRY
                            | $paymentMethodAbstractClass::CHECK_USE_FOR_CURRENCY
                            | $paymentMethodAbstractClass::CHECK_ORDER_TOTAL_MIN_MAX
                            | $paymentMethodAbstractClass::CHECK_ZERO_TOTAL;
        }

        $paymentDataObject = new Varien_Object();
        $paymentDataObject->setPaymentData($data);

        Mage::dispatchEvent(
            'postnl_checkout_set_payment_before',
            array(
                'payment'             => $quote->getPayment(),
                'quote'               => $quote,
                'payment_data_object' => $paymentDataObject,
            )
        );

        $paymentData = $paymentDataObject->getPaymentData();

        $quote->getPayment()->setMethod($data['method'])->importData($paymentData);
        $quote->getPayment()->getMethodInstance()->assignData($paymentData);

        Mage::dispatchEvent(
            'postnl_checkout_set_payment_after',
            array(
                'payment'             => $quote->getPayment(),
                'quote'               => $quote,
            )
        );

        $quote->save();

        return $this;
    }

    /**
     * Processes PostNL payment data
     *
     * @param StdClass $data
     * @param boolean $methodOnly
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _processPostnlPaymentData($data, $methodOnly, $quote)
    {
        /**
         * Get the payment data PostNL supplied
         */
        $postnlPaymentData = $data->BetaalMethode;

        /**
         * Check if the plugin supports the chosen payment method
         */
        $postnlPaymentMethods = Mage::helper('postnl/checkout')->getCheckoutPaymentMethods();
        $methodName = array_search($postnlPaymentData->Code, $postnlPaymentMethods);

        /**
         * Check if the payment method chosen is allowed
         */
        if (!Mage::getStoreConfigFlag(self::XPATH_PAYMENT_METHODS . '/' . $methodName, $quote->getStoreId())) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Selected payment method %s is not available.', $methodName),
                'POSTNL-0048'
            );
        }

        if ($methodOnly === true) {
            $this->_processPaymentMethod($methodName, $quote);
            return $this;
        }

        $this->_processPaymentData($postnlPaymentData, $methodName, $quote);
        return $this;
    }

    /**
     * Process a chosen payment method
     *
     * @param string $methodName
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return $this
     */
    protected function _processPaymentMethod($methodName, $quote)
    {
        /**
         * Get the Magento payment method code associated with this method
         */
        $methodCode = Mage::getStoreConfig(
                          self::XPATH_PAYMENT_METHODS . '/' . $methodName . '_method',
                          $quote->getStoreId()
        );
        Mage::register('postnl_payment_data', array('method' => $methodCode));

        /**
         * Remove any current payment associated with the quote and get a new one
         */
        $payment = $quote->removePayment()
                         ->getPayment();

        $payment->setMethod($methodCode);
        $quote->save();

        return $this;
    }

    /**
     * Process a chosen payment method with extra payment data
     *
     * @param StdClass $postnlPaymentData
     * @param string $methodName
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return $this
     */
    protected function _processPaymentData($postnlPaymentData, $methodName, $quote)
    {
        /**
         * Otherwise we need to form the payment data array containing all relevant payment data
         */
        $paymentData = Mage::app()->getRequest()->getPost('payment', array());

        $optionValue = $postnlPaymentData->Optie;

        /**
         * Get the payment method code associated with the chosen payment method
         */
        $methodCode = Mage::getStoreConfig(
                          self::XPATH_PAYMENT_METHODS . '/' . $methodName . '_method',
                          $quote->getStoreId()
        );

        /**
         * Extra checks used by Magento
         *
         * @since Magento CE v1.7
         * @since Magento EE v1.13
         *
         * @var $paymentMethodAbstractClass Mage_Payment_Model_Method_Abstract
         */
        $paymentMethodAbstractClass = Mage::getConfig()->getModelClassName('payment/method_abstract');
        if (defined($paymentMethodAbstractClass . '::CHECK_USE_CHECKOUT')
            && defined($paymentMethodAbstractClass . '::CHECK_USE_FOR_COUNTRY')
            && defined($paymentMethodAbstractClass . '::CHECK_USE_FOR_CURRENCY')
            && defined($paymentMethodAbstractClass . '::CHECK_ORDER_TOTAL_MIN_MAX')
            && defined($paymentMethodAbstractClass . '::CHECK_ZERO_TOTAL')
        ) {
            $paymentData['checks'] = $paymentMethodAbstractClass::CHECK_USE_CHECKOUT
                            | $paymentMethodAbstractClass::CHECK_USE_FOR_COUNTRY
                            | $paymentMethodAbstractClass::CHECK_USE_FOR_CURRENCY
                            | $paymentMethodAbstractClass::CHECK_ORDER_TOTAL_MIN_MAX
                            | $paymentMethodAbstractClass::CHECK_ZERO_TOTAL;
        }

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($methodCode);
        } else {
            $quote->getShippingAddress()->setPaymentMethod($methodCode);
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $paymentData['method'] = $methodCode;

        /**
         * If the chosen payment method has an optional field (like bank selection for iDEAL) we have to check
         * system / config in order to map it to a form field the payment method would expect.
         */
        if ($optionValue) {
            $field = Mage::getStoreConfig(
                self::XPATH_PAYMENT_METHODS . '/' . $methodName . '_option_field',
                $quote->getStoreId()
            );

            /**
             * If a field name is specified we add the option to the payment data as well as to the super global POST
             * array.
             */
            if ($field) {
                $paymentData[$field] = $optionValue;
                $_POST[$field] = $optionValue;
            }
        }

        $paymentDataObject = new Varien_Object();
        $paymentDataObject->setPaymentData($paymentData);

        Mage::dispatchEvent(
            'postnl_checkout_set_payment_before',
            array(
                'payment'             => $quote->getPayment(),
                'quote'               => $quote,
                'payment_data_object' => $paymentDataObject,
            )
        );

        $paymentData = $paymentDataObject->getPaymentData();

        /**
         * Import the payment data, save the payment, and then save the quote
         */
        $quote->getPayment()->importData($paymentData);

        Mage::dispatchEvent(
            'postnl_checkout_set_payment_after',
            array(
                'payment'             => $quote->getPayment(),
                'quote'               => $quote,
            )
        );

        $quote->save();

        return $this;
    }

    /**
     * Adds the customer to the quote if a customer is currently logged in. Also updates the customer's DOB if possible.
     *
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote | null $quote
     *
     * @return $this
     */
    public function updateQuoteCustomer($data, $quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        /**
         * Load the current customer if the user is logged in
         */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();

        /**
         * If there is no customer we don't have to do anything
         */
        if(!$customerId) {
            return $this;
        }

        /**
         * Add the customer to the quote
         */
        $quote->setCustomerId($customerId);
        $quote->getShippingAddress()->setCustomerId($customerId);
        $quote->getBillingAddress()->setCustomerId($customerId);

        /**
         * If the customer already has a DOB we're finished
         */
        if ($customer->getDob()) {
            return $this;
        }

        /**
         * Check if PostNL returned a DOB for the customer
         */
        if (isset($data->Consument)
            && is_object($data->Consument)
            && isset($data->Consument->GeboorteDatum)
            && !empty($data->Consument->GeboorteDatum)
        ) {
            $dob = $data->Consument->GeboorteDatum;
        }

        if (!isset($dob)) {
            return $this;
        }

        $dob = new DateTime($dob);

        /**
         * Update the customer with the DOB and save
         */
        $customer->setDob($dob->getTimestamp())
                 ->save();

        return $this;
    }

    /**
     * Updates the PostNL order with the selected options
     *
     * @param      $data
     * @param null $quote
     *
     * @return $this
     */
    public function updatePostnlOrder($data, $quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        $this->setStoreId($quote->getStoreId());

        $this->_verifyData($data, $quote);

        $postnlOrder = Mage::getModel('postnl_core/order');
        $postnlOrder->load($quote->getId(), 'quote_id');

        /**
         * If a confirm date has been specified, save it with the PostNL Order object so we can reference it later
         */
        if (isset($data->Voorkeuren)
            && is_object($data->Voorkeuren)
            && isset($data->Voorkeuren->Bezorging)
            && is_object($data->Voorkeuren->Bezorging)
            && isset($data->Voorkeuren->Bezorging->VerzendDatum)
            && isset($data->Voorkeuren->Bezorging->Datum)
        ) {
            $delivery = $data->Voorkeuren->Bezorging;
            $postnlOrder->setConfirmDate($delivery->VerzendDatum)
                        ->setDeliveryDate($delivery->Datum);
        }

        /**
         * If a specific product code is needed to ship this order, save it as well
         */
        if (isset($data->Bezorging)
            && is_object($data->Bezorging)
            && isset($data->Bezorging->ProductCode)
        ) {
            $postnlOrder->setProductCode($data->Bezorging->ProductCode);
        }

        /**
         * Check if this is a PakjeGemak order. If so, save the PostNL Order as such
         */
        if (Mage::registry('quote_is_pakje_gemak')) {
            $postnlOrder->setIsPakjeGemak(1);

            Mage::unRegister('quote_is_pakje_gemak');
        }

        $postnlOrder->save();

        return $this;
    }

    /**
     * Converts a quote to it's order
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Mage_Sales_Model_Order
     *
     * @throws TIG_PostNL_Exception
     */
    public function saveOrder($quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        Mage::dispatchEvent('postnl_checkout_save_order_before',
            array(
                'quote' => $quote
            )
        );

        $quoteService = Mage::getModel('sales/service_quote', $quote);
        $quoteService->submitAll();
        $order = $quoteService->getOrder();

        if(empty($order)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to create an order for quote #%s', $quote->getId()),
                'POSTNL-0049'
            );
        }

        /**
         * If a pakje_gemak address is present, add it to the order as well.
         *
         * @var Mage_Sales_Model_Quote_Address $address
         */
        $quoteAddresses = $quote->getAllAddresses();
        foreach ($quoteAddresses as $address) {
            if ($address->getAddressType() != self::ADDRESS_TYPE_PAKJEGEMAK) {
                continue;
            }

            $address->load($address->getId());
            $orderAddress = Mage::getModel('sales/convert_quote')->addressToOrderAddress($address);

            $order->addAddress($orderAddress);

            $orderAddress->save();
            break;
        }

        /**
         * Save the customer's name
         */
        $billingAddress = $quote->getBillingAddress();
        $order->setCustomerFirstname($billingAddress->getFirstname())
              ->setCustomerLastname($billingAddress->getLastname())
              ->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after',
            array(
                'order' => $order,
                'quote' => $quote
            )
        );

        Mage::dispatchEvent('postnl_checkout_save_order_after',
            array(
                'order' => $order,
                'quote' => $quote
            )
        );

        $quote->setIsActive(false)
              ->save();

        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
        $postnlOrder->setOrderId($order->getId())
                    ->setIsActive(false)
                    ->save();

        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutSession->setLastSuccessQuoteId($order->getQuoteId())
                        ->setLastRealOrderId($order->getRealOrderId())
                        ->setLastQuoteId($order->getQuoteId())
                        ->setLastOrderId($order->getId());

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array(
                'order' => $order,
                'quote' => $quote,
                'recurring_profiles' => null
            )
        );

        return $order;
    }

    /**
     * Confirms a PostNL order with PostNL.
     *
     * @param Mage_Sales_Model_Quote
     *
     * @return $this
     */
    public function confirmPostnlOrder($quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        /**
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')
                           ->load($quote->getId(), 'quote_id');

        $cif = Mage::getModel('postnl_checkout/cif');
        $cif->confirmOrder($postnlOrder);

        return $this;
    }

    /**
     * Parses a PostNL Checkout address into a varien object that can be used by Magento.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param StdClass $addressData
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _parseAddress($address, $addressData)
    {
        $storeId = $this->getStoreId();

        /**
         * First parse the street data (streetname, house nr. house nr. ext.)
         */
        $address = $this->_parseStreetData($address, $addressData);

        /**
         * Parse optional address fields
         */
        $buildingNameField = Mage::getStoreConfig(self::XPATH_BUILDING_NAME_FIELD, $storeId);
        $departmentField   = Mage::getStoreConfig(self::XPATH_DEPARTMENT_FIELD, $storeId);
        $doorcodeField     = Mage::getStoreConfig(self::XPATH_DOORCODE_FIELD, $storeId);
        $floorField        = Mage::getStoreConfig(self::XPATH_FLOOR_FIELD, $storeId);
        $areaField         = Mage::getStoreConfig(self::XPATH_AREA_FIELD, $storeId);

        if ($buildingNameField) {
            $address->setData('street' . $buildingNameField, $addressData->Gebouw);
        }

        if ($departmentField) {
            $address->setData('street' . $departmentField, $addressData->Afdeling);
        }

        if ($doorcodeField) {
            $address->setData('street' . $doorcodeField, $addressData->Deurcode);
        }

        if ($floorField) {
            $address->setData('street' . $floorField, $addressData->Verdieping);
        }

        if ($areaField) {
            $address->setData('street' . $areaField, $addressData->Wijk);
        }

        /**
         * Parse the remaining simple fields that require no additional logic
         */
        $firstname  = $addressData->Voornaam;
        $lastname   = $addressData->Achternaam;
        $middlename = $addressData->Tussenvoegsel;
        $country    = $addressData->Land;
        $city       = $addressData->Plaats;
        $postcode   = $addressData->Postcode;

        $address->setFirstname($firstname)
                ->setLastname($lastname)
                ->setMiddlename($middlename)
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode);

        if (!$address->getCountryId()) {
            $address->setCountryId('NL');
        }

        $address->setShouldIgnoreValidation(true);

        return $address;
    }

    /**
     * Add optional service location data to the shipping address. This overrides the previously set address data.
     * nto a varien object that can be used by Magento
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param                                $serviceLocationData
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _addServiceLocationData($address, $serviceLocationData)
    {
        /**
         * First parse the street data (streetname, house nr. house nr. ext.)
         */
        $address = $this->_parseStreetData($address, $serviceLocationData);

        /**
         * Remove any company data that may have been set, this could cause confusion when delivering the package to a
         * service location with a different company name.
         */
        $address->setCompany(false);

        return $address;
    }

    /**
     * Parses street data and returns an address object containing properly formatted street lines.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param StdClass $addressData
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _parseStreetData($address, $addressData)
    {
        $storeId = $this->getStoreId();
        $splitStreet = Mage::getStoreConfigFlag(self::XPATH_SPLIT_STREET, $storeId);

        if (!$splitStreet) {
            /**
             * If the store uses single line addresses, merge the street fields
             */
            $streetData = $addressData->Straat
                        . PHP_EOL
                        . $addressData->Huisnummer
                        . PHP_EOL
                        . $addressData->HuisnummerExt;

            $address->setStreet($streetData);
            return $address;
        }

        $streetData = array();

        /**
         * If the store uses multiple address lines, check which part of the address goes where
         */
        $streetnameField = Mage::getStoreConfig(self::XPATH_STREETNAME_FIELD, $storeId);
        $housenumberField = Mage::getStoreCOnfig(self::XPATH_HOUSENUMBER_FIELD, $storeId);

        /**
         * Set the streetname to the appropriate field
         */
        $streetData[$streetnameField] = $addressData->Straat;

        /**
         * Check if the store splits housenumber and housenumber extensions as well. Place them in appriopriate fields
         */
        $splitHousenumber = Mage::getStoreConfigFlag(self::XPATH_SPLIT_HOUSENUMBER, $storeId);
        if (!$splitHousenumber) {
            $housenumber = $addressData->Huisnummer . ' ' . $addressData->HuisnummerExt;
            $streetData[$housenumberField] = $housenumber;
        } else {
            $housenumberExtensionField = Mage::getStoreConfig(self::XPATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
            $streetData[$housenumberField] = $addressData->Huisnummer;
            $streetData[$housenumberExtensionField] = $addressData->HuisnummerExt;
        }

        /**
         * Sort the street data according to the field numbers and set it
         */
        ksort($streetData);
        $address->setStreet($streetData);

        return $address;
    }

    /**
     * Verifies the validity of the supplied data
     *
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _verifyData($data, $quote)
    {
        /**
         * Check  if the quote matches the one PostNL expected
         */
        $quoteId = $data->Order->ExtRef;
        if ($quote->getId() != $quoteId) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid quote supplied.'),
                'POSTNL-0050'
            );
        }

        /**
         * Verify the webshop ID to make sure this message was not meant for another shop
         */
        $dataWebshopId = $data->Webshop->IntRef;
        $webshopId = Mage::getStoreConfig(self::XPATH_WEBSHOP_ID, $this->getStoreId());
        $webshopId = Mage::helper('core')->decrypt($webshopId);

        if ($webshopId != $dataWebshopId) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid data supplied.'),
                'POSTNL-0051'
            );
        }

        return $this;
    }

    /**
     * Removes all addresses associated with a quote. The quote's regular method to remove all addresses
     * (removeAllAddresses()) effectively resets the addresses rather than removing them (replaces each address by a
     * default one of the same type). We specifically want to delete the optional PakjeGemak address as well.
     *
     * @param Mage_Sales_Model_Quote &$quote
     *
     * @return $this
     */
    protected function _removeAllQuoteAddresses(&$quote)
    {
        /**
         * Truly delete the PakjeGemak address.
         *
         * @var Mage_Sales_Model_Quote_Address $address
         */
        $addresses = $quote->getAllAddresses();
        foreach ($addresses as $address) {
            if ($address->getAddressType() == self::ADDRESS_TYPE_PAKJEGEMAK) {
                $address->isDeleted(true);
            }
        }

        /**
         * Reset all remaining address types (by default only billing and shipping will remain)
         */
        $quote->removeAllAddresses();

        return $this;
    }
}
