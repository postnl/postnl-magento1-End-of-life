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
class TIG_PostNL_Model_Payment_Service
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
        $invoice = $this->_initInvoice($shipment);
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
     *
     * @return Mage_Sales_Model_Order_Invoice
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _initInvoice(Mage_Sales_Model_Order_Shipment $shipment)
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
        if (!$order->canInvoice()) {
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
        $qtys = $this->_getItemQtys($shipment);

        /**
         * Create the invoice.
         */
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
        if (!$invoice->getTotalQty()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Cannot create an invoice without products.'),
                'POSTNL-0162'
            );
        }

        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Get an array of order items and quantities that should be invoiced.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getItemQtys(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $shipmentItems = $shipment->getItemsCollection();
        $qtys = array();

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($shipmentItems as $item) {
            if (!$item->getOrderItem()->canInvoice()) {
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