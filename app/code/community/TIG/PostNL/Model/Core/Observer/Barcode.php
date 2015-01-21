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
class TIG_PostNL_Model_Core_Observer_Barcode
{
    /**
     * Generates a barcode for the shipment if it is new
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event sales_order_shipment_save_after
     *
     * @observer postnl_shipment_generate_barcode
     */
    public function generateBarcode(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('postnl/cif');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = $observer->getShipment();

        /**
         * Check if this shipment was placed using PostNL.
         */
        $shippingMethod = $shipment->getOrder()->getShippingMethod();

        /**
         * If this shipment's order was not placed with PostNL, remove any PakjeGemak addresses that may have been
         * saved.
         */
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return $this;
        }

        /**
         * Check if a postnl shipment exists for this shipment.
         */
        if (Mage::helper('postnl/cif')->postnlShipmentExists($shipment->getId())) {
            return $this;
        }

        /**
         * Create a new postnl shipment entity.
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->setShipmentId($shipment->getId());

        /**
         * Check if this shipment has an associated PostNL Order. If so, copy it's data.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($shipment->getOrderId(), 'order_id');

        if ($postnlOrder->getId()) {
            if ($postnlOrder->hasConfirmDate()) {
                $confirmDate = new DateTime($postnlOrder->getConfirmDate());
                $postnlShipment->setConfirmDate($confirmDate->format('Y-m-d H:i:s'));
            }

            if ($postnlOrder->hasDeliveryDate()) {
                $deliveryDate = new DateTime($postnlOrder->getDeliveryDate());
                $postnlShipment->setDeliveryDate($deliveryDate->format('Y-m-d H:i:s'));
            }

            if ($postnlOrder->getIsPakjeGemak()) {
                $postnlShipment->setIsPakjeGemak($postnlOrder->getIsPakjeGemak());
            }

            if ($postnlOrder->getIsPakketautomaat()) {
                $postnlShipment->setIsPakketautomaat($postnlOrder->getIsPakketautomaat());
            }

            if ($postnlOrder->hasExpectedDeliveryTimeStart()) {
                $postnlShipment->setExpectedDeliveryTimeStart($postnlOrder->getExpectedDeliveryTimeStart());
            }

            if ($postnlOrder->hasExpectedDeliveryTimeEnd()) {
                $postnlShipment->setExpectedDeliveryTimeEnd($postnlOrder->getExpectedDeliveryTimeEnd());
            }
        }

        /**
         * We need an ID in order to save the barcodes.
         */
        $postnlShipment->save();

        /**
         * Barcode generation needs to be tried separately. This functionality may throw a valid exception which case it
         * needs to be tried again later without preventing the shipment from being created. This may happen when CIF is
         * overburdened.
         */
        try {
            $postnlShipment->saveAdditionalShippingOptions();

            if ($postnlShipment->canGenerateBarcode()) {
                $postnlShipment->generateBarcodes();
            }

            $printReturnLabel = $helper->isReturnsEnabled($postnlShipment->getStoreId());
            if ($printReturnLabel && $postnlShipment->canGenerateReturnBarcode()) {
                $postnlShipment->generateReturnBarcode();
            }
        } catch (Exception $e) {
            $helper->logException($e);
        }

        $postnlShipment->save();

        return $this;
    }
}
