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
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
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
     * Error counter used by certain actions.
     *
     * @var int
     */
    protected $_errors = 0;

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
     * Creates a shipment of an order containing all available items.
     *
     * @param Mage_Sales_Model_Order|int $order
     *
     * @return int
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _createShipment($order)
    {
        if (is_numeric($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!is_object($order) || !($order instanceof Mage_Sales_Model_Order)) {
            throw new InvalidArgumentException(
                'Order must be an instance of Mage_Sales_Model_Order or a valid entity ID.'
            );
        }

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
     * Create shipments for an array of order IDs
     *
     * @param array   $orderIds
     * @param boolean $loadExisting Flag to determine if existing shipments should be loaded. If set to false, an error
     *                              will be thrown for shipments that have already been shipped.
     *
     * @return array
     */
    protected function _createShipments(array $orderIds, $loadExisting = false)
    {
        $helper = Mage::helper('postnl');

        /**
         * Load the requested orders. Any orders that weren't shipped using PostNL will be skipped.
         */
        $orders = $this->_loadOrders($orderIds);
        $processedOrderIds = $orders->getColumnValues('entity_id');

        /**
         * Add a warning for all orders which were skipped because they weren't shipped with PostNL.
         */
        $missingIds = array_diff($orderIds, $processedOrderIds);
        foreach ($missingIds as $missingId) {
            $incrementId = Mage::getResourceModel('sales/order')->getIncrementId($missingId);
            $this->addWarning(
                array(
                    'entity_id'   => $incrementId,
                    'code'        => 'POSTNL-0009',
                    'description' => $this->__(
                        'This action is not available for order #%s, because it was not placed using PostNL.',
                        $incrementId
                    ),
                )
            );
            $this->_errors++;
        }

        /**
         * Create the shipments.
         *
         * @var Mage_Sales_Model_Order $order
         */
        $shipmentIds = array();
        foreach ($orders as $order) {
            try {
                $shipmentIds[] = $this->_createShipment($order);
            } catch (TIG_PostNL_Exception $e) {
                if (!$loadExisting) {
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($order->getId()),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                    $this->_errors++;

                    continue;
                }
                /**
                 * If any shipments already exist, get their IDs so they can be processed.
                 */
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');
                $shipmentCollection->addFieldToSelect('entity_id')
                                   ->addFieldToFilter('order_id', $order->getId());

                if ($shipmentCollection->getSize() > 0) {
                    $shipmentIds = array_merge($shipmentCollection->getColumnValues('entity_id'), $shipmentIds);
                } else {
                    /**
                     * If no shipments exist, add a warning message indicating the process failed for this order.
                     */
                    $helper->logException($e);
                    $this->addWarning(
                        array(
                            'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($order->getId()),
                            'code'        => $e->getCode(),
                            'description' => $e->getMessage(),
                        )
                    );
                    $this->_errors++;
                }
            } catch (Exception $e) {
                $helper->logException($e);
                $this->addWarning(
                    array(
                        'entity_id'   => Mage::getResourceModel('sales/order')->getIncrementId($order->getId()),
                        'code'        => null,
                        'description' => $e->getMessage(),
                    )
                );
                $this->_errors++;
            }
        }

        return $shipmentIds;
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
     * Get the output of printing labels for an array of shipments.
     *
     * @param TIG_PostNL_Model_Core_Shipment[] $shipments
     *
     * @return string|false
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getMassLabelsOutput($shipments)
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the labels from CIF.
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

        if (!$labels) {
            return false;
        }

        /**
         * The label wills be base64 encoded strings. Convert these to a single pdf.
         */
        $label  = Mage::getModel('postnl_core/label');
        $output = $label->createPdf($labels);

        return $output;
    }

    /**
     * Get the output of printing packing slips for an array of shipments.
     *
     * @param TIG_PostNL_Model_Core_Shipment[] $shipments
     *
     * @return bool|string
     *
     * @throws Zend_Pdf_Exception
     */
    protected function _getMassPackingSlipsOutput($shipments)
    {
        $helper = Mage::helper('postnl');

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

                $shipmentLabels = $this->_getLabels($shipment, true);
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

        if (!$pdf->pages) {
            return false;
        }

        /**
         * Render the pdf as a string.
         */
        $output = $pdf->render();
        return $output;
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
             * Generate new shipping labels without confirming.
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

        $resource              = Mage::getSingleton('core/resource');
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

        /**
         * This regex will filter all non-postnl shipments.
         */
        $postnlShippingMethodsRegex = '';
        foreach ($postnlShippingMethods as $method) {
            if ($postnlShippingMethodsRegex) {
                $postnlShippingMethodsRegex .= '|';
            } else {
                $postnlShippingMethodsRegex .= '^';
            }

            $postnlShippingMethodsRegex .= "({$method})(_{0,1}[0-9]*)";
        }

        $postnlShippingMethodsRegex .= '$';

        /**
         * Get the requested shipments. Only shipments that have been shipped using PostNL will be returned.
         */
        if ($loadPostnlShipments) {
            $shipments = Mage::getResourceModel('postnl_core/shipment_collection')
                             ->addFieldToFilter('shipment_id', array('in' => $shipmentIds))
                             ->addFieldToFilter(
                                 '`order`.`shipping_method`',
                                 array(
                                     'regexp' => $postnlShippingMethodsRegex
                                 )
                             );

            $shipments->getSelect()->joinInner(
                array('order' => $resource->getTableName('sales/order')),
                '`main_table`.`order_id`=`order`.`entity_id`',
                array(
                    'shipping_method' => 'order.shipping_method',
                )
            );

            $processedShipmentIds = $shipments->getColumnValues('shipment_id');
        } else {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                             ->addFieldToFilter('main_table.entity_id', array('in' => $shipmentIds))
                             ->addFieldToFilter(
                                 '`order`.`shipping_method`',
                                 array(
                                     'regexp' => $postnlShippingMethodsRegex
                                 )
                             );

            $shipments->getSelect()->joinInner(
                array('order' => $resource->getTableName('sales/order')),
                '`main_table`.`order_id`=`order`.`entity_id`',
                array(
                    'shipping_method' => 'order.shipping_method',
                )
            );

            $processedShipmentIds = $shipments->getColumnValues('entity_id');
        }

        /**
         * Check if all requested IDs were processed.
         */
        $missingIds = array_diff($shipmentIds, $processedShipmentIds);
        if (!$missingIds) {
            return $shipments;
        }

        /**
         * If any requested shipments were not found, it's because they were not shipped using PostNL.
         */
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        foreach ($missingIds as $shipmentId) {
            /**
             * Get the shipment's increment ID. We need this, because many merchants do not know the difference between
             * increment IDs and entity IDs.
             */
            $bind    = array(':entity_id' => $shipmentId);
            $select  = $adapter->select()
                               ->from($resource->getTableName('sales/shipment'), array("increment_id"))
                               ->where('entity_id = :entity_id');

            $shipmentIncrementId = $adapter->fetchOne($select, $bind);

            if ($throwException) {
                throw new TIG_PostNL_Exception(
                    $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentIncrementId
                    ),
                    'POSTNL-0009'
                );
            }

            $this->addWarning(
                array(
                    'entity_id'   => $shipmentIncrementId,
                    'code'        => 'POSTNL-0009',
                    'description' => $this->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentIncrementId
                    ),
                )
            );
        }

        return $shipments;
    }

    /**
     * Load an order collection based on an array of order IDs. Non-PostNL orders will be skipped.
     *
     * @param array|int $orderIds
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _loadOrders($orderIds)
    {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();

        /**
         * This regex will filter all non-postnl shipments.
         */
        $postnlShippingMethodsRegex = '';
        foreach ($postnlShippingMethods as $method) {
            if ($postnlShippingMethodsRegex) {
                $postnlShippingMethodsRegex .= '|';
            } else {
                $postnlShippingMethodsRegex .= '^';
            }

            $postnlShippingMethodsRegex .= "({$method})(_{0,1}[0-9]*)";
        }

        $postnlShippingMethodsRegex .= '$';

        $orders = Mage::getResourceModel('sales/order_collection')
                      ->addFieldToFilter('entity_id', array('in' => $orderIds))
                      ->addFieldToFilter('shipping_method', array('regexp' => $postnlShippingMethodsRegex));

        return $orders;
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