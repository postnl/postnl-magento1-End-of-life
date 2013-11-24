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
         * Send a ping request to see if the PostNL Checkout service is available
         */
        try {
            $cif = Mage::getModel('postnl_checkout/cif');
            $result = $cif->ping();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            return false;
        }
        
        if (!$result || $result !== 'OK') {
            return false;
        }
        
        return true;
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
 