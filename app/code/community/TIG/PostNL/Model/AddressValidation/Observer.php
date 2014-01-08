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
class TIG_PostNL_Model_AddressValidation_Observer extends Varien_Object
{
    /**
     * The block classes that we want to edit
     */
    const ONEPAGE_BILLING_ADDRESS_BLOCK_NAME = 'checkout/onepage_billing';
    
    public function getOnepageBillingAddressBlockClass()
    {
        if ($this->hasData('onepage_billing_address_block_class')) {
            return $this->getData('onepage_billing_address_block_class');
        }
        
        $blockClass = Mage::getConfig()->getBlockClassName(self::ONEPAGE_BILLING_ADDRESS_BLOCK_NAME);
        
        $this->setOnepageBillingAddressBlockClass($blockClass);
        return $blockClass;
    }
    
    public function onepagePostcodeCheck(Varien_Event_Observer $observer)
    {
        /**
         * check if the extension is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        /**
         * Checks if the current block is the one we want to edit.
         * 
         * Unfortunately there is no unique event for this block
         */
        $block = $observer->getBlock();
        $blockClass = $this->getOnepageBillingAddressBlockClass();
       
        if (get_class($block) !== $blockClass) {
            return $this;
        }
        
        $block->setTemplate('TIG/PostNL/checkout/onepage/billing.phtml');
        
        return $this;
    }
}
