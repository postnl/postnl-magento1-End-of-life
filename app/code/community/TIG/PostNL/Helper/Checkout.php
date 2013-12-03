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
class TIG_PostNL_Helper_Checkout extends TIG_PostNL_Helper_Data
{
    /**
     * XML path to checkout on/off switch
     */
    const XML_PATH_CHECKOUT_ACTIVE = 'postnl/checkout/active';
    
    /**
     * XML path to all PostNL Checkout payment methods.
     * N.B. last part of the XML path is missing.
     */
    const XML_PATH_CHECKOUT_PAYMENT_METHOD = 'postnl/checkout_payment_methods';
    
    /**
     * XML path to test / live mode setting
     */
    const XML_PATH_TEST_MODE = 'postnl/checkout/mode';
    
    /**
     * Array of payment methods supported by PostNL Checkout. 
     * Keys are the names used in system.xml, values are codes used by PostNL Checkout.
     * 
     * @var array
     */
    protected $_checkoutPaymentMethods = array(
        'ideal'                  => 'IDEAL',
        'creditcard'             => 'CREDITCARD',
        'checkpay'               => 'CHECKPAY',
        'paypal'                 => 'PAYPAL',
        'directdebit'            => 'MACHTIGING',
        'acceptgiro'             => 'ACCEPTGIRO',
        'vooraf_betalen'         => 'VOORAF',
        'termijnen'              => 'TERMIJNEN',
        'giftcard'               => 'KADOBON',
        'rabobank_internetkassa' => 'RABOINTKASSA', 
        'afterpay'               => 'AFTERPAY',
    );
    
    /**
     * An array of required configuration settings
     * 
     * @var array
     */
    protected $_checkoutRequiredFields = array(
        'postnl/checkout/active',
        'postnl/cif/webshop_id',
        'postnl/cif/public_webshop_id',
    );
    
    /**
     * Gets a list of payment methods supported by PostNL Checkout
     * 
     * @return array
     */
    public function getCheckoutPaymentMethods()
    {
        $paymentMethods = $this->_checkoutPaymentMethods;
        return $paymentMethods;
    }
    
    /**
     * Returns an array of configuration settings that must be entered for PostNL Checkout to function
     * 
     * @return array
     */
    public function getCheckoutRequiredFields()
    {
        $requiredFields = $this->_checkoutRequiredFields;
        return $requiredFields;
    }
    
    /**
     * Restores a quote to working order
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function restoreQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setIsActive(true)
              ->save();
        
        return $quote;
    }
    
    /**
     * Check if the module is set to test mode
     * 
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        if (Mage::registry('postnl_checkout_test_mode') !== null) {
            return Mage::registry('postnl_checkout_test_mode');
        }
        
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $testMode = Mage::getStoreConfigFlag(self::XML_PATH_TEST_MODE, $storeId);
        
        Mage::register('postnl_checkout_test_mode', $testMode);
        return $testMode;
    }
    
    /**
     * Check if PostNL checkout is enabled
     * 
     * @param null | int $storeId
     * 
     * @return boolean
     */
    public function isCheckoutEnabled($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $isPostnlEnabled = $this->isEnabled();
        if ($isPostnlEnabled === false) {
            return false;
        }
        
        $checkoutEnabled = Mage::getStoreConfigFlag(self::XML_PATH_CHECKOUT_ACTIVE, $storeId);
        if (!$checkoutEnabled) {
            return false;
        }
        
        $isConfigured = $this->isCheckoutConfigured($storeId);
        if (!$isConfigured) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if all required fields are entered
     * 
     * @param null|int $storeId
     * 
     * @return boolean
     */
    public function isCheckoutConfigured($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        /**
         * First check if all required configuration settings are entered
         */
        $requiredFields = $this->getCheckoutRequiredFields();
        foreach ($requiredFields as $field) {
            $value = Mage::getStoreConfig($field, $storeId);
            if (empty($value)) {
                return false;
            }
        }
        
        /**
         * Go through each supported payment method. At least one of them must be activated.
         */
        $paymentMethods = $this->getCheckoutPaymentMethods();
        $paymentMethodSettings = Mage::getStoreConfig(self::XML_PATH_CHECKOUT_PAYMENT_METHOD, $storeId);
        foreach ($paymentMethods as $methodCode => $method) {
            if (array_key_exists($methodCode, $paymentMethodSettings)
                && $paymentMethodSettings[$methodCode] === '1'
            ) {
                return true;
            }
        }
        
        /**
         * If no payment method was activated the extension is not configured properly
         */
        return false;
    }
    
    
}
