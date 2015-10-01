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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder
{
    /**
     * Updates the PostNL order after the order has been placed. Also copies the PakjeGemak quote address to the order
     * as an order address or deletes it if it's no longer needed.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event sales_order_place_after
     *
     * @observer postnl_update_order
     */
    public function updatePostnlOrder(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $observer->getOrder();

        /**
         * Get the PostNL order associated with this order.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($order->getQuoteId(), 'quote_id');

        /**
         * Check if the order was placed using a PostNL shipping method.
         */
        $orderIsPostnl = Mage::helper('postnl/carrier')->isPostnlShippingMethod($order->getShippingMethod());

        /**
         * If the order was placed using a PostNL shipping method, yet does not have a PostNL order object; create one.
         * Otherwise, if the order was not placed using a PostNL shipping method, yet does have a PostNL order object;
         * delete the PostNL order object.
         */
        if ($orderIsPostnl && !$postnlOrder->getId()) {
            $this->_createPostnlOrder($postnlOrder, $order);
            return $this;
        } elseif (!$orderIsPostnl && $postnlOrder->getId()) {
            $postnlOrder->delete();
            return $this;
        }

        /**
         * Validate the PostNL order.
         */
        if (!$this->_validatePostnlOrder($postnlOrder, $order)) {
            /**
             * Always remove the PakjeGemak order if it's available.
             */
            $this->_removePakjeGemakAddress($order);

            /**
             * If the PostNL order exists, delete it.
             */
            if ($postnlOrder && $postnlOrder->getId()) {
                $postnlOrder->delete();

                /**
                 * If the order is a PostNL order, create a new one. this way we can guarantee the new PostNL order will
                 * contain the correct data.
                 */
                if ($orderIsPostnl) {
                    /**
                     * We need a new instance, because the previous instance has been deleted. Causing any further save
                     * operations on that instance to fail.
                     */
                    $postnlOrder = Mage::getModel('postnl_core/order');
                    $this->_createPostnlOrder($postnlOrder, $order);
                }
            }
            return $this;
        }

        /**
         * If no such PostNL order exists or if the PostNL order has already been updated we don't need to do anything.
         */
        if (!$postnlOrder->getId() || $postnlOrder->getOrderId()) {
            return $this;
        }

        $type = $postnlOrder->getType();
        if (!$type) {
            $type = $postnlOrder::TYPE_OVERDAG;
            $postnlOrder->setType($type);
        }

        /**
         * Update the order's shipment costs. If the order type is PGE or Avond, this will be a fee as configured in
         * system > config. Otherwise it will be set to 0.
         */
        $fee = 0;
        if ($type == $postnlOrder::TYPE_PGE || $type == $postnlOrder::TYPE_AVOND) {
            /**
             * Check whether the shipping prices are entered with or without tax.
             */
            $includingTax = false;
            if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
                $includingTax = true;
            }

            /**
             * Calculate the correct fee based on the order type.
             */
            if ($type == $postnlOrder::TYPE_PGE) {
                $fee = Mage::helper('postnl/deliveryOptions')
                           ->getExpressFee(false, $includingTax, false);
            } elseif ($type == $postnlOrder::TYPE_AVOND) {
                $fee = Mage::helper('postnl/deliveryOptions')
                           ->getEveningFee(false, $includingTax, false);
            }
        }

        /**
         * Make sure the PostNL order has at least a confirm and delivery date.
         */
        if (!$postnlOrder->getConfirmDate() || !$postnlOrder->getDeliveryDate()) {
            $this->_setDates($postnlOrder, $order);
        }


        /**
         * Update the PostNL order.
         */
        $postnlOrder->setShipmentCosts($fee)
                    ->setOrderId($order->getId())
                    ->setIsActive(false)
                    ->save();

        /**
         * Copy the PakjeGemak address to the order if this was a PakjeGemak order.
         */
        if ($postnlOrder->getIsPakjeGemak() || $postnlOrder->getIsPakketautomaat()) {
            $this->copyPakjeGemakAddressToOrder($order);
        }

        return $this;
    }

    /**
     * If a PakjeGemak address was added to the quote, this method will copy it to the order.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function copyPakjeGemakAddressToOrder(Mage_Sales_Model_Order $order)
    {
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        if (!$quote || !$quote->getId()) {
            $quote = $order->getQuote();
        }

        if (!$quote || !$quote->getId()) {
            return $this;
        }

        $quoteAddresses = $quote->getAllAddresses();

        /**
         * Check all quote addresses to see if one of them is a PakjeGemak address.
         *
         * @var Mage_Sales_Model_Quote_Address $address
         */
        $pakjeGemakAddress = false;
        foreach ($quoteAddresses as $address) {
            if ($address->getAddressType() == 'pakje_gemak') {
                $pakjeGemakAddress = $address;
            }
        }

        /**
         * If no PakjeGemak address was found we don't need to do anything else.
         */
        if (!$pakjeGemakAddress) {
            return $this;
        }

        /**
         * Convert the quote address to an order address and add it to the order.
         */
        $pakjeGemakAddress->load($pakjeGemakAddress->getId());
        $orderAddress = Mage::getModel('sales/convert_quote')->addressToOrderAddress($pakjeGemakAddress);

        $order->addAddress($orderAddress)
              ->save();

        /**
         * This is a fix for the order address missing a parent ID.
         *
         * @since v1.3.0
         */
        if (!$orderAddress->getParentId()) {
            $orderAddress->setParentId($order->getId());
        }

        /**
         * This is required for some PSP extensions which will not save the PakjeGemak address otherwise.
         *
         * @since v1.2.1
         */
        $orderAddress->save();

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event controller_action_postdispatch_checkout_onepage_saveShippingMethod
     *        |controller_action_predispatch_onestepcheckout_ajax_set_methods_separate
     *
     * @observer checkout_shipping_method_save_options
     *
     * @todo Move this functionality to the saveSelectedOption AJAX call instead.
     */
    public function saveOptions(Varien_Event_Observer $observer)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        /**
         * Get the PostNL order associated with this quote.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($quote->getId(), 'quote_id');
        if (!$postnlOrder->getId()) {
            return $this;
        }

        /**
         * Get all shipping methods that are considered to be PostNL.
         */
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        /**
         * If this order is not being shipped to the Netherlands or was not placed using PostNL, remove any options that
         * may have been saved.
         */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress
            || $shippingAddress->getCountryId() != 'NL'
            || !Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)

        ) {
            $postnlOrder->setOptions(false)
                        ->save();

            return $this;
        }

        /**
         * @var Mage_Core_Controller_Varien_Front $controller
         */
        $controller = $observer->getControllerAction();
        $options    = $controller->getRequest()->getParam('s_method_' . $shippingMethod, array());
        if (empty($options['postnl'])) {
            $options = $controller->getRequest()->getParam($shippingMethod, array());
        }

        $postnlOptions = false;
        if (isset($options['postnl'])) {
            $postnlOptions = $options['postnl'];
        }

        $postnlOrder->setOptions($postnlOptions)
                    ->validateOptions()
                    ->save();

        return $this;
    }

    /**
     * Validates the PostNl order. This is to prevent problems when quotes have been deleted from the database with
     * foreign key checks disabled.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param Mage_Sales_Model_Order      $order
     *
     * @return bool
     */
    protected function _validatePostnlOrder(TIG_PostNL_Model_Core_Order $postnlOrder, Mage_Sales_Model_Order $order)
    {
        /**
         * The PostNL order cannot already have a Magento order associated with it.
         */
        if ($postnlOrder->getOrderId()) {
            return false;
        }

        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        $utcTimeZone = new DateTimeZone('UTC');

        /**
         * Get the quote and the PostNL order's created at times.
         */
        $postnlOrderCreated = new DateTime($postnlOrder->getCreatedAt(), $utcTimeZone);
        $quoteCreated       = new DateTime($quote->getCreatedAt(), $utcTimeZone);

        /**
         * The PostNL order cannot have been created before the quote.
         */
        if ($postnlOrderCreated < $quoteCreated) {
            return false;
        }

        /**
         * Check if this order is being shipped to the Netherlands.
         */
        $shippingAddress = $order->getShippingAddress();

        if (!$shippingAddress
            || $shippingAddress->getCountryId() != 'NL'
        ) {
            return false;
        }

        /**
         * Check if the shipping method is a PostNL shipping method.
         */
        $shippingMethod = $order->getShippingMethod();
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return false;
        }

        return true;
    }

    /**
     * Creates a PostNL order for the given Magento order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param Mage_Sales_Model_Order      $order
     *
     * @return $this
     * @throws Exception
     */
    protected function _createPostnlOrder(TIG_PostNL_Model_Core_Order $postnlOrder, Mage_Sales_Model_Order $order)
    {
        $postnlOrder->setQuoteId($order->getQuoteId())
                    ->setOrderId($order->getId())
                    ->setType($postnlOrder::TYPE_OVERDAG)
                    ->setIsActive(0)
                    ->setIsCanceled(0)
                    ->setShipmentCosts(0)
                    ->setIsPakjeGemak(0)
                    ->setIsPakketautomaat(0);

        $postnlOrder = $this->_setDates($postnlOrder, $order);

        $postnlOrder->save();

        return $this;
    }

    /**
     * Set the confirm and delivery dates for a given PostNl order.
     *
     * @param TIG_PostNL_Model_Core_Order $postnlOrder
     * @param Mage_Sales_Model_Order      $order
     *
     * @return TIG_PostNL_Model_Core_Order
     */
    protected function _setDates(TIG_PostNL_Model_Core_Order $postnlOrder, Mage_Sales_Model_Order $order)
    {
        $helper = Mage::helper('postnl/deliveryOptions');
        $shippingDuration = $helper->getOrderShippingDuration($order);
        $deliveryDate = $helper->getDeliveryDate(
            $order->getCreatedAt(),
            $order->getStoreId(),
            false,
            true,
            true,
            $shippingDuration,
            true
        );

        $deliveryDate = $helper->getValidDeliveryDate($deliveryDate);

        $confirmDate = clone $deliveryDate;
        $confirmDate->sub(new DateInterval('P1D'));
        $confirmDate = $helper->getValidConfirmDate($confirmDate);

        $postnlOrder->setDeliveryDate($deliveryDate->getTimestamp())
                    ->setConfirmDate($confirmDate->getTimestamp());

        return $postnlOrder;
    }

    /**
     * Deletes any PakjeGemak addresses associated with this order.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function _removePakjeGemakAddress(Mage_Sales_Model_Order $order)
    {
        /**
         * @var Mage_Sales_Model_Order_Address $address
         */
        $addressCollection = $order->getAddressesCollection();
        foreach ($addressCollection as $address) {
            if ($address->getAddressType() == 'pakje_gemak') {
                $address->delete();
            }
        }

        return $this;
    }
}