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
        $helper = Mage::helper('postnl');
        
        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
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
            $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
            if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) {
                throw new TIG_PostNL_Exception($this->__('This action cannot be used on non-PostNL shipments.'), 'POSTNL-0009');
            }
            
            /**
             * get the labels from CIF
             */
            $labels = $this->_printLabels($shipment);
            
            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();
            
            /**
             * merge the labels and print them
             */
            $labelModel = Mage::getModel('postnl_core/label');
            $labelModel->createPdf($labels);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
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
        $helper = Mage::helper('postnl');
        
        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
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
            $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
            if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) {
                throw new TIG_PostNL_Exception($this->__('This action cannot be used on non-PostNL shipments.'), 'POSTNL-0009');
            }
            
            /**
             * Confirm the shipment
             */
            $this->_confirmShipment($shipment);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        $helper->addSessionMessage('adminhtml/session', null, 'success',
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
        $currentTimestamp = Mage::getModel('core/date')->gmtTimestamp();
        $fifteenMinutesAgo = strtotime("-15 minutes", $currentTimestamp);
        $statusHistoryUpdatedAt = $postnlShipment->getStatusHistoryUpdatedAt();
        
        /**
         * If this shipment's status history has not been updated in the last 15 minutes (if ever) update it
         */
        if ($postnlShipment->getId()
            && ($postnlShipment->getStatusHistoryUpdatedAt() === null
                || strtotime($statusHistoryUpdatedAt) < $fifteenMinutesAgo
            )
        ) {
            try {
                $postnlShipment->updateCompleteShippingStatus()
                               ->save();
            } catch (Exception $e) {
                /**
                 * This request may return a valid exception when the shipment could not be found
                 */
                Mage::helper('postnl')->logException($e);
            }
        }
        
        $this->loadLayout();
        $this->renderLayout();
             
        return $this;
    }
    
    public function sendTrackAndTraceAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $helper = Mage::helper('postnl');
        
        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }
        
        try {
            /**
             * Load the shipment and check if it exists and is valid
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
            if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) {
                throw new TIG_PostNL_Exception($this->__('This action cannot be used on non-PostNL shipments.'), 'POSTNL-0009');
            }
            
            $postnlShipment = $this->_getPostnlShipment($shipmentId);
            $postnlShipment->sendTrackAndTraceEmail(true);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }
        
        $helper->addSessionMessage('adminhtml/session', null, 'success',
            $this->__('The track & trace email was sent.')
        );
        
        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
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
        $helper = Mage::helper('postnl');
        
        /**
         * Check if an order was selected
         */
        if (!is_array($orderIds)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0011', 'error',
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
         * Check if a shipment should be treated as abandoned when it can't be delivered
         */
        $treatAsAbandoned = $this->getRequest()->getParam('globalpack_treat_as_abandoned');
        if ($treatAsAbandoned) {
            $extraOptions['treat_as_abandoned'] = $treatAsAbandoned;
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
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }
        
        $helper->addSessionMessage('adminhtml/session', null, 'success',
            $this->__('The shipments were successfully created.')
        );
        
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
        $helper = Mage::helper('postnl');
        
        /**
         * Check if a shipment was selected
         */
        if (!is_array($shipmentIds)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0013', 'error',
                $this->__('Please select one or more shipments.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        /**
         * Validate the numer of labels to be printed. Every shipment has at least 1 label. So if we have more than 200 shipments
         * we can stop the process right here.
         */
        if(count($shipmentIds) > 200 && !Mage::helper('postnl/cif')->allowInfinitePrinting()) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0014', 'error',
                $this->__('You can print a maximum of 200 labels at once.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
        }
        
        $labels = array();
        try {
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true);
            
            /**
             * Get the labels from CIF
             */
            foreach ($shipments as $shipment) {
                $shipmentLabels = $this->_printLabels($shipment, true);
                $labels = array_merge($labels, $shipmentLabels);
            }
            
            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();
            
            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');
            $label->createPdf($labels);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
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
        $helper = Mage::helper('postnl');
        
        /**
         * Check if a shipment was selected
         */
        if (!is_array($shipmentIds)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0013', 'error',
                $this->__('Please select one or more shipments.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        if(count($shipmentIds) > 200 && !Mage::helper('postnl/cif')->allowInfinitePrinting()) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0014', 'error',
                $this->__('You can print a maximum of 200 labels at once.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
        }
        
        $labels = array();
        try {
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true);
            
            /**
             * Get the labels from CIF
             */
            foreach ($shipments as $shipment) {
                $labels = array_merge($labels, $this->_printLabels($shipment, false));
            }
            
            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();
            
            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');
            $label->createPdf($labels);
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
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
        $helper = Mage::helper('postnl');
        
        /**
         * Check if a shipment was selected
         */
        if (!is_array($shipmentIds)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0013', 'error',
                $this->__('Please select one or more shipments.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        try {
            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true);
            
            /**
             * Confirm the shipments
             */
            foreach ($shipments as $shipment) {
                $this->_confirmShipment($shipment);
            }
        } catch (TIG_PostNL_Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error', 
                $this->__('An error occurred while processing this action.')
            );
            
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }
        
        $this->_checkForWarnings();
        
        $helper->addSessionMessage('adminhtml/session', null, 'success',
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
            throw new TIG_PostNL_Exception(
                $this->__("Order #%s cannot be shipped at this time.", $order->getIncrementId()),
                'POSTNL-0015' 
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
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     * @param boolean $confirm Optional parameter to also confirm the shipment
     * 
     * @return string The encoded label
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _printLabels($shipment, $confirm = false)
    {
        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        } else {
            $postnlShipment = $shipment;
        }
        
        /**
         * Check if the shipment already has any labels. If so, return those. If we also need to confirm the shipment, do that
         * first.
         */
        if ($postnlShipment->hasLabels()
            && $confirm === true
            && !$postnlShipment->isConfirmed()
        ) {
            $this->_confirmShipment($postnlShipment);
            return $postnlShipment->getlabels();
        }
        
        if ($postnlShipment->hasLabels()) {
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
        
        if ($confirm === true 
            && !$postnlShipment->hasLabels()
            && !$postnlShipment->isConfirmed()
        ) {
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
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     * 
     * @return TIG_PostNL_Adminhtml_ShipmentController
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _confirmShipment($shipment)
    {
        /**
         * Load the PostNL shipment
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        } else {
            $postnlShipment = $shipment;
        }
        
        /**
         * Prevent EU shipments from being confirmed if their labels are not yet printed
         */
        if ($postnlShipment->isEuShipment() && !$postnlShipment->getLabelsPrinted()) {
            throw new TIG_PostNL_Exception(
                $this->__("For EU shipments you may only confirm a shipment after it's labels have been printed."), 
                'POSTNL-0016'
            );
        }
        
        /**
         * If the PostNL shipment is new, set the magento shipment ID
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipment->getId());
        }
        
        /**
         * If the shipment does not have a main barcode, generate new barcodes
         */
        if (!$postnlShipment->getMainBarcode()) {
            $postnlShipment->generateBarcodes();
        }
        
        if ($postnlShipment->getConfirmStatus() === $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            /**
             * The shipment is already confirmed
             */
            throw new TIG_PostNL_Exception($this->__('This shipment has already been confirmed.'), 'POSTNL-00017');
        }
        
        if (!$postnlShipment->canConfirm()) {
            /**
             * The shipment cannot be confirmed at this time
             */
            throw new TIG_PostNL_Exception($this->__('This shipment cannot be confirmed at this time.'), 'POSTNL-00018');
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
    protected function _loadAndCheckShipments($shipmentIds, $loadPostnlShipments = false)
    {
        if (!is_array($shipmentIds)) {
            $shipmentIds = array($shipmentIds);
        }
        
        $shipments = array();
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        foreach ($shipmentIds as $shipmentId) {
            /**
             * Load the shipment
             */
            if ($loadPostnlShipments ===  false) {
                $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                $shippingMethod = $shipment->getOrder()->getShippingMethod();
            } else {
                $shipment = Mage::getModel('postnl_core/shipment')->load($shipmentId, 'shipment_id');
                $shippingMethod = $shipment->getShipment()->getOrder()->getShippingMethod();
            }
            
            /**
             * Check if the shipping method used is allowed
             */
            if (!in_array($shippingMethod, $postnlShippingMethods)) {
                throw new TIG_PostNL_Exception($this->__('This action cannot be used on non-PostNL shipments.'), 'POSTNL-0009');
            }
            
            $shipments[] = $shipment;
        }
        
        return $shipments;
    }
    
    /**
     * Checks if any warnings were received while processing the action in CIF. If any warnings are found they are
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
            /**
             * Check if we have an error code for this warning so we can add a link to the TiG knowledgebase
             */
            $link = false;
            $error = Mage::getConfig()->getNode('tig/errors/' . $warning['code']);
            if ($error !== false) {
                $link = (string) $error->url;
            }
            
            /**
             * Build the message proper
             */
            $warningMessage .= '<li>' 
                             . '[' . $warning['code'] . '] '
                             . $this->__($warning['description']);
            /**
             * Add the link if it's available
             */
            if ($link) {
                $warningMessage .= ' <a href="' 
                                 . $link 
                                 . '" target="_blank" class="postnl-message">' 
                                 . $this->__('Click here for more information from the TiG knowledgebase.') 
                                 . '</a>';
            }
            
            $warningMessage .= '</li>';
        }
        
        $warningMessage .= '</ul>';
        
        /**
         * Add the warnings to the session
         */
        Mage::helper('postnl')->addSessionMessage('adminhtml/session', null, 'notice',
            $warningMessage
        );
            
        return $this;
    }
}