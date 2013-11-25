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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Checkout_Service extends Varien_Object
{
    /**
     * XML path to public webshop ID setting
     */
    const XML_PATH_WEBSHOP_ID = 'postnl/checkout/webshop_id';
    
    /**
     * Constants containing XML paths to cif address configuration options
     */
    const XML_PATH_SPLIT_STREET                = 'postnl/cif_address/split_street';
    const XML_PATH_STREETNAME_FIELD            = 'postnl/cif_address/streetname_field';
    const XML_PATH_HOUSENUMBER_FIELD           = 'postnl/cif_address/housenr_field';
    const XML_PATH_SPLIT_HOUSENUMBER           = 'postnl/cif_address/split_housenr';
    const XML_PATH_HOUSENUMBER_EXTENSION_FIELD = 'postnl/cif_address/housenr_extension_field';
    const XML_PATH_AREA_FIELD                  = 'postnl/cif_address/area_field';
    const XML_PATH_BUILDING_NAME_FIELD         = 'postnl/cif_address/building_name_field';
    const XML_PATH_DEPARTMENT_FIELD            = 'postnl/cif_address/department_field';
    const XML_PATH_DOORCODE_FIELD              = 'postnl/cif_address/doorcode_field';
    const XML_PATH_FLOOR_FIELD                 = 'postnl/cif_address/floor_field';
    const XML_PATH_REMARK_FIELD                = 'postnl/cif_address/remark_field';
    
    /**
     * XML path to all PostNL Checkout payment settings
     * N.B. missing last part os it will return an array of settings
     */
    const XML_PATH_PAYMENT_METHODS = 'postnl/checkout_payment_methods';
    
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

        $quote = Mage::getSingleton('checkout/session')->getQuote();;
        
        $this->setQuote($quote);
        return $quote;
    }
    
    /**
     * Updates a quote with the given PostNL order data. Each part of the data is used to replace the data normally acquired
     * during checkout.
     * 
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote | null $quote
     * 
     * @return TIG_PostNL_Model_Checkout_Service
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
         * Parse the shipping and billing addresses
         */
        $delivery = $data->Bezorging;
        $shippingAddressData = $delivery->Geadresseerde;
        $shippingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress->setAddressType($shippingAddress::TYPE_SHIPPING);
        $shippingAddress = $this->_parseAddress($shippingAddress, $shippingAddressData);
        
        $billingAddressData = $data->Facturatie->Adres;
        $billingAddress = Mage::getModel('sales/quote_address');
        $billingAddress->setAddressType($billingAddress::TYPE_BILLING);
        $billingAddress = $this->_parseAddress($billingAddress, $billingAddressData);
        
        if (isset($delivery->ServicePunt)) {
            $serviceLocationData = $delivery->ServicePunt;
            $pakjeGemakAddress = Mage::getModel('sales/quote_address');
            $pakjeGemakAddress->setAddressType(self::ADDRESS_TYPE_PAKJEGEMAK);
            $pakjeGemakAddress = $this->_parseAddress($pakjeGemakAddress, $serviceLocationData);
            
            $quote->addAddress($pakjeGemakAddress);
        }
        
        /**
         * Update the quote's addresses
         */
        $quote->setShippingAddress($shippingAddress)
              ->setBillingAddress($billingAddress)
              ->save();
        
        return $this;
    }
    
    /**
     * Updates a quote with the given PostNL payment data. This method specifically updates the quote's payment data
     * 
     * @param StdClass $data
     * @param Mage_Sales_Model_Quote | null $quote
     * 
     * @return TIG_PostNL_Model_Checkout_Service
     * 
     * @throws TIG_PostNL_Exception
     */
    public function updateQuotePayment($data, $quote = null)
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
        if (!Mage::getStoreConfigFlag(self::XML_PATH_PAYMENT_METHODS . '/' . $methodName, $quote->getStoreId())) {
            throw Mage::exception('TIG_PostNL', "Selected payment method {$methodName} is not available.");
        }

        /**
         * Get the Magento payment method code associated with this method
         */
        $methodCode = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS . '/' . $methodName . '_method', $quote->getStoreId());
        
        /**
         * Remove any current payment associtaed with the quote and get a new one
         */
        $payment = $quote->removePayment()
                         ->getPayment();
        
        /**
         * Form the payment data array
         */
         
        $paymentData = Mage::app()->getRequest()->getPost('payment', array());
        $paymentData['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
            
        $paymentData['method'] =$methodCode;
        
        /**
         * If the chosen payment method has an optional field (like bank selection for iDEAL) we have to check system / config in
         * order to map it to a form field the payment method would expect.
         */
        $optionValue = $postnlPaymentData->Optie;
        if ($optionValue) {
            $field = Mage::getStoreConfig(
                self::XML_PATH_PAYMENT_METHODS . '/' . $methodName . '_option_field', 
                $quote->getStoreId()
            );
            
            /**
             * If a field name is specified we add the option to the payment data as well as to the super global POST array
             */
            if ($field) {
                $paymentData[$field] = $optionValue;
                $_POST[$field] = $optionValue;
            }
        }
        
        /**
         * Import the payment data, save the payment, and then save the quote
         */
        $payment->importData($paymentData)
                ->save();
                
        $quote->save();
        
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
        
        $quoteService = Mage::getModel('sales/service_quote', $quote);
        $quoteService->submitAll();
        $order = $quoteService->getOrder();
        
        if(empty($order)) {
            throw Mage::exception('TIG_PostNL', 'Unable to create an order for quote #' . $quote->getId());
        }
        
        Mage::dispatchEvent('checkout_type_onepage_save_order_after',
            array(
                'order' => $order, 
                'quote' => $quote
            )
        );
        
        $quote->setIsActive(false)
              ->save();
        
        $postnlOrder = Mage::getModel('postnl_checkout/order');
        $postnlOrder->load($quote->getId(), 'quote_id')
                    ->setOrderId($order->getId())
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
     * Updates the PostNL order with the selected options
     * 
     * @param StdClass $orderDetails
     * 
     * @return TIG_PostNL_Model_Checkout_Service
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
                
        $postnlOrder = Mage::getModel('postnl_checkout/order');
        $postnlOrder->load($quote->getId(), 'quote_id');
        
        /**
         * If a confirm date has been specified, save it with the PostNL Order object so we can reference it later
         */
        if (isset($data->Voorkeuren)
            && is_object($data->Voorkeuren)
            && isset($data->Voorkeuren->Bezorging)
            && is_object($data->Voorkeuren->Bezorging)
            && isset($data->Voorkeuren->Bezorging->VerzendDatum)
        ) {
            $postnlOrder->setConfirmDate($data->Voorkeuren->Bezorging->VerzendDatum);
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
        
        $postnlOrder->save();
        
        return $this;
    }
    
    /**
     * Confirms a PostNL order with PostNL.
     * 
     * @param Mage_Sales_Model_Quote
     * 
     * @return TIG_PostNL_Model_Checkout_Service
     */
    public function confirmPostnlOrder($quote = null)
    {
        /**
         * Load the current quote if none was supplied
         */
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        
        $postnlOrder = Mage::getModel('postnl_checkout/order')
                           ->load($quote->getId(), 'quote_id');
                           
        $cif = Mage::getModel('postnl_checkout/cif');
        $cif->confirmOrder($postnlOrder);
        
        return $this;
    }
    
    /**
     * Parses a PostNL Checkout address into a varien object that can be used by Magento
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
        $buildingNameField = Mage::getStoreConfig(self::XML_PATH_BUILDING_NAME_FIELD, $storeId);
        $departmentField   = Mage::getStoreConfig(self::XML_PATH_DEPARTMENT_FIELD, $storeId);
        $doorcodeField     = Mage::getStoreConfig(self::XML_PATH_DOORCODE_FIELD, $storeId);
        $floorField        = Mage::getStoreConfig(self::XML_PATH_FLOOR_FIELD, $storeId);
        $areaField         = Mage::getStoreConfig(self::XML_PATH_AREA_FIELD, $storeId);
        
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
                ->setMiddelname($middlename)
                ->setCountry($country)
                ->setCity($city)
                ->setPostcode($postcode);
        
        $address->setShouldIgnoreValidation(true);
        
        return $address;
    }
    
    /**
     * Add optional service location data to the shipping address. This ovverrides the previously set address data.
     * nto a varien object that can be used by Magento
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @param StdClass $addressData
     * 
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _addServiceLocationData($address, $serviceLocationData)
    {
        $storeId = $this->getStoreId();
        
        /**
         * First parse the street data (streetname, house nr. house nr. ext.)
         */        
        $address = $this->_parseStreetData($address, $serviceLocationData);
        
        /**
         * Remove any company data that may have been set, this could cause confusion when delivering the package to a service
         * location with a different company name
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
        $splitStreet = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_STREET, $storeId);
        
        if (!$splitStreet) {
            /**
             * If the store uses single line addresses, merge the street fields
             */
            $streetData = $addressData->Straat . PHP_EOL . $addressData->Huisnummer . PHP_EOL . $addressData->HuisnummerExt;
            
            $address->setStreet($streetData);
            return $address;
        }
        
        $streetData = array();
        
        /**
         * If the store uses multiple address lines, check which part of the address goes where
         */
        $streetnameField = Mage::getStoreConfig(self::XML_PATH_STREETNAME_FIELD, $storeId);
        $housenumberField = Mage::getStoreCOnfig(self::XML_PATH_HOUSENUMBER_FIELD, $storeId);
        
        /**
         * Set the streetname to the appropriate field
         */
        $streetData[$streetnameField] = $addressData->Straat;
        
        /**
         * Check if the store splits housenumber and housenumber extensions as well. Place them in appriopriate fields
         */
        $splitHousenumber = Mage::getStoreConfigFlag(self::XML_PATH_SPLIT_HOUSENUMBER, $storeId);
        if (!$splitHousenumber) {
            $housenumber = $addressData->Huisnummer . ' ' . $addressData->HuisnummerExt;
            $streetData[$housenumberField] = $housenumber;
        } else {
            $housenumberExtensionField = Mage::getStoreConfig(self::XML_PATH_HOUSENUMBER_EXTENSION_FIELD, $storeId);
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
     * @return TIG_PostNL_Model_Checkout_Service
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
            throw Mage::exception('TIG_PostNL', 'Invalid quote supplied.');
        }
        
        /**
         * Verify the webshop ID to make sure this message was not meant for another shop
         */
        $webshopId = $data->Webshop->IntRef;
        if (Mage::getStoreConfig(self::XML_PATH_WEBSHOP_ID, $this->getStoreId()) != $webshopId) {
            throw Mage::exception('TIG_PostNL', 'Invalid data supplied.');
        }
        
        return $this;
    }
}
