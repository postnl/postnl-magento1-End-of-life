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
class TIG_PostNL_Block_Core_ShippingStatus extends Mage_Core_Block_Template
{
    /**
     * Checks if a given shipment has been confirmed with PostNL
     * 
     * @param Mage_Sales_Model_Order_Shipment
     * 
     * @return boolean
     */
    public function isConfirmed($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        if ($postnlShipment->getConfirmStatus() == $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks if a given shipment has been confirmed with PostNL
     * 
     * @param Mage_Sales_Model_Order_Shipment
     * 
     * @return string
     */
    public function getConfirmedAt($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        
        $confirmedAt = Mage::helper('core')->formatDate($postnlShipment->getConfirmedAt(), 'medium', false);
        
        return $confirmedAt;
    }
    
    /**
     * Checks if a given shipment has been confirmed with PostNL
     * 
     * @param Mage_Sales_Model_Order_Shipment
     * 
     * @return boolean
     */
    public function getTrackingUrl($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        
        $barcodeUrl = $postnlShipment->getBarcodeUrl(true);
        
        $trackingUrl = "<a href={$barcodeUrl} title='mijnpakket' target='_blank'>"
                     . $this->__('here')
                     . '</a>';
        
        return $trackingUrl;
    }
    
    /**
     * Check if the PostNL module is enabled. Otherwise return an empty string.
     * 
     * @return string | Mage_Core_Block_Template::_toHtml()
     */
    protected function _toHtml()
    {
        if (!Mage::helper('postnl')->isEnabled()) {
            return '';
        }
        
        return parent::_toHtml();
    }
}
