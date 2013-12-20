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

class TIG_PostNL_Block_Adminhtml_System_Config_ConfigCheck
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Template file used by this element
     * 
     * @var string
     */
    protected $_template = 'TIG/PostNL/system/config/config_check.phtml';
    
    /**
     * Get the postnl helper
     * 
     * @return TIG_PostNL_Helper_Data
     */
    public function getPostnlHelper()
    {
        if ($this->hasPostnlHelper()) {
            return $this->getData('postnl_helper');
        }
        
        $helper = Mage::helper('postnl');
        
        $this->setPostnlHelper($helper);
        return $helper;
    }
    
    /**
     * Check if live mode is enabled
     * 
     * @return boolean
     */
    public function isLiveEnabled()
    {
        $helper = $this->getPostnlHelper();
        
        return $helper->isEnabled(false, false, false);
    }
    
    /**
     * gets config errors from the registry
     * 
     * @return array|null
     */
    public function getLiveConfigErrors()
    {
        $configErrors = Mage::registry('postnl_is_configured_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_enabled_errors');
        }
        
        return $configErrors;
    }
    
    /**
     * Check if test mode is enabled
     * 
     * @return boolean
     */
    public function isTestEnabled()
    {
        $helper = $this->getPostnlHelper();
        
        return $helper->isEnabled(false, false, true);
    }
    
    /**
     * gets config errors from the registry
     * 
     * @return array|null
     */
    public function getTestConfigErrors()
    {
        $configErrors = Mage::registry('postnl_is_configured_test_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_enabled_test_errors');
        }
        
        return $configErrors;
    }
    
    /**
     * Check if global shipments are
     * 
     * @return boolean
     */
    public function isGlobalEnabled()
    {
        $helper = $this->getPostnlHelper();
        
        return $helper->isEnabled(false, true, false);
    }
    
    /**
     * gets config errors from the registry
     * 
     * @return array|null
     */
    public function getGlobalConfigErrors()
    {
        $configErrors = Mage::registry('postnl_is_configured_global_test_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_enabled_global_test_errors');
        }
        
        return $configErrors;
    }
    
    /**
     * Check if checkout is enabled
     * 
     * @return boolean
     */
    public function isCheckoutEnabled()
    {
        $helper = Mage::helper('postnl/checkout');
        
        return $helper->isCheckoutEnabled(false);
    }
    
    /**
     * gets config errors from the registry
     * 
     * @return array|null
     */
    public function getCheckoutConfigErrors()
    {
        $configErrors = Mage::registry('postnl_is_configured_checkout_errors');
        if (is_null($configErrors)) {
            $configErrors = Mage::registry('postnl_enabled_checkout_errors');
        }
        
        return $configErrors;
    }
    
    /**
     * Render the element
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * 
     * @return string
     * 
     * @todo implement this method so that it renders a warning if the module has not yet been fully configured
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}