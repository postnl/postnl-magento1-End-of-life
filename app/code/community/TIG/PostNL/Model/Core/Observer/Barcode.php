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
class TIG_PostNL_Model_Core_Observer_Barcode
{
    /**
     * Generates a barcode for the shipment if it is new
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Core_Observer_Barcode
     * 
     * @event sales_order_shipment_save_after
     * 
     * @observer postnl_shipment_generate_barcode
     * 
     * @todo change confirm date to the correct value, taking into account 'ordered before X, delivered on Y' settings
     */
    public function generateBarcode(Varien_Event_Observer $observer)
    {
        /**
         * Check if the PostNL module is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        $shipment = $observer->getShipment();
        
        /**
         * Check if a postnl shipment exists for this shipment
         */
        if (Mage::helper('postnl/cif')->postnlShipmentExists($shipment->getId())) {
            return $this;
        }
        
        /**
         * create a new postnl shipment entity
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->setShipmentId($shipment->getId())
                       ->setConfirmDate(Mage::getModel('core/date')->gmtTimestamp());
        
        /**
         * Check if this shipment has an associated PostNL Order. If so, copy it's data.
         */
        $postnlOrder = Mage::getModel('postnl_checkout/order')->load($shipment->getOrderId(), 'order_id');
        if ($postnlOrder->getId()) {
            if ($postnlOrder->getConfirmDate()) {
                $postnlShipment->setConfirmDate(strtotime($postnlOrder->getConfirmDate()));
            }
            
            if ($postnlOrder->getIsPakjeGemak()) {
                $postnlShipment->setIsPakjeGemak($postnlOrder->getIsPakjeGemak());
            }
        }
        
        /**
         * We need an ID in order to save the barcodes
         */
        $postnlShipment->save();
        
        /**
         * Barcode generation needs to be tried seperately. This functionality may throw a valid exception
         * in which case it needs to be tried again later without preventing the shipment from being
         * created. This may happen when CIF is overburdoned.
         */              
        try {
            $postnlShipment->saveAdditionalShippingOptions()
                           ->generateBarcodes();
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }
        
        $postnlShipment->save();
        
        return $this;
    }
}
