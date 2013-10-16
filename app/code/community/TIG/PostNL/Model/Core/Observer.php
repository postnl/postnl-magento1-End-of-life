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
class TIG_PostNL_Model_Core_Observer
{
    /**
     * Generates a barcode for the shipment if it is new
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Core_Observer
     * 
     * @event sales_order_shipment_save_after
     * 
     * @observer postnl_shipment_generate_barcode
     * 
     * @todo change confirm date to the correct value, instead of the current timestamp
     */
    public function generateBarcode(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getShipment();
        
        /**
         * Check if a postnl shipment exists for this shipment
         */
        if (Mage::helper('postnl/carrier')->postnlShipmentExists($shipment->getId())) {
            return $this;
        }
        
        //create a new postnl shipment entity
        $postnlShipment = Mage::getModel('postnl/shipment');
        $postnlShipment->setShipmentId($shipment->getId())
                       ->setConfirmData(Mage::getModel('core/date')->timestamp()) //TODO change this to the actual confirm date
                       ->generateBarcode()
                       ->addTrackingCodeToShipment()
                       ->save();
        
        return $this;
    }
}
