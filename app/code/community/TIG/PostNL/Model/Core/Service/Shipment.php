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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_Service_Shipment
{
    /**
     * Xpath to the 'print_return_and_shipping_label' setting.
     */
    const XPATH_PRINT_RETURN_AND_SHIPPING_LABEL = 'postnl/returns/print_return_and_shipping_label';

    /**
     * Xpath to 'show_label' setting.
     */
    const XPATH_SHOW_LABEL = 'postnl/packing_slip/show_label';

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
     * @return bool
     */
    public function hasWarnings()
    {
        $warnings = $this->getWarnings();
        if (count($warnings) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function resetWarnings()
    {
        $this->setWarnings(array());

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
     * Gets the postnl shipment associated with a shipment
     *
     * @param int $shipmentId
     *
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function getPostnlShipment($shipmentId)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipmentId, 'shipment_id');

        return $postnlShipment;
    }

    /**
     * Load a shipment based on a shipment ID.
     *
     * @param int     $shipmentId
     * @param boolean $loadPostnlShipments
     *
     * @return boolean|Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment
     */
    public function loadShipment($shipmentId, $loadPostnlShipments)
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
            $shipment = $this->getPostnlShipment($shipmentId);
            if (!$shipment || !$shipment->getId()) {
                return false;
            }

            $shippingMethod = $shipment->getShipment()->getOrder()->getShippingMethod();
        }

        /**
         * Check if the shipping method used is allowed
         */
        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper = Mage::helper('postnl/carrier');
        if (!$helper->isPostnlShippingMethod($shippingMethod)) {
            return false;
        }

        return $shipment;
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
    public function loadAndCheckShipments($shipmentIds, $loadPostnlShipments = false, $throwException = true)
    {
        if (!is_array($shipmentIds)) {
            $shipmentIds = array($shipmentIds);
        }

        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper                = Mage::helper('postnl/carrier');
        /** @var Mage_Core_Model_Resource $resource */
        $resource              = Mage::getSingleton('core/resource');
        $postnlShippingMethods = $helper->getPostnlShippingMethods();

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
            /** @var TIG_PostNL_Model_Core_Resource_Shipment_Collection $shipments */
            $shipments = Mage::getResourceModel('postnl_core/shipment_collection')
                             ->addFieldToFilter('shipment_id', array('in' => $shipmentIds))
                             ->addFieldToFilter(
                                 'order.shipping_method',
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
            /** @var TIG_PostNL_Model_Core_Resource_Shipment_Collection $shipments */
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                             ->addFieldToFilter('main_table.entity_id', array('in' => $shipmentIds))
                             ->addFieldToFilter(
                                 'order.shipping_method',
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
        $adapter = $resource->getConnection('core_read');
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
                    $helper->__(
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
                    'description' => $helper->__(
                        'This action is not available for shipment #%s, because it was not shipped using PostNL.',
                        $shipmentIncrementId
                    ),
                )
            );
        }

        return $shipments;
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
    public function createShipment($order)
    {
        $helper = Mage::helper('postnl');

        if (is_numeric($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        if (!is_object($order) || !($order instanceof Mage_Sales_Model_Order) || !$order->getId()) {
            throw new InvalidArgumentException(
                'Order must be an instance of Mage_Sales_Model_Order or a valid entity ID.'
            );
        }

        if (!$order->canShip()) {
            throw new TIG_PostNL_Exception(
                $helper->__('Order #%s cannot be shipped at this time.', $order->getIncrementId()),
                'POSTNL-0015'
            );
        }

        /** @var Mage_Sales_Model_Service_Order $serviceModel */
        $serviceModel = Mage::getModel('sales/service_order', $order);
        $shipment = $serviceModel->prepareShipment($this->_getItemQtys($order));

        $shipment->register();
        $this->_saveShipment($shipment);

        return $shipment->getId();
    }

    /**
     * Create shipments for an array of order IDs
     *
     * @param array   $orderIds
     * @param boolean $loadExisting     Flag to determine if existing shipments should be loaded. If set to false, an
     *                                  error will be thrown for shipments that have already been shipped.
     * @param boolean $registerExisting
     *
     * @return array
     */
    public function createShipments(array $orderIds, $loadExisting = false, $registerExisting = true)
    {
        /** @var TIG_PostNL_Helper_Data $helper */
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
                    'description' => $helper->__(
                        'This action is not available for order #%s, because it was not placed using PostNL.',
                        $incrementId
                    ),
                )
            );
        }

        /**
         * Create the shipments.
         *
         * @var Mage_Sales_Model_Order $order
         */
        $shipmentIds = array();
        $existingShipmentsLoaded = array();
        foreach ($orders as $order) {
            try {
                $shipmentIds[] = $this->createShipment($order);
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

                    continue;
                }
                /**
                 * If any shipments already exist, get their IDs so they can be processed.
                 */
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection');
                $shipmentCollection->addFieldToSelect('entity_id')
                                   ->addFieldToFilter('order_id', $order->getId());

                $orderShipmentIds = $shipmentCollection->getColumnValues('entity_id');

                if ($shipmentCollection->getSize() > 0) {
                    $shipmentIds = array_merge($orderShipmentIds, $shipmentIds);

                    if ($registerExisting) {
                        $existingShipmentsLoaded = array_merge($orderShipmentIds, $existingShipmentsLoaded);
                    }
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
            }
        }

        if ($registerExisting) {
            Mage::unregister('postnl_existing_shipments_loaded');
            Mage::register('postnl_existing_shipments_loaded', $existingShipmentsLoaded);
        }

        return $shipmentIds;
    }

    /**
     * Retrieves the shipping label for a given shipment ID.
     *
     * If the shipment has a stored label, it is returned. Otherwise a new one is generated.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     * @param boolean                                                        $confirm Optional parameter to also
     *                                                                                confirm the shipment
     * @param boolean|null                                                   $includeReturnLabels
     *
     * @return TIG_PostNL_Model_Core_Shipment_Label[]
     *
     * @throws TIG_PostNL_Exception
     */
    public function getLabels($shipment, $confirm = false, $includeReturnLabels = null)
    {
        if (is_null($includeReturnLabels)) {
            $includeReturnLabels = Mage::getStoreConfigFlag(
                self::XPATH_PRINT_RETURN_AND_SHIPPING_LABEL,
                $shipment->getStoreId()
            );

            /**
             * Return labels may only be included if the current admin user is allowed to print them.
             */
            if (!$this->_checkIsAllowed(array('print_return_labels'))) {
                $includeReturnLabels = false;
            }
        }

        /**
         * Check if printing return labels is allowed.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->isReturnsEnabled($shipment->getStoreId())) {
            $includeReturnLabels = false;
        }

        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = $this->getPostnlShipment($shipment->getId());
        } else {
            $postnlShipment = $shipment;
        }

        /**
         * Check if the shipment already has any labels. If so, return those. If we also need to confirm the shipment,
         * do that first.
         */
        if ($postnlShipment->hasLabels()) {
            if ($confirm === true && !$postnlShipment->isConfirmed() && $postnlShipment->canConfirm()) {
                $this->confirmShipment($postnlShipment);
            }
        } else {
            /**
             * Generate the required labels.
             */
            $postnlShipment = $this->_generateLabels($shipment, $postnlShipment, $confirm);
        }

        $labels = $postnlShipment->getlabels($includeReturnLabels);

        if (!$postnlShipment->getLabelsPrinted()) {
            $postnlShipment->setLabelsPrinted(true);
        }

        if ($includeReturnLabels && !$postnlShipment->getReturnLabelsPrinted()) {
            $postnlShipment->setReturnLabelsPrinted(true);
        }

        if ($postnlShipment->hasDataChanges()) {
            $postnlShipment->save();
        }

        return $labels;
    }

    /**
     * Get all return labels for a shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return TIG_PostNL_Model_Core_Shipment_Label[]|false
     */
    public function getReturnLabels($shipment)
    {
        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = $this->getPostnlShipment($shipment->getId());
        } else {
            $postnlShipment = $shipment;
        }

        if (!$postnlShipment->hasReturnBarcode() && !$postnlShipment->canGenerateReturnBarcode()) {
            return false;
        }

        if ($postnlShipment->hasReturnLabels()) {
            return $postnlShipment->getReturnLabels();
        }

        $postnlShipment = $this->_generateLabels($shipment, $postnlShipment, false);

        $labels = $postnlShipment->getReturnLabels();

        if (!$postnlShipment->getLabelsPrinted()) {
            $postnlShipment->setLabelsPrinted(true);
        }

        if (!$postnlShipment->getReturnLabelsPrinted()) {
            $postnlShipment->setReturnLabelsPrinted(true);
        }

        if ($postnlShipment->hasDataChanges()) {
            $postnlShipment->save();
        }

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
    public function confirmShipment($shipment)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');

        /**
         * Load the PostNL shipment.
         */
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $postnlShipment = $this->getPostnlShipment($shipment->getId());
        } else {
            $postnlShipment = $shipment;
        }

        /**
         * Prevent EU shipments from being confirmed if their labels are not yet printed.
         */
        if ($postnlShipment->isEuShipment() && !$postnlShipment->getLabelsPrinted()) {
            throw new TIG_PostNL_Exception(
                $helper->__(
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

        $printReturnLabel = $helper->isReturnsEnabled($shipment->getStoreId());
        if ($printReturnLabel && !$postnlShipment->hasReturnBarcode() && $postnlShipment->canGenerateReturnBarcode()) {
            $postnlShipment->generateReturnBarcode();
        }

        if ($postnlShipment->getConfirmStatus() === $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            /**
             * The shipment is already confirmed.
             */
            $this->addWarning(
                array(
                    'entity_id'   => $postnlShipment->getShipmentId(),
                    'code'        => 'POSTNL-0017',
                    'description' => $helper->__(
                        'Shipment #%s has already been confirmed.',
                        $postnlShipment->getShipment()->getIncrementId()
                    ),
                )
            );

            return $this;
        }

        if (!$postnlShipment->canConfirm()) {
            /**
             * The shipment cannot be confirmed at this time.
             */
            throw new TIG_PostNL_Exception(
                $helper->__(
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
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return $this
     * @throws Exception
     * @throws TIG_PostNL_Exception
     * @throws TIG_PostNL_Model_Core_Cif_Exception
     */
    public function updateShippingStatus(TIG_PostNL_Model_Core_Shipment $postnlShipment)
    {
        $helper = Mage::helper('postnl');

        /**
         * Only confirmed shipments cna be updated.
         */
        if (!$postnlShipment->isConfirmed()) {
            throw new TIG_PostNL_Exception(
                $helper->__(
                    'The shipping status of shipment #%s cannot be updated, because it has not yet been confirmed.',
                    $postnlShipment->getShipmentIncrementId()
                ),
                'POSTNL-0206'
            );
        }

        /**
         * Check if the shipment's shipping status or return status may be updated.
         */
        if (!$postnlShipment->canUpdateShippingStatus() && !$postnlShipment->canUpdateReturnStatus()) {
            throw new TIG_PostNL_Exception(
                $helper->__(
                    'The shipping status of shipment #%s cannot be updated.',
                    $postnlShipment->getShipmentIncrementId()
                ),
                'POSTNL-0220'
            );
        }

        if ($postnlShipment->canUpdateShippingStatus()) {
            $postnlShipment->updateShippingStatus(true);
        }

        if ($postnlShipment->canUpdateReturnStatus()) {
            $postnlShipment->updateReturnStatus(true);
        }

        if ($postnlShipment->hasDataChanges()) {
            $postnlShipment->save();
        }

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
    public function getMassLabelsOutput($shipments)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');

        /**
         * Get the labels from CIF.
         */
        $labels = array();
        foreach ($shipments as $shipment) {
            try {
                $printReturnLabels = $helper->canPrintReturnLabelsWithShippingLabels(
                    $shipment->getStoreId()
                );

                $shipmentLabels = $this->getLabels($shipment, true, $printReturnLabels);
                $labels = array_merge($labels, $shipmentLabels);
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                $helper->parseCifException($e);

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
        /** @var TIG_PostNL_Model_Core_Label $label */
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
    public function getMassPackingSlipsOutput($shipments)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');

        /**
         * Get the packing slip model.
         */
        /** @var TIG_PostNL_Model_Core_PackingSlip $packingSlipModel */
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
                        $helper->__(
                            'Approaching memory limit for this operation. Please select fewer shipments and try ' .
                            'again.'
                        ),
                        'POSTNL-0170'
                    );
                }

                $printReturnLabels = $helper->canPrintReturnLabelsWithShippingLabels(
                    $shipment->getStoreId()
                );

                $showLabelsOption = Mage::getStoreConfig(self::XPATH_SHOW_LABEL, Mage_Core_Model_App::ADMIN_STORE_ID);
                if ($showLabelsOption == 'none') {
                    $shipmentLabels = array();
                } else {
                    $shipmentLabels = $this->getLabels($shipment, true, $printReturnLabels);
                }

                $packingSlipModel->createPdf($shipmentLabels, $shipment, $pdf);
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                $helper->parseCifException($e);

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

        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper = Mage::helper('postnl/carrier');
        $postnlShippingMethods = $helper->getPostnlShippingMethods();

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
     * Generate shipping labels for this given shipment. This method includes the functionality required to prepare the
     * shipment for generating labels if required.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param TIG_PostNL_Model_Core_Shipment  $postnlShipment
     * @param boolean                         $confirm
     *
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _generateLabels($shipment, $postnlShipment, $confirm = false)
    {
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

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $printReturnLabel = $helper->isReturnsEnabled($postnlShipment->getStoreId());
        if ($printReturnLabel && $postnlShipment->canGenerateReturnBarcode()) {
            $postnlShipment->generateReturnBarcode();
        }

        if (true === $confirm
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
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment->getOrder()->setIsInProcess(true);
        /** @var Mage_Core_Model_Resource_Transaction $transaction */
        $transaction = Mage::getModel('core/resource_transaction');
        $transaction->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

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
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $isAllowed = $helper->checkIsPostnlActionAllowed($actions, false);

        return $isAllowed;
    }
}
