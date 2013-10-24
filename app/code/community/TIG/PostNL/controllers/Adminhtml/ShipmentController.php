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
     * Print a shipping label for a single shipment
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function printLabelAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (is_null($shipmentId)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select a shipment.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        try {
            $labels = $this->_getShippingLabels($shipmentId);
            
            $labelModel = Mage::getModel('postnl_core/label');
            $labelModel->createPdf($labels);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
        }
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Creates shipments for a supplied array of orders. This action is triggered by a massaction in the sales > order grid
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massCreateShipmentsAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select one or more orders.')
            );
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }
        
        $chosenOptions = $this->getRequest()->getParam('product_options');
        if ($chosenOptions && $chosenOptions != 'default') {
            Mage::register('postnl_product_options', $chosenOptions);
        }
        
        try {
            foreach ($orderIds as $orderId) {
                $this->_createShipment($orderId);
            }
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred whilst creating processing the shipment(s): %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Prints shipping labels for selected orders.
     * 
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the 'postnl_core/label' model
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massPrintLabelsAction()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');
        if (!is_array($shipmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select one or more shipments.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        if(count($shipmentIds) > 200 && !Mage::helper('postnl/cif')->allowInfinitePrinting()) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('You can print a maximum of 200 labels at once.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
        }
        
        $labels = array();
        try {
            foreach ($shipmentIds as $shipmentId) {
                $labels = array_merge($labels, $this->_getShippingLabels($shipmentId));
            }
            
            /**
             * The label will be a base64 encoded string. Convert this to a pdf
             */
            $label = Mage::getModel('postnl_core/label');
            $label->createPdf($labels);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
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
            /**
             * the qty to ship is the total remaining (not yet shipped) qty of every item 
             */
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
    
    /**
     * Retrieves the shipping label for a given shipment ID.
     * 
     * If the shipment has a stored label, it is returned. Otherwise a new one is generated.
     * 
     * @param int $shipmentId
     * 
     * @return string The encoded label
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _getShippingLabels($shipmentId)
    {
        /**
         * Check iof the shipment was shipped with PostNL
         */
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        if ($shipment->getOrder()->getShippingMethod() != Mage::helper('postnl/carrier')->getPostnlShippingMethod()) {
            throw Mage::Exception('TIG_PostNL', 'This action cannot be used on non-PostNL shipments.');
        }
        
        /**
         * Load the PostNL shipment and check if it already has a label
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipmentId, 'shipment_id');
        if ($postnlShipment->getLabels()) {
            return $postnlShipment->getlabels();
        }
        
        /**
         * If the PostNL shipment is new, set the magento shipment ID
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipmentId);
        }
        
        /**
         * If the shipment does not have a barcode, generate one
         */
        if (!$postnlShipment->getBarcode()) {
            $postnlShipment->generateBarcode()
                           ->addTrackingCodeToShipment();
        }
        
        /**
         * Confirm the shipment and request a new label
         */
        $postnlShipment->confirmAndPrintLabel()
                       ->save();
                       
        $labels = $postnlShipment->getLabels();
        return $labels;
    }
}