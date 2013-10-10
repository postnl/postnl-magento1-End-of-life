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
class TIG_PostNL_Adminhtml_ShipmentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Creates shipments for a supplied array of orders. This action is triggered by a massaction in the sales > order grid
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massCreateShipmentsAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!$orderIds) {
            $this->_redirect('adminhtml/sales_order/index');
        }
        
        foreach ($orderIds as $orderId) {
            $this->_createShipment($orderId);
            
        }
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Creates a shipment of an order containing all available items
     * 
     * @param int $orderId
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    protected function _createShipment($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if (!$order->canShip()) {
            throw Mage::exception(
                'TIG_PostNL', 
                $this->__("Order #%s cannot be shipped at this time.", $order->getIncrementId())
            );
        }
        
        $shipment = Mage::getModel('sales/service_order', $order)
                        ->prepareShipment($this->_getItemQtys($order));
                        
        Mage::register('current_shipment', $shipment);
        
        $shipment->register();
        $this->_saveShipment($shipment);
        
        return $this;
    }
    
    /**
     * Initialize shipment items QTY
     * 
     * @param Mage_Sales_Model_Order $order
     * 
     * @return array
     */
    protected function _getItemQtys($order)
    {
        $itemQtys = array();
        
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            //the qty to ship is the total remaining (not yet shipped) qty of every item 
            $itemQty = $item->getQtyOrdered() - $item->getQtyShipped();
            
            $itemQtys[$item->getId()] = $itemQty;
        }
        
        return $itemQtys;
    }
    
    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
                               ->addObject($shipment)
                               ->addObject($shipment->getOrder())
                               ->save();

        return $this;
    }
}