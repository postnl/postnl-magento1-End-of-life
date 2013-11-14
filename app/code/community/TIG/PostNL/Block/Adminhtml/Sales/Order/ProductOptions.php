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
class TIG_PostNL_Block_Adminhtml_Sales_Order_ProductOptions extends Mage_Adminhtml_Block_Abstract
{    
    /**
     * Get available product options
     * 
     * @return array
     */
    public function getExtraCoverProductOptions()
    {
        if ($this->getData('extra_cover_product_options')) {
            return $this->getData('extra_cover_product_options');
        }
        
        $productOptions = Mage::getModel('postnl_core/system_config_source_allProductOptions')
                              ->getExtraCoverOptions(true);
        
        $this->setExtraCoverProductOptions($productOptions);
        return $productOptions;
    }
    
    /**
     * Get available GlobalPack product option
     * 
     * @return string
     */
    public function getGlobalPackProductOption()
    {
        if ($this->getData('globalpack_product_option')) {
            return $this->getData('globalpack_product_option');
        }
        
        $globalPackProductOption = Mage::getModel('postnl_core/system_config_source_globalProductOptions')
                                       ->getAvailableOptions();
        
        if (empty($globalPackProductOption)) {
            return '';
        }
        
        $optionValue = $globalPackProductOption[0]['value'];
        $this->setGlobalpackProductOption($optionValue);
        return $optionValue;
    }
    
    /**
     * Gets an array of shipment types for use with GlobalPack shipments
     * 
     * @return array
     */
    public function getShipmentTypes()
    {
        $shipmentTypes = Mage::helper('postnl/cif')->getShipmentTypes();
        
        return $shipmentTypes;
    }
    
    /**
     * Check if the PostNL module is enabled before rendering
     * 
     * @return string | parent::_toHtml()
     * 
     * @see Mage_Adminhtml_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {     
        if (!Mage::helper('postnl')->isEnabled()) { 
            return ''; 
        }
        
        return parent::_toHtml();
    }
}
