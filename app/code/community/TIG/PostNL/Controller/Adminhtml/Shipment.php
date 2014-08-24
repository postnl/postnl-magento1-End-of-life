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
class TIG_PostNL_Controller_Adminhtml_Shipment extends Mage_Adminhtml_Controller_Action
{
    /**
     * Used module name in current adminhtml controller.
     */
    protected $_usedModuleName = 'TIG_PostNL';

    /**
     * @var array
     */
    protected $_warnings = array();

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->_warnings;
    }

    /**
     * @param array $warnings
     *
     * @return $this
     */
    public function setWarnings(array $warnings)
    {
        $this->_warnings = $warnings;

        return $this;
    }

    /**
     * @param array|string $warning
     *
     * @return $this
     */
    public function addWarning($warning)
    {
        if (!is_array($warning)) {
            $warning = array(
                'entity_id'   => null,
                'code'        => null,
                'description' => $warning,
            );
        }

        $warnings = $this->getWarnings();
        $warnings[] = $warning;

        $this->setWarnings($warnings);
        return $this;
    }

    /**
     * Get shipment Ids from the request.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getShipmentIds()
    {
        $shipmentIds = $this->getRequest()->getParam('shipment_ids', array());

        /**
         * Check if a shipment was selected.
         */
        if (!is_array($shipmentIds) || empty($shipmentIds)) {
            throw new TIG_PostNL_Exception(
                $this->__('Please select one or more shipments.'),
                'POSTNL-0013'
            );
        }

        return $shipmentIds;
    }

    /**
     * Get order Ids from the request.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getOrderIds()
    {
        $orderIds = $this->getRequest()->getParam('order_ids', array());

        /**
         * Check if an order was selected.
         */
        if (!is_array($orderIds) || empty($orderIds)) {
            throw new TIG_PostNL_Exception(
                $this->__('Please select one or more orders.'),
                'POSTNL-0011'
            );
        }

        return $orderIds;
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
     * Initialize shipment items QTY
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getItemQtys($order)
    {
        $itemQtys = array();

        /**
         * @var Mage_Sales_Model_Order_Item $item
         */
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
     * Creates a shipment of an order containing all available items
     *
     * @param int $orderId
     *
     * @return int
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _createShipment($orderId)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($orderId);

        if (!$order->canShip()) {
            throw new TIG_PostNL_Exception(
                $this->__('Order #%s cannot be shipped at this time.', $order->getIncrementId()),
                'POSTNL-0015'
            );
        }

        $shipment = Mage::getModel('sales/service_order', $order)
                        ->prepareShipment($this->_getItemQtys($order));

        $shipment->register();
        $this->_saveShipment($shipment);

        return $shipment->getId();
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        Mage::getModel('core/resource_transaction')
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
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getLabels($shipment, $confirm = false)
    {
        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = $this->_getPostnlShipment($shipment->getId());
        } else {
            $postnlShipment = $shipment;
        }

        /**
         * Check if the shipment already has any labels. If so, return those. If we also need to confirm the shipment,
         * do that first.
         */
        if ($postnlShipment->hasLabels()) {
            if ($confirm === true && !$postnlShipment->isConfirmed() && $postnlShipment->canConfirm()) {
                $this->_confirmShipment($postnlShipment);
            }

            return $postnlShipment->getlabels();
        }

        /**
         * If the PostNL shipment is new, set the magento shipment ID.
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipment->getId());
        }

        /**
         * If the shipment does not have a barcode, generate one.
         */
        if (!$postnlShipment->getMainBarcode() && $postnlShipment->canGenerateBarcode()) {
            $postnlShipment->generateBarcodes();
        }

        if ($confirm === true
            && !$postnlShipment->hasLabels()
            && !$postnlShipment->isConfirmed()
            && $postnlShipment->canConfirm(true)
        ) {
            /**
             * Confirm the shipment and request a new label.
             */
            $postnlShipment->confirmAndGenerateLabel();

            if ($postnlShipment->canAddTrackingCode()) {
                $postnlShipment->addTrackingCodeToShipment();
            }

            $postnlShipment->save();
        } else {
            /**
             * generate new shipping labels without confirming.
             */
            $postnlShipment->generateLabel()
                           ->save();
        }

        $labels = $postnlShipment->getLabels();
        return $labels;
    }

    /**
     * Confirms the shipment without printing labels.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _confirmShipment($shipment)
    {
        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = $this->_getPostnlShipment($shipment->getId());
        } else {
            $postnlShipment = $shipment;
        }

        /**
         * Prevent EU shipments from being confirmed if their labels are not yet printed.
         */
        if ($postnlShipment->isEuShipment() && !$postnlShipment->getLabelsPrinted()) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    "Shipment #%s could not be confirmed, because for EU shipments you may only confirm a shipment " .
                    "after it's labels have been printed.",
                    $postnlShipment->getShipment()->getIncrementId()
                ),
                'POSTNL-0016'
            );
        }

        /**
         * If the PostNL shipment is new, set the magento shipment ID.
         */
        if (!$postnlShipment->getShipmentId()) {
            $postnlShipment->setShipmentId($shipment->getId());
        }

        /**
         * If the shipment does not have a main barcode, generate new barcodes.
         */
        if (!$postnlShipment->getMainBarcode() && $postnlShipment->canGenerateBarcode()) {
            $postnlShipment->generateBarcodes();
        }

        if ($postnlShipment->getConfirmStatus() === $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            /**
             * The shipment is already confirmed.
             */
            throw new TIG_PostNL_Exception(
                $this->__('Shipment #%s has already been confirmed.', $postnlShipment->getShipment()->getIncrementId()),
                'POSTNL-0017'
            );
        }

        if (!$postnlShipment->canConfirm()) {
            /**
             * The shipment cannot be confirmed at this time.
             */
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Shipment #%s cannot be confirmed at this time.',
                    $postnlShipment->getShipment()->getIncrementId()
                ),
                'POSTNL-00018'
            );
        }

        /**
         * Confirm the shipment.
         */
        $postnlShipment->confirm();

        if ($postnlShipment->canAddTrackingCode()) {
            $postnlShipment->addTrackingCodeToShipment();
        }

        $postnlShipment->save();

        return $this;
    }

    /**
     * Load an array of shipments based on an array of shipmentIds and check if they're shipped using PostNL
     *
     * @param array|int $shipmentIds
     * @param boolean   $loadPostnlShipments Flag that determines whether the shipments will be loaded as
     *                                       Mage_Sales_Model_Shipment or TIG_PostNL_Model_Core_Shipment objects.
     * @param boolean   $throwException Flag whether an exception should be thrown when loading the shipment fails.
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _loadAndCheckShipments($shipmentIds, $loadPostnlShipments = false, $throwException = true)
    {
        if (!is_array($shipmentIds)) {
            $shipmentIds = array($shipmentIds);
        }

        $shipments = array();
        foreach ($shipmentIds as $shipmentId) {
            /**
             * Load the shipment.
             *
             * @var Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment|boolean $shipment
             */
            $shipment = $this->_loadShipment($shipmentId, $loadPostnlShipments);

            if (!$shipment && $throwException) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipment->getIncrementId()
                    ),
                    'POSTNL-0009'
                );
            } elseif (!$shipment) {
                $this->addWarning(
                    array(
                        'entity_id'   => $shipmentId,
                        'code'        => 'POSTNL-0009',
                        'description' => $this->__(
                            'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                            $shipmentId
                        ),
                    )
                );

                continue;
            }

            $shipments[] = $shipment;
        }

        return $shipments;
    }

    /**
     * Load a shipment based on a shipment ID.
     *
     * @param int     $shipmentId
     * @param boolean $loadPostnlShipments
     *
     * @return boolean|Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment
     */
    protected function _loadShipment($shipmentId, $loadPostnlShipments)
    {
        if ($loadPostnlShipments === false) {
            /**
             * @var Mage_Sales_Model_Order_Shipment $shipment
             */
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            if (!$shipment || !$shipment->getId()) {
                return false;
            }

            $shippingMethod = $shipment->getOrder()->getShippingMethod();
        } else {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            $shipment = $this->_getPostnlShipment($shipmentId);
            if (!$shipment || !$shipment->getId()) {
                return false;
            }

            $shippingMethod = $shipment->getShipment()->getOrder()->getShippingMethod();
        }

        /**
         * Check if the shipping method used is allowed
         */
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return false;
        }

        return $shipment;
    }

    /**
     * Output the specified string as a pdf.
     *
     * @param string $filename
     * @param string $output
     *
     * @return $this
     * @throws Zend_Controller_Response_Exception
     */
    protected function _preparePdfResponse($filename, $output)
    {
        $this->getResponse()
             ->setHttpResponseCode(200)
             ->setHeader('Pragma', 'public', true)
             ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate', true)
             ->setHeader('Content-type', 'application/pdf', true)
             ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
             ->setHeader('Last-Modified', date('r'))
             ->setBody($output);

        return $this;
    }

    /**
     * Checks if any warnings were received while processing the shipments and/or orders. If any warnings are found they
     * are added to the adminhtml session as a notice.
     *
     * @return $this
     */
    protected function _checkForWarnings()
    {
        /**
         * Check if any warnings were registered
         */
        $cifWarnings = Mage::registry('postnl_cif_warnings');

        if (is_array($cifWarnings) && !empty($cifWarnings)) {
            $this->_addWarningMessages($cifWarnings, $this->__('PostNL replied with the following warnings:'));
        }

        $warnings = $this->getWarnings();

        if (!empty($warnings)) {
            $this->_addWarningMessages(
                $warnings,
                $this->__('The following shipments or orders could not be processed:')
            );
        }

        return $this;
    }

    /**
     * Add an array of warning messages to the adminhtml session.
     *
     * @param        $warnings
     * @param string $headerText
     *
     * @return $this
     * @throws TIG_PostNL_Exception
     */
    protected function _addWarningMessages($warnings, $headerText = '')
    {
        $helper = Mage::helper('postnl');

        /**
         * Create a warning message to display to the merchant.
         */
        $warningMessage = $headerText;
        $warningMessage .= '<ul class="postnl-warning">';

        /**
         * Add each warning to the message.
         */
        foreach ($warnings as $warning) {
            /**
             * Warnings must have a description.
             */
            if (!array_key_exists('description', $warning)) {
                continue;
            }

            /**
             * Codes are optional for warnings, but must be present in the array. If no code is found in the warning we
             * add an empty one.
             */
            if (!array_key_exists('code', $warning)) {
                $warning['code'] = null;
            }

            /**
             * Get the formatted warning message.
             */
            $warningText = $helper->getSessionMessage(
                $warning['code'],
                'warning',
                $this->__($warning['description'])
            );

            /**
             * Prepend the warning's entity ID if present.
             */
            if (!empty($warning['entity_id'])) {
                $warningText = $warning['entity_id'] . ': ' . $warningText;
            }

            /**
             * Build the message proper.
             */
            $warningMessage .= '<li>' . $warningText . '</li>';
        }

        $warningMessage .= '</ul>';

        /**
         * Add the warnings to the session.
         */
        Mage::helper('postnl')->addSessionMessage('adminhtml/session', null, 'notice',
            $warningMessage
        );

        return $this;
    }

    /**
     * Checks if the specified actions are allowed.
     *
     * @param array $actions
     *
     * @throws TIG_PostNL_Exception
     *
     * @return bool
     */
    protected function _checkIsAllowed($actions = array())
    {
        $helper = Mage::helper('postnl');
        $isAllowed = $helper->checkIsPostnlActionAllowed($actions, false);

        return $isAllowed;
    }
}