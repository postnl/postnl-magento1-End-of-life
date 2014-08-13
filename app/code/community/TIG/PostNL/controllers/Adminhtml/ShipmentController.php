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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Adminhtml_ShipmentController extends TIG_PostNL_Controller_Adminhtml_Shipment
{
    /**
     * Print a shipping label for a single shipment
     *
     * @return $this
     */
    public function printLabelAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('print_label')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        $shipmentId = $this->getRequest()->getParam('shipment_id');

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
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            /**
             * get the labels from CIF
             */
            $labels = $this->_getLabels($shipment);

            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();

            /**
             * merge the labels and print them
             */
            $labelModel = Mage::getModel('postnl_core/label');
            $output = $labelModel->createPdf($labels);

            $filename = 'PostNL Shipping Labels' . date('YmdHis') . '.pdf';

            $this->_preparePdfResponse($filename, $output);
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
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
     * @return $this
     */
    public function confirmAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('confirm')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        $shipmentId = $this->getRequest()->getParam('shipment_id');

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
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            /**
             * Confirm the shipment
             */
            $this->_confirmShipment($shipment);
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
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

        /**
         * Redirect to either the grid or the shipment view.
         */
        if ($this->getRequest()->getParam('return_to_view')) {
            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        } else {
            $this->_redirect('adminhtml/sales_shipment/index');
        }
        return $this;
    }

    /**
     * Loads the status history tab on the shipment view page
     *
     * @return $this
     */
    public function statusHistoryAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('view_complete_status')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

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

    /**
     * Manually sends a track & trace email to the customer.
     *
     * @return $this
     */
    public function sendTrackAndTraceAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('send_track_and_trace')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        $shipmentId = $this->getRequest()->getParam('shipment_id');

        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);
            $postnlShipment->sendTrackAndTraceEmail(true, true);
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
     * Resets a single shipment's confirmation status.
     *
     * @return $this
     */
    public function resetConfirmationAction()
    {
        $helper = Mage::helper('postnl');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (!$this->_checkIsAllowed(array('reset_confirmation', 'delete_labels'))) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }


        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);
            $postnlShipment->resetConfirmation(true, true)->save();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
            $this->__("The shipment's confirmation has been undone.")
        );

        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        return $this;
    }

    /**
     * Remove a shipment's shipping labels.
     *
     * @return $this
     */
    public function removeLabelsAction()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('delete_labels')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }

        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);
            $postnlShipment->deleteLabels()
                           ->setLabelsPrinted(false)
                           ->save();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
            $this->__("The shipment's shipping labels have been deleted.")
        );

        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        return $this;
    }

    /**
     * Convert a shipment to a buspakje shipment.
     *
     * @return $this
     */
    public function convertToBuspakjeAction()
    {
        $helper = Mage::helper('postnl');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (!$this->_checkIsAllowed(array('convert_to_buspakje', 'delete_labels'))) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }

        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);

            if ($postnlShipment->isConfirmed()) {
                if (!$this->_checkIsAllowed(array('reset_confirmation'))) {
                    $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                        $this->__('The current user is not allowed to perform this action.')
                    );

                    $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
                    return $this;
                }

                $postnlShipment->resetConfirmation(true, true)->save();
            }
            $postnlShipment->convertToBuspakje()->save();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
            $this->__('The shipment has been converted to a letter box parcel.')
        );

        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        return $this;
    }

    /**
     * Convert a shipment to a package shipment.
     *
     * @return $this
     */
    public function convertToPackageAction()
    {
        $helper = Mage::helper('postnl');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (!$this->_checkIsAllowed(array('convert_to_package', 'delete_labels'))) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }

        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);

            if ($postnlShipment->isConfirmed()) {
                if (!$this->_checkIsAllowed(array('reset_confirmation'))) {
                    $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                        $this->__('The current user is not allowed to perform this action.')
                    );

                    $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
                    return $this;
                }

                $postnlShipment->resetConfirmation(true, true)->save();
            }
            $postnlShipment->convertToPackage()->save();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
            $this->__('The shipment has been converted to a package.')
        );

        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        return $this;
    }

    /**
     * Convert a shipment to a package shipment.
     *
     * @return $this
     */
    public function changeProductCodeAction()
    {
        $helper = Mage::helper('postnl');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if (!$this->_checkIsAllowed(array('change_product_code'))) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
        }

        /**
         * If no shipment was selected, cause an error
         */
        if (is_null($shipmentId)) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__('Shipment not found.')
            );
            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            /**
             * Load the shipment and check if it exists and is valid.
             *
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentId
                    ),
                    'POSTNL-0009'
                );
            }

            $postnlShipment = $this->_getPostnlShipment($shipmentId);

            if ($postnlShipment->hasLabels()) {
                if (!$this->_checkIsAllowed(array('delete_labels'))) {
                    $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                        $this->__('The current user is not allowed to perform this action.')
                    );

                    $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
                    return $this;
                }

                $postnlShipment->deleteLabels();
            }

            $productOption = $this->getRequest()->getParam('product_option');

            $postnlShipment->changeProductCode($productOption)->save();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
            return $this;
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
            $this->__("The shipment's product option has been changed succesfully.")
        );

        $this->_redirect('adminhtml/sales_shipment/view', array('shipment_id' => $shipmentId));
        return $this;
    }

    /**
     * Refreshes the status history grid after a filter or sorting request
     *
     * @return $this
     */
    public function statusHistoryGridAction()
    {
        $this->_checkIsAllowed('view_complete_status');

        $this->loadLayout(false);
        $this->renderLayout();

        return $this;
    }

    /**
     * Creates shipments for a supplied array of orders. This action is triggered by a massaction in the sales > order
     * grid.
     *
     * @return $this
     */
    public function massCreateShipmentsAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('create_shipment')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }

        $extraOptions = array();

        /**
         * Check if any options were selected. If not, the default will be used.
         */
        $chosenOptions = $this->getRequest()->getParam('product_options', array());

        if (!empty($chosenOptions)) {
            Mage::register('postnl_product_option', $chosenOptions);
        }

        /**
         * Check if an extra cover amount was entered.
         */
        $extraCoverValue = $this->getRequest()->getParam('extra_cover_value');
        if ($extraCoverValue) {
            $extraOptions['extra_cover_amount'] = $extraCoverValue;
        }

        /**
         * Check if a shipment type was specified.
         */
        $shipmentType = $this->getRequest()->getParam('globalpack_shipment_type');
        if ($shipmentType) {
            $extraOptions['globalpack_shipment_type'] = $shipmentType;
        }

        /**
         * Check if a shipment should be treated as abandoned when it can't be delivered.
         */
        $treatAsAbandoned = $this->getRequest()->getParam('globalpack_treat_as_abandoned');
        if ($treatAsAbandoned) {
            $extraOptions['treat_as_abandoned'] = $treatAsAbandoned;
        }

        /**
         * Register the extra options.
         */
        if (!empty($extraOptions)) {
            Mage::register('postnl_additional_options', $extraOptions);
        }

        try {
            $orderIds = $this->_getOrderIds();

            /**
             * Create the shipments.
             */
            $errors = 0;
            foreach ($orderIds as $orderId) {
                try {
                    $this->_createShipment($orderId);
                } catch (TIG_PostNL_Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($orderId),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                    $errors++;
                } catch (Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($orderId),
                            'code'        => null,
                            'description' => $e->getMessage(),
                        )
                    );
                    $errors++;
                }
            }
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_order/index');
            return $this;
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

        /**
         * Check for warnings.
         */
        $this->_checkForWarnings();

        /**
         * Add either a success or failure message and redirect the user accordingly.
         */
        if ($errors < count($orderIds)) {
            $helper->addSessionMessage(
                'adminhtml/session', null, 'success',
                $this->__('The shipments were successfully created.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
        } else {
            $helper->addSessionMessage(
                'adminhtml/session', null, 'error',
                $this->__('None of the shipments could be created. Please check the error messages for more details.')
            );

            $this->_redirect('adminhtml/sales_order/index');
        }

        return $this;
    }

    /**
     * This action basically performs the entire flow of the PostNL extension at once. First we create shipments for the
     * selected orders. Then we confirm those shipments and get their shipping labels. If all goes according to plan,
     * the labels will be presented as a pdf. This really is the "Don't give me any options, just do everything"-option.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function massFullPostnlFlowAction()
    {
        $helper = Mage::helper('postnl');

        $fullFlowAclResources = array(
            'create_shipment',
            'confirm',
            'print_label',
        );

        if (!$this->_checkIsAllowed($fullFlowAclResources)) {
            $helper->addSessionMessage(
                'adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_order/index');

            return $this;
        }

        try {
            /**
             * Perform the full process for all selected orders.
             */
            $this->_fullPostnlFlow();
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_order/index');
            return $this;
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

        return $this;
    }

    /**
     * Create the shipments, confirm them and print their shipping labels.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _fullPostnlFlow()
    {
        $helper = Mage::helper('postnl');

        $orderIds = $this->_getOrderIds();

        /**
         * If the buspakje calculation mode is set to 'automatic', we should check if each order could be a buspakje.
         * Otherwise all shipments will be marked as regular package shipments.
         */
        if (Mage::helper('postnl/deliveryOptions')->getBuspakjeCalculationMode() == 'automatic') {
            $isBuspakje = -1;
        } else {
            $isBuspakje = 0;
        }

        /**
         * Register the requested product option to use the default option and whether to check if the shipment could be
         * a buspakje shipment.
         */
        Mage::unregister('postnl_product_option');
        Mage::register(
            'postnl_product_option',
            array(
                'use_default' => 1,
                'is_buspakje' => $isBuspakje,
            )
        );

        /**
         * Create the shipments.
         */
        $errors = 0;
        $shipmentIds = array();
        $carrierHelper = Mage::helper('postnl/carrier');
        foreach ($orderIds as $orderId) {
            try {
                /**
                 * @var Mage_Sales_Model_Order $order
                 */
                $order = Mage::getModel('sales/order')->load($orderId);
                $shippingMethod = $order->getShippingMethod();

                /**
                 * Check that the order was placed using PostNL.
                 */
                if (!$carrierHelper->isPostnlShippingMethod($shippingMethod)) {
                    $this->addWarning(
                        array(
                            'entity_id'   => $order->getIncrementId(),
                            'code'        => 'POSTNL-0009',
                            'description' => $this->__(
                                'This action is not available for order #%s, because it was not placed using PostNL.',
                                $order->getIncrementId()
                            ),
                        )
                    );

                    $errors++;
                    continue;
                }

                $shipmentIds[] = $this->_createShipment($orderId);
            } catch (TIG_PostNL_Exception $e) {
                /**
                 * If any shipments already exist, get their IDs so they can be processed.
                 */
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');
                $shipmentCollection->addFieldToSelect('entity_id')
                                   ->addFieldToFilter('order_id', $orderId);

                if ($shipmentCollection->getSize() > 0) {
                    $shipmentIds = array_merge($shipmentCollection->getColumnValues('entity_id'), $shipmentIds);
                } else {
                    /**
                     * If no shipments exist, add a warning message indicating the process failed for this order.
                     */
                    $helper->logException($e);
                    $this->addWarning(
                         array(
                             'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($orderId),
                             'code'        => $e->getCode(),
                             'description' => $e->getMessage(),
                         )
                    );
                    $errors++;
                }
            } catch (Exception $e) {
                $helper->logException($e);
                $this->addWarning(
                     array(
                         'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($orderId),
                         'code'        => null,
                         'description' => $e->getMessage(),
                     )
                );
                $errors++;
            }
        }

        /**
         * Add either a success or failure message and redirect the user accordingly.
         */
        if ($errors < count($orderIds)) {
            $helper->addSessionMessage(
            'adminhtml/session', null, 'success',
            $this->__('The shipments were successfully created.')
            );
        } else {
            $helper->addSessionMessage(
                'adminhtml/session',
                null,
                'error',
                $this->__('None of the shipments could be created. Please check the error messages for more details.')
            );

            /**
             * Check for warnings.
             */
            $this->_checkForWarnings();

            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }

        /**
         * Validate the number of labels to be printed. Every shipment has at least 1 label. So if we have more than
         * 200 shipments we can stop the process right here.
         *
         * @var $labelClassName TIG_PostNL_Model_Core_Label
         */
        $labelClassName = Mage::getConfig()->getModelClassName('postnl_core/label');
        if(count($shipmentIds) > $labelClassName::MAX_LABEL_COUNT
            && !Mage::helper('postnl/cif')->allowInfinitePrinting()
        ) {
            throw new TIG_PostNL_Exception(
                $this->__('You can print a maximum of 200 labels at once.'),
                'POSTNL-0014'
            );
        }

        /**
         * Load the shipments and check if they are valid
         */
        $shipments = $this->_loadAndCheckShipments($shipmentIds, true, false);

        /**
         * Get the labels from CIF.
         *
         * @var TIG_PostNL_Model_Core_Shipment $shipment
         */
        $labels = array();
        foreach ($shipments as $shipment) {
            try {
                $shipmentLabels = $this->_getLabels($shipment, true);
                $labels = array_merge($labels, $shipmentLabels);
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                Mage::helper('postnl/cif')->parseCifException($e);

                $helper->logException($e);
                $this->addWarning(
                    array(
                        'entity_id'   => $shipment->getShipmentIncrementId(),
                        'code'        => $e->getCode(),
                        'description' => $e->getMessage(),
                    )
                );
            } catch (TIG_PostNL_Exception $e) {
                $helper->logException($e);
                $this->addWarning(
                    array(
                        'entity_id'   => $shipment->getShipmentIncrementId(),
                        'code'        => $e->getCode(),
                        'description' => $e->getMessage(),
                    )
                );
            } catch (Exception $e) {
                $helper->logException($e);
                $this->addWarning(
                    array(
                        'entity_id'   => $shipment->getShipmentIncrementId(),
                        'code'        => null,
                        'description' => $e->getMessage(),
                    )
                );
            }
        }

        /**
         * Check for warnings.
         */
        $this->_checkForWarnings();

        if (!$labels) {
            $helper->addSessionMessage('adminhtml/session', null, 'error',
                $this->__(
                    'Unfortunately no shipments could be processed. Please check the error messages for more ' .
                    'details.'
                )
            );

            $this->_redirect('adminhtml/sales_order/index');
            return $this;
        }

        /**
         * The label wills be base64 encoded strings. Convert these to a single pdf.
         */
        $label  = Mage::getModel('postnl_core/label');
        $output = $label->createPdf($labels);

        $filename = 'PostNL Shipping Labels' . date('YmdHis') . '.pdf';

        $this->_preparePdfResponse($filename, $output);
        return $this;
    }

    /**
     * Prints shipping labels and confirms selected shipments.
     *
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the
     * 'postnl_core/label' model.
     *
     * @return $this
     */
    public function massPrintLabelsAndConfirmAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed(array('print_label', 'confirm'))) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        $labels = array();
        try {
            $shipmentIds = $this->_getShipmentIds();

            /**
             * Validate the number of labels to be printed. Every shipment has at least 1 label. So if we have more than
             * 200 shipments we can stop the process right here.
             *
             * @var $labelClassName TIG_PostNL_Model_Core_Label
             */
            $labelClassName = Mage::getConfig()->getModelClassName('postnl_core/label');
            if(count($shipmentIds) > $labelClassName::MAX_LABEL_COUNT
                && !Mage::helper('postnl/cif')->allowInfinitePrinting()
            ) {
                throw new TIG_PostNL_Exception(
                    $this->__('You can print a maximum of 200 labels at once.'),
                    'POSTNL-0014'
                );
            }

            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true, false);

            /**
             * Get the labels from CIF.
             *
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            foreach ($shipments as $shipment) {
                try {
                    $shipmentLabels = $this->_getLabels($shipment, true);
                    $labels = array_merge($labels, $shipmentLabels);
                } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                    Mage::helper('postnl/cif')->parseCifException($e);

                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                } catch (TIG_PostNL_Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                } catch (Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => null,
                            'description' => $e->getMessage(),
                        )
                    );
                }
            }

            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();

            if (!$labels) {
                $helper->addSessionMessage('adminhtml/session', null, 'error',
                    $this->__(
                        'Unfortunately no shipments could be processed. Please check the error messages for more ' .
                        'details.'
                    )
                );

                $this->_redirect('adminhtml/sales_shipment/index');
                return $this;
            }

            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');

            if ($this->getRequest()->getPost('print_start_pos')) {
                $label->setLabelCounter($this->getRequest()->getPost('print_start_pos'));
            }

            $output = $label->createPdf($labels);

            $filename = 'PostNL Shipping Labels' . date('YmdHis') . '.pdf';

            $this->_preparePdfResponse($filename, $output);
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
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the
     * 'postnl_core/label' model.
     *
     * @return $this
     */
    public function massPrintLabelsAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('print_label')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        $labels = array();
        try {
            $shipmentIds = $this->_getShipmentIds();

            /**
             * @var $labelClassName TIG_PostNL_Model_Core_Label
             */
            $labelClassName = Mage::getConfig()->getModelClassName('postnl_core/label');
            if(count($shipmentIds) > $labelClassName::MAX_LABEL_COUNT
                && !Mage::helper('postnl/cif')->allowInfinitePrinting()
            ) {
                throw new TIG_PostNL_Exception(
                    $this->__('You can print a maximum of 200 labels at once.'),
                    'POSTNL-0014'
                );
            }

            /**
             * Load the shipments and check if they are valid.
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true, false);

            /**
             * Get the labels from CIF.
             *
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            foreach ($shipments as $shipment) {
                try {
                    $shipmentLabels = $this->_getLabels($shipment, true);
                    $labels = array_merge($labels, $shipmentLabels);
                } catch (TIG_PostNL_Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                } catch (Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => null,
                            'description' => $e->getMessage(),
                        )
                    );
                }
            }

            /**
             * We need to check for warnings before the label download response
             */
            $this->_checkForWarnings();

            if (!$labels) {
                $helper->addSessionMessage('adminhtml/session', null, 'error',
                    $this->__(
                        'Unfortunately no shipments could be processed. Please check the error messages for more ' .
                        'details.'
                    )
                );

                $this->_redirect('adminhtml/sales_shipment/index');
                return $this;
            }

            /**
             * The label wills be base64 encoded strings. Convert these to a single pdf
             */
            $label = Mage::getModel('postnl_core/label');

            if ($this->getRequest()->getPost('print_start_pos')) {
                $label->setLabelCounter($this->getRequest()->getPost('print_start_pos'));
            }

            $output = $label->createPdf($labels);

            $fileName = 'PostNL Shipping Labels' . date('YmdHis') . '.pdf';

            $this->_preparePdfResponse($fileName, $output);
        } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
            Mage::helper('postnl/cif')->parseCifException($e);

            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
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
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the
     * 'postnl_core/label' model.
     *
     * @return $this
     */
    public function massPrintPackingSlipsAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('print_label')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            if ($this->getRequest()->getParam('shipment_ids')) {
                $shipmentIds = $this->_getShipmentIds();
            } else {
                $orderIds = $this->_getOrderIds();

                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                                          ->addFieldToSelect('entity_id')
                                          ->addFieldToFilter('order_id', array('in', $orderIds));

                $shipmentIds = $shipmentCollection->getColumnValues('entity_id');
                unset($shipmentCollection);

                /**
                 * Check if a shipment was selected
                 */
                if (empty($shipmentIds)) {
                    throw new TIG_PostNL_Exception(
                        $this->__(
                            'None of the orders you have selected have any associated shipments. Please choose at ' .
                            'least one order that has a shipment.'
                        ),
                        'POSTNL-0171'
                    );
                }
            }

            /**
             * @var $labelClassName TIG_PostNL_Model_Core_Label
             */
            $labelClassName = Mage::getConfig()->getModelClassName('postnl_core/label');
            if(count($shipmentIds) > $labelClassName::MAX_LABEL_COUNT
                && !Mage::helper('postnl/cif')->allowInfinitePrinting()
            ) {
                throw new TIG_PostNL_Exception(
                    $this->__('You can print a maximum of 200 labels at once.'),
                    'POSTNL-0014'
                );
            }

            /**
             * Printing many packing slips can take a while, therefore we need to disable the PHP execution time limit.
             */
            set_time_limit(0);

            /**
             * Load the shipments and check if they are valid.
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true, false);

            /**
             * Get the packing slip model.
             */
            $packingSlipModel = Mage::getModel('postnl_core/packingSlip');

            /**
             * Get the current memory limit as an integer in bytes. Because printing packing slips can be very memory
             * intensive, we need to monitor memory usage.
             */
            $memoryLimit = $helper->getMemoryLimit();

            /**
             * Create the pdf's and add them to the main pdf object.
             *
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            $pdf = new Zend_Pdf();
            foreach ($shipments as $shipment) {
                try {
                    /**
                     * If the current memory usage exceeds 75%, end the script. Otherwise we risk other processes being
                     * unable to finish and throwing fatal errors.
                     */
                    $memoryUsage = memory_get_usage(true);

                    if ($memoryUsage / $memoryLimit > 0.75) {
                        throw new TIG_PostNL_Exception(
                            $this->__(
                                'Approaching memory limit for this operation. Please select fewer shipments and try ' .
                                'again.'
                            ),
                            'POSTNL-0170'
                        );
                    }

                    $shipmentLabels = $this->_getLabels($shipment, false);
                    $packingSlipModel->createPdf($shipmentLabels, $shipment, $pdf);
                } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                    Mage::helper('postnl/cif')->parseCifException($e);

                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                } catch (TIG_PostNL_Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                } catch (Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => null,
                            'description' => $e->getMessage(),
                        )
                    );
                }
            }
            unset($shipment, $shipments, $shipmentLabels, $packingSlip, $packingSlipModel);

            /**
             * We need to check for warnings before the label download response.
             */
            $this->_checkForWarnings();

            if (!$pdf->pages) {
                $helper->addSessionMessage('adminhtml/session', null, 'error',
                    $this->__(
                        'Unfortunately no shipments could be processed. Please check the error messages for more ' .
                        'details.'
                    )
                );

                $this->_redirect('adminhtml/sales_shipment/index');
                return $this;
            }

            /**
             * Render the pdf as a string.
             */
            $output = $pdf->render();

            $fileName = 'PostNL Packing Slips '
                      . date('Ymd-His', Mage::getSingleton('core/date')->timestamp())
                      . '.pdf';

            $this->_preparePdfResponse($fileName, $output);
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
     * Please note that if you use a different label than the default 'GraphicFile|PDF' you must overload the
     * 'postnl_core/label' model.
     *
     * @return $this
     */
    public function massConfirmAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('confirm')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            $shipmentIds = $this->_getShipmentIds();

            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true, false);

            /**
             * Confirm the shipments.
             *
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            $errors = 0;
            foreach ($shipments as $shipment) {
                try {
                    $this->_confirmShipment($shipment);
                } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                    Mage::helper('postnl/cif')->parseCifException($e);

                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                    $errors++;
                } catch (TIG_PostNL_Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                    $errors++;
                } catch (Exception $e) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => $shipment->getShipmentIncrementId(),
                            'code'        => null,
                            'description' => $e->getMessage(),
                        )
                    );
                    $errors++;
                }
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

        if ($errors < count($shipments)) {
            $helper->addSessionMessage(
                'adminhtml/session', null, 'success',
                $this->__('The shipments have been confirmed successfully.')
            );
        } else {
            $helper->addSessionMessage(
                'adminhtml/session', null, 'error',
                $this->__(
                    'Unfortunately no shipments could be processed. Please check the error messages for more details.'
                )
            );
        }

        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }

    /**
     * Creates a Parcelware export file based on the selected shipments
     *
     * @return $this
     */
    public function massCreateParcelwareExportAction()
    {
        $helper = Mage::helper('postnl');
        if (!$this->_checkIsAllowed('create_parcelware_export')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/sales_shipment/index');
            return $this;
        }

        try {
            $shipmentIds = $this->_getShipmentIds();

            /**
             * Load the shipments and check if they are valid
             */
            $shipments = $this->_loadAndCheckShipments($shipmentIds, true);

            /**
             * @var TIG_PostNL_Model_Parcelware_Export $parcelwareExportModel
             */
            $parcelwareExportModel = Mage::getModel('postnl_parcelware/export');
            $csvContents = $parcelwareExportModel->exportShipments($shipments);

            $timestamp = date('Ymd_His', Mage::getModel('core/date')->timestamp());

            $this->_prepareDownloadResponse("PostNL_Parcelware_Export_{$timestamp}.csv", $csvContents);
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

        $this->_redirect('adminhtml/sales_shipment/index');
        return $this;
    }
}