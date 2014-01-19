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
class TIG_PostNL_Model_Checkout_Observer_Shipment
{
    /**
     * Updates a PostNL Checkout order with CIF. This has to occur after a shipment is confirmed. If an order has multiple
     * shipments, this has to happen every time a shipment is confirmed. Each time the request will contain and additional
     * shipment each having 1 or more parcels.
     *  
     * @param Varien_Event_Observer $observer
     * 
     * @return TIG_PostNL_Model_Core_Observer_Barcode
     * 
     * @event postnl_shipment_confirm_after
     * 
     * @observer postnl_checkout_update_order
     * 
     * @throws TIG_PostNL_Exception
     */
    public function updateOrder(Varien_Event_Observer $observer)
    {
        $postnlShipment = $observer->getShipment();
        
        $orderId = $postnlShipment->getOrderId();
        $postnlOrder = Mage::getModel('postnl_checkout/order');
        $postnlOrder->load($orderId, 'order_id');
        if (!$postnlOrder->getId()) {
            return $this;
        }
        
        $cif = Mage::getModel('postnl_checkout/cif');
        $result = $cif->updateOrder($postnlOrder);
        
        if (!isset($result->Succes) || $result->Succes != 'true') {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid UpdateOrder response received!'),
                'POSTNL-0037'
            );
        }
        
        return $this;
    }
}
