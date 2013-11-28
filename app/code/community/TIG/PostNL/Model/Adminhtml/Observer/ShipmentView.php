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
 
/**
 * Observer to edit the shipment view
 */
class TIG_PostNL_Model_Adminhtml_Observer_ShipmentView
{
    /**
     * The block we want to edit
     */
    const SHIPMENT_VIEW_BLOCK_NAME = 'adminhtml/sales_order_shipment_view';
    
    /**
     * Observer that adds a print label button to the shipment view page
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Adminhtml_Observer_ShipmentView
     * 
     * @event adminhtml_block_html_before
     * 
     * @observer postnl_adminhtml_shipmentview
     */
    public function addPrintLabelButton(Varien_Event_Observer $observer)
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
        $shipmentViewClass = Mage::getConfig()->getBlockClassName(self::SHIPMENT_VIEW_BLOCK_NAME);
        
        if (get_class($block) !== $shipmentViewClass) {
            return $this;
        }
        
        /**
         * Check if the current shipment was placed with PostNL
         */
        $shipment = Mage::registry('current_shipment');
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) { 
            return $this; 
        } 
        
        $printShippingLabelUrl = $this->getPrintShippingLabelUrl($shipment->getId());
        $block->addButton('print_shipping_label', array(
            'label'   => Mage::helper('postnl')->__('PostNL - Print shipping label'),
            'onclick' => "setLocation('{$printShippingLabelUrl}')",
            'class'   => 'save',
        ));
                
        return $this;
    }
    
    /**
     * Get adminhtml url for PostNL print shipping label action
     * 
     * @param int $shipmentId The ID of the current shipment
     * 
     * @return string
     */
    public function getPrintShippingLabelUrl($shipmentId)
    {
        $url = Mage::helper('adminhtml')->getUrl(
            'postnl/adminhtml_shipment/printLabel', 
            array('shipment_id' => $shipmentId)
        );
        
        return $url;
    }
}
