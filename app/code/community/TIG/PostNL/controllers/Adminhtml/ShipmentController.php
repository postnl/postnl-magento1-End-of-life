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
        
        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select a shipment.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        try {
            /**
             * Load the shipment and check if it exists and is valid
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if ($shipment->getOrder()->getShippingMethod() != Mage::helper('postnl/carrier')->getPostnlShippingMethod()) {
                throw Mage::exception('TIG_PostNL', 'This action cannot be used on non-PostNL shipments.');
            }
            
            /**
             * get the labels from CIF
             */
            $labels = $this->_printLabels($shipment);
            
            /**
             * merge the labels and print them
             */
            $labelModel = Mage::getModel('postnl_core/label');
            $labelModel->createPdf($labels);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }

    /**
     * Confirm a PosTNL shipment without printing a label
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function confirmAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        
        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select a shipment.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        try {
            /**
             * Load the shipment and check if it exists and is valid
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if ($shipment->getOrder()->getShippingMethod() != Mage::helper('postnl/carrier')->getPostnlShippingMethod()) {
                throw Mage::exception('TIG_PostNL', 'This action cannot be used on non-PostNL shipments.');
            }
            
            /**
             * Confirm the shipment
             */
            $this->_confirmShipment($shipment);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('The shipment has been successfully confirmed')
        );
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Loads the status history tab on the shipment view page
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function statusHistoryAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $postnlShipment = $this->_getPostnlShipment($shipmentId);
        Mage::register('current_postnl_shipment', $postnlShipment);
        
        /**
         * Get the postnl shipments' status history updated at timestamp and a reference timestamp of 15 minutes ago
         */
        $currentTimestamp = Mage::getModel('core/date')->timestamp();
        $fifteenMinutesAgo = strtotime("-15 minutes", $currentTimestamp);
        $statusHistoryUpdatedAt = $postnlShipment->getStatusUpdatedAt();
        
        /**
         * If this shipment's status history has not been updated in the last 15 minutes (if ever) update it
         */
        if ($postnlShipment->getId()
            && ($postnlShipment->getStatusUpdatedAt() === null
                || strtotime($statusHistoryUpdatedAt) < $fifteenMinutesAgo
            )
        ) {
            $postnlShipment->updateCompleteShippingStatus();
        }
        
        $this->loadLayout();
        $this->renderLayout();
             
        return $this;
    }
    
    /**
     * Gets the postnl shipment associated with a shipment
     * 
     * @param int $shipmentId
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _getPostnlShipment($shipmentId)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipmentId, 'shipment_id');
        
        return $postnlShipment;
    }
    
    /**
     * Refreshes the status history grid after a filter or sorting request
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function statusHistoryGridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
        
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
        
        /**
         * Check if an order was selected
         */
        if (!is_array($orderIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select one or more orders.')
            );
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }
        
        $extraOptions = array();
        
        /**
         * Check if any options were selected. If not, the default will be used
         */
        $chosenOptions = $this->getRequest()->getParam('product_options');
        if ($chosenOptions && $chosenOptions != 'default') {
            Mage::register('postnl_product_option', $chosenOptions);
        }
        
        /**
         * Check if an extra cover amount was entered
         */
        $extraCoverValue = $this->getRequest()->getParam('extra_cover_value');
        if ($extraCoverValue) {
            $extraOptions['extra_cover_amount'] = $extraCoverValue;
        }
        
        /**
         * Check if a shipment type was specified
         */
        $shipmentType = $this->getRequest()->getParam('globalpack_shipment_type');
        if ($shipmentType) {
            $extraOptions['shipment_type'] = $shipmentType;
        }
        
        /**
         * Register the extra options
         */
        if (!empty($extraOptions)) {
            Mage::register('postnl_additional_options', $extraOptions);
            
        }
        try {
            /**
             * Create the shipments
             */
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
        
        $this->_checkForWarnings();
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Prints shipping labels and confirms selected shipments.
     * 
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the 'postnl_core/label' model
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massPrintLabelsAndConfirmAction()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');
        
        /**
         * Check if a shipment was selected
         */
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
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds);
            
            /**
             * Get the labels from CIF
             */
            foreach ($shipments as $shipment) {
                $labels = array_merge($labels, $this->_printLabels($shipment, true));
            }
            
            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');
            $label->createPdf($labels);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Prints shipping labels for selected shipments.
     * 
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the 'postnl_core/label' model
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massPrintLabelsAction()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');
        
        /**
         * Check if a shipment was selected
         */
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
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds);
            
            /**
             * Get the labels from CIF
             */
            foreach ($shipments as $shipment) {
                $labels = array_merge($labels, $this->_printLabels($shipment, false));
            }
            
            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');
            $label->createPdf($labels);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
    
    /**
     * Prints shipping labels and confirms selected shipments.
     * 
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the 'postnl_core/label' model
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    public function massConfirmAction()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids');
        
        /**
         * Check if a shipment was selected
         */
        if (!is_array($shipmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('Please select one or more shipments.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        try {
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds);
            
            /**
             * Confirm the shipments
             */
            foreach ($shipments as $shipment) {
                $this->_confirmShipment($shipment);
            }
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while processing this action: %s', $e->getMessage())
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('The shipments have been confirmed successfully.')
        );
        
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
     * @param boolean $confirm Optional parameter to also confirm the shipment
     * 
     * @return string The encoded label
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _printLabels($shipment, $confirm = false)
    {
        /**
         * Load the PostNL shipment and check if it already has a label
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        if ($postnlShipment->getLabels()) {
            return $postnlShipment->getlabels();
        }
        
        /**
         * If the PostNL shipment is new, set the magento shipment ID
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipment->getId());
        }
        
        /**
         * If the shipment does not have a barcode, generate one
         */
        if (!$postnlShipment->getMainBarcode()) {
            $postnlShipment->generateBarcodes()
                           ->addTrackingCodeToShipment();
        }
        
        if ($confirm === true) {
            /**
             * Confirm the shipment and request a new label
             */
            $postnlShipment->confirmAndGenerateLabel()
                           ->addTrackingCodeToShipment()
                           ->save();
        } else {
            /**
             * generate new shipping labels without confirming
             */
            $postnlShipment->generateLabel()
                           ->save();
        }
                   
        $labels = $postnlShipment->getLabels();
        return $labels;
    }
    
    /**
     * Confirms the shipment without printing labels
     * 
     * @param int $shipmentId
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _confirmShipment($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        
        /**
         * If the PostNL shipment is new, set the magento shipment ID
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipment->getId());
        }
        
        /**
         * If the shipment does not have a barcode, generate one
         */
        if (!$postnlShipment->getMainBarcode()) {
            $postnlShipment->generateBarcode()
                           ->addTrackingCodeToShipment();
        }
        
        if ($postnlShipment->getConfirmStatus() === $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            /**
             * The shipment is already confirmed
             */
            throw Mage::exception('TIG_PostNL', 'This shipment has already been confirmed.');
        }
        
        if (!$postnlShipment->canConfirm()) {
            /**
             * The shipment cannot be confirmed at this time
             */
            throw Mage::exception('TIG_PostNL', 'This shipment cannot be confirmed at this time.');
        }

        /**
         * Confirm the shipment
         */
        $postnlShipment->confirm()
                       ->addTrackingCodeToShipment()
                       ->save();
        
        $labels = $postnlShipment->getLabels();
        return $labels;
    }
    
    /**
     * Load an array of shipments based on an array of shipmentIds and check if they're shipped using PostNL
     * 
     * @param array $shipmentIds
     * 
     * @return array
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _loadAndCheckShipments($shipmentIds)
    {
        if (!is_array($shipmentIds)) {
            $shipmentIds = array($shipmentIds);
        }
        
        $shipments = array();
        $postnlShippingMethod = Mage::helper('postnl/carrier')->getPostnlShippingMethod();
        foreach ($shipmentIds as $shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if ($shipment->getOrder()->getShippingMethod() != $postnlShippingMethod) {
                throw Mage::exception('TIG_PostNL', 'This action cannot be used on non-PostNL shipments.');
            }
            
            $shipments[] = $shipment;
        }
        
        return $shipments;
    }
    
    /**
     * Checks if any warnings were recieved while processing the action in CIF. If any warnings are found they are
     * added to the adminhtml session as a notice.
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     */
    protected function _checkForWarnings()
    {
        /**
         * Check if any warnings were registered
         */
        $warnings = Mage::registry('postnl_cif_warnings');
        
        if (!is_array($warnings)) {
            return $this;
        }
        
        /**
         * Create a warning message to display to the merchant
         */
        $warningMessage = $this->__('PostNL replied with the following warnings:');
        $warningMessage .= '<ul>';
        
        /**
         * Add each warning to the message
         */
        foreach ($warnings as $warning) {
            $warningMessage .= '<li>' . $this->__('Error code %s: %s', $warning['code'], $warning['description']) . '</li>';
        }
        
        $warningMessage .= '</ul>';
        
        /**
         * Add the warnings to the session
         */
        Mage::getSingleton('adminhtml/session')->addNotice($warningMessage);
            
        return $this;
    }
}