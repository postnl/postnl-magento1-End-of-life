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
class TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder
{
    /**
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
         * Check if this order was placed using PostNL.
         */
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        $shippingMethod = $order->getShippingMethod();

        /**
         * If this order was not placed with PostNL, remove any PakjeGemak addresses that may have been saved.
         */
        if (!in_array($shippingMethod, $postnlShippingMethods)) {
            $this->_removePakjeGemakAddress($order);
            return $this;
        }

        /**
         * Get the PostNL order associated with this order.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($order->getQuoteId(), 'quote_id');

        /**
         * If this order is not being shipped to the Netherlands, remove any PakjeGemak addresses that may have been
         * saved and delete the PostNL order.
         */
        $shippingCountry = $order->getShippingAddress()->getCountryId();
        if ($shippingCountry != 'NL') {
            $this->_removePakjeGemakAddress($order);
            $postnlOrder->delete();
            return $this;
        }

        /**
         * If no such PostNL order exists or if the PostNL order has already been updated we don't need to do anything.
         */
        if (!$postnlOrder->getId() || $postnlOrder->getOrderId() || !$postnlOrder->getIsActive()) {
            return $this;
        }

        $postnlOrder->setOrderId($order->getId())
                    ->setIsActive(false)
                    ->save();

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
         * This is required for some PSP extensions which will not save the PakjeGemak address otherwise.
         *
         * @since v1.2.1
         */
        $orderAddress->save();

        return $this;
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