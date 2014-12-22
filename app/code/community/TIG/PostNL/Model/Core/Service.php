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
class TIG_PostNL_Model_Core_Service
{
    /**
     * Registers an invoice based on a shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function registerInvoiceFromShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        /**
         * Create and register the invoice.
         */
        $invoice = $this->initInvoice($shipment);
        if (!$invoice) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'An error occurred while creating an invoice for this shipment.'
                ),
                'POSTNL-0166'
            );
        }

        $invoice->register();

        $order = $invoice->getOrder();

        $order->setIsInProcess(true);

        /**
         * Save all related objects (invoice, order and shipment).
         */
        $transactionSave = Mage::getModel('core/resource_transaction')
                               ->addObject($invoice)
                               ->addObject($order)
                               ->addObject($shipment);
        $transactionSave->save();

        return $this;
    }

    /**
     * Initialize invoice model instance.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param boolean                         $createDummyInvoice
     *
     * @return Mage_Sales_Model_Order_Invoice
     *
     * @throws TIG_PostNL_Exception
     */
    public function initInvoice(Mage_Sales_Model_Order_Shipment $shipment, $createDummyInvoice = false)
    {
        $order = $shipment->getOrder();

        /**
         * Check order existing.
         */
        if (!$order->getId()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to create an invoice for this shipment due to the order missing.'),
                'POSTNL-0164'
            );
        }
        /**
         * Check invoice create availability.
         */
        if (!$createDummyInvoice && !$order->canInvoice()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Unable to create an invoice for this shipment because the order cannot be invoiced.'
                ),
                'POSTNL-0165'
            );
        }

        /**
         * Get an array of order items and their quantities to invoice.
         */
        $qtys = $this->_getItemQtys($shipment, $createDummyInvoice);

        /**
         * Create the invoice.
         */
        if (!$createDummyInvoice) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
        } else {
            $invoice = $this->_prepareDummyInvoice($order, $qtys);
        }

        if (!$invoice->getTotalQty()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Cannot create an invoice without products.'),
                'POSTNL-0162'
            );
        }

        Mage::unregister('current_invoice');
        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Prepares a dummy invoice. This invoice is identical to a regular invoice, except that none of the items will
     * actually be invoiced.
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $qtys
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _prepareDummyInvoice($order, $qtys = array())
    {
        $serviceOrder = Mage::getModel('sales/service_order', $order);
        if (method_exists($serviceOrder, 'updateLocaleNumbers')) {
            Mage::getModel('sales/service_order', $order)
                ->updateLocaleNumbers($qtys);
        }

        $convertor = Mage::getModel('sales/convert_order');

        $dummyPaymentMethod = Mage::getModel('postnl_core/service_paymentMethodDummy');

        $dummyPayment = Mage::getModel('postnl_core/service_paymentDummy');
        $dummyPayment->setMethod('postnl_dummy')
                     ->setMethodInstance($dummyPaymentMethod);

        $dummyOrder = Mage::getModel('postnl_core/service_orderDummy');
        $dummyOrder->setData($order->getData())
                   ->setSubtotalInvoiced(0)
                   ->setBaseSubtotalInvoiced(0)
                   ->setTaxInvoiced(0)
                   ->setHiddenTaxInvoiced(0)
                   ->setBaseTaxInvoiced(0)
                   ->setBaseHiddenTaxInvoiced(0)
                   ->setPayment($dummyPayment);

        $invoice = Mage::getModel('postnl_core/service_invoiceDummy');
        $invoice->setOrder($dummyOrder)
                ->setStoreId($dummyOrder->getStoreId())
                ->setCustomerId($dummyOrder->getCustomerId())
                ->setBillingAddressId($dummyOrder->getBillingAddressId())
                ->setShippingAddressId($dummyOrder->getShippingAddressId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_invoice', $order, $invoice);

        /**
         * @var Mage_Sales_Model_Order_Item $orderItem
         */
        $totalQty = 0;
        foreach ($order->getAllItems() as $orderItem) {
            $item = $convertor->itemToInvoiceItem($orderItem);

            $qty = 0;
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } else if (!empty($qtys)) {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float) $qtys[$orderItem->getId()];
                }
            } else {
                $qty = $orderItem->getQtyToInvoice();
            }

            $orderItem->setQtyInvoiced(0)
                      ->setRowInvoiced(0)
                      ->setBaseRowInvoiced(0);

            $totalQty += $qty;
            $item->setData('qty', $qty);
            $invoice->addItem($item);
        }

        $invoice->setTotalQty($totalQty);

        $invoice->collectTotals();

        return $invoice;
    }

    /**
     * Get an array of order items and quantities that should be invoiced.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param boolean                         $createDummyInvoice
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getItemQtys(Mage_Sales_Model_Order_Shipment $shipment, $createDummyInvoice = false)
    {
        $shipmentItems = $shipment->getAllItems();
        $qtys = array();

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($shipmentItems as $item) {
            if (!$createDummyInvoice && !$item->getOrderItem()->canInvoice()) {
                throw new TIG_PostNL_Exception(
                    Mage::helper('postnl')->__('Order item #%s could not be invoiced.', $item->getOrderItemId()),
                    'POSTNL-0163'
                );
            }

            $qtys[$item->getOrderItemId()] = $item->getQty();
        }

        return $qtys;
    }
}