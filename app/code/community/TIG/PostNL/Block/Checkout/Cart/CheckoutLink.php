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
class TIG_PostNL_Block_Checkout_Cart_CheckoutLink extends Mage_Core_Block_Template
{
    /**
     * Base URLs of the checkout button
     */
    const CHECKOUT_BUTTON_TEST_BASE_URL = 'https://tppcb-sandbox.e-id.nl/Button/Checkout';
    const CHECKOUT_BUTTON_LIVE_BASE_URL = 'https://checkout.postnl.nl/Button/Checkout';
    
    /**
     * XML path to public webshop ID setting
     */
    const XML_PATH_PUBLIC_WEBSHOP_ID = 'postnl/cif/public_webshop_id';
    
    /**
     * XML path for 'show_checkout_for_letter' setting
     */
    const XML_PATH_SHOW_CHECKOUT_FOR_LETTER = 'postnl/checkout/show_checkout_for_letter';
    
    /**
     * Gets the checkout URL
     * 
     * @return string
     */
    public function getCheckoutUrl()
    {
        $url = Mage::helper('checkout/url')->getCheckoutUrl();
        
        return $url;
    }
    
    /**
     * Check if the button should be disabled
     * 
     * @return boolean
     */
    public function isDisabled()
    {
        $isDisabled = !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
        
        return $isDisabled;
    }

    /**
     * Check if the button should be displayed
     * 
     * @return boolean
     */
    public function canUsePostnlCheckout()
    {
        $checkoutEnabled = Mage::helper('postnl/checkout')->isCheckoutEnabled();
        if (!$checkoutEnabled) {
            return false;
        }
        
        /**
         * Check if the total weight of all items in the quote exceed 2 kg. If so, the order might fit in a letter and PostNL
         * Checkout should onyl be available if the merchant has expressly configured it as such.
         */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quoteItems = $quote->getAllItems();
        
        $totalWeight = 0;
        foreach ($quoteItems as $item) {
            $totalWeight += $item->getRowWeight();
        }
        
        $kilograms = Mage::helper('postnl/cif')->standardizeWeight($totalWeight, Mage::app()->getStore()->getId());
        $showCheckoutForLetters = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_CHECKOUT_FOR_LETTER);
        if ($kilograms < 2 && !$showCheckoutForLetters) {
            return false;
        }
        
        /**
         * Send a ping request to see if the PostNL Checkout service is available
         */
        try {
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->ping();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            return false;
        }
        
        if ($result !== 'OK') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Gets this webshop's public ID
     * 
     * @return string
     */
    public function getPublicWebshopId()
    {
        if ($this->hasPublicWebshopId()) {
            return $this->getData('public_webshop_id');
        }
        
        $webshopId = Mage::getStoreConfig(self::XML_PATH_PUBLIC_WEBSHOP_ID, Mage::app()->getStore()->getId());
        
        $this->setPublicWebshopId($webshopId);
        return $webshopId;
    }
    
    /**
     * Gets the checkout button src attribute
     * 
     * @return string
     */
    public function getSrc()
    {
        if (Mage::helper('postnl/checkout')->isTestMode()) {
            $baseUrl = self::CHECKOUT_BUTTON_TEST_BASE_URL;
        } else {
            $baseUrl = self::CHECKOUT_BUTTON_LIVE_BASE_URL;
        }
        
        $webshopId = $this->getPublicWebshopId();
        
        $url =  $baseUrl 
             . '?publicId=' . $webshopId
             . '&format=Large&type=Orange';
                  
        return $url;
    }
    
    /**
     * Returns the block's html. Checks if the 'use_postnl_checkout' param is set. If not, returns and empty string
     * 
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canUsePostnlCheckout()) {
            return '';
        }
        
        return parent::_toHtml();
    }
}
 