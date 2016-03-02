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
class TIG_PostNL_Model_Payment_Observer_Cod
{
    /**
     * Xpath to COD auto invoice setting.
     */
    const XPATH_COD_AUTO_INVOICE = 'postnl/cod/auto_invoice';

    /**
     * Automatically invoice a shipment after it has been delivered.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event postnl_shipment_setshippingphase_delivered
     *
     * @observer postnl_cod_auto_invoice
     */
    public function autoInvoice(Varien_Event_Observer $observer)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $shipment
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment = $observer->getShipment();

        /**
         * Auto invoicing is only allowed if the shipment has been delivered.
         */
        if ($shipment->getShippingPhase() != $shipment::SHIPPING_PHASE_DELIVERED) {
            return $this;
        }

        /**
         * Get the order and check if we can invoice it.
         */
        $order = $shipment->getShipment()->getOrder();
        if (!$order->canInvoice()) {
            return $this;
        }

        /**
         * Check if the order was placed using a PostNL COD payment method.
         */
        $paymentMethod = $order->getPayment()->getMethod();

        /** @var TIG_PostNL_Helper_Payment $helper */
        $helper = Mage::helper('postnl/payment');
        $codPaymentMethods = $helper->getCodPaymentMethods();
        if (!in_array($paymentMethod, $codPaymentMethods)) {
            return $this;
        }

        /**
         * Check if auto-invoicing is actually enabled.
         */
        $autoInvoiceEnabled = Mage::getStoreConfigFlag(self::XPATH_COD_AUTO_INVOICE, $order->getStoreId());
        if (!$autoInvoiceEnabled) {
            return $this;
        }

        try {
            /** @var TIG_PostNL_Model_Core_Service $serviceModel */
            $serviceModel = Mage::getModel('postnl_core/service');
            $serviceModel->registerInvoiceFromShipment($shipment->getShipment());
            $order->addStatusHistoryComment(
                $helper->__("This order has been automatically invoiced by the PostNL COD payment method.")
            );
        } catch (Exception $e) {
            $helper->logException($e);
        }

        return $this;
    }

    /**
     * Prevents the creation of partial shipments by comparing the total qty of the shipment with that of the order.
     *
     * Unfortunately partial shipments are not possible for orders placed using COD.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     *
     * @event sales_order_shipment_save_before
     *
     * @observer postnl_cod_prevent_partial_shipment
     */
    public function preventPartialShipment(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         * @var Mage_Sales_Model_Order $order
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $shipment = $observer->getShipment();
        $order    = $shipment->getOrder();

        /**
         * Check if the order was placed using a PostNL COD payment method.
         */
        $paymentMethod = $order->getPayment()->getMethod();

        /** @var TIG_PostNL_Helper_Payment $helper */
        $helper = Mage::helper('postnl/payment');
        $codPaymentMethods = $helper->getCodPaymentMethods();
        if (!in_array($paymentMethod, $codPaymentMethods)) {
            return $this;
        }

        $shipmentQty = $shipment->getTotalQty();
        $orderQty = 0;

        /**
         * @var Mage_Sales_Model_Order_Item $item
         */
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $orderQty += $item->getQtyOrdered();
        }

        if ($orderQty > $shipmentQty) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'It is not possible to create partial shipments for orders placed using PostNL COD. Please ' .
                    'create only full shipments.'
                ),
                'POSTNL-0179'
            );
        }

        return $this;
    }
}
