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
class TIG_PostNL_Model_Checkout_Observer_Order
{
    /**
     * Cancels a PostNL Checkout order after it's Magento order has been cancelled.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @event order_cancel_after
     *
     * @observer postnl_cancel_checkout_order
     */
    public function cancelOrder(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order          $order
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $order = $observer->getOrder();
        $postnlOrder = Mage::getModel('postnl_core/order')->load($order->getId(), 'order_id');

        if (!$postnlOrder->getId() || !$postnlOrder->getToken()) {
            return $this;
        }

        try {
            $postnlOrder->cancel()
                        ->save();
        } catch (Exception $e) {
            Mage::helper('postnl/checkout')->logException($e);
        }

        return $this;
    }
}
