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
class TIG_PostNL_Model_Payment_Observer_Cod
{
    /**
     * Xpath to COD auto invoice setting.
     */
    const XPATH_COD_AUTO_INVOICE = 'postnl/cod/auto_invoice';

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function autoInvoice(Varien_Event_Observer $observer)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $shipment
         */
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
            Mage::getModel('postnl_core/service')->registerInvoiceFromShipment($shipment->getShipment());
            $order->addStatusHistoryComment(
                $helper->__("This order has been automatically invoiced by the PostNL COD payment method.")
            );
        } catch (Exception $e) {
            $helper->logException($e);
        }

        return $this;
    }
}