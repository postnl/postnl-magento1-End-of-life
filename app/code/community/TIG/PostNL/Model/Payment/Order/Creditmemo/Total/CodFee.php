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
class TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Get the COD fee total amount.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $fee     = $creditmemo->getPostnlCodFee();
        $baseFee = $creditmemo->getBasePostnlCodFee();

        /**
         * If the creditmemo has a fee already, we only need to set the totals. This is the case for existing
         * creditmemos that are being viewed.
         */
        if ($fee && $baseFee) {
            $creditmemo->setPostnlCodFee($fee)
                       ->setBasePostnlCodFee($baseFee)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

            $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
                  ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

            return $this;
        }

        /**
         * If we are currently in the backend and logged in, we need to check the POST parameters to see if any fee
         * amount is to be refunded.
         */
        if (Mage::helper('postnl')->isAdmin() && Mage::getSingleton('admin/session')->isLoggedIn()) {
            /**
             * This is unfortunately the only way to determine the fee amount that needs to be refunded without
             * rewriting a core class. If anybody knows of a better way, please let us know at
             * servicedesk@totalinternetgroup.nl.
             */
            $request = Mage::app()->getRequest();
            $creditmemoParameters = $request->getParam('creditmemo', array());

            if (isset($creditmemoParameters['postnl_cod_fee'])
                && $creditmemoParameters['postnl_cod_fee'] !== null
                && $creditmemoParameters['postnl_cod_fee'] !== ''
            ) {
                /**
                 * Get the fee amounts that are to be refunded.
                 */
                $baseFee = (float) $creditmemoParameters['postnl_cod_fee'];
                $fee     = $baseFee * $order->getBaseToOrderRate();

                /**
                 * Round the fee amounts that are already refunded and add the fee amount that is to be refunded.
                 */
                $store               = $creditmemo->getStore();
                $roundedTotalFee     = $store->roundPrice($order->getPostnlCodFeeRefunded()) + $store->roundPrice($fee);
                $roundedTotalBaseFee = $store->roundPrice($order->getBasePostnlCodFeeRefunded())
                                     + $store->roundPrice($baseFee);

                /**
                 * If the total amount refuned exceeds the available fee amount, we have a rounding error. Modify the
                 * fee amounts accordingly.
                 */
                if ($roundedTotalFee > $order->getPostnlCodFee()) {
                    $fee -= 0.0001;
                }

                if ($roundedTotalBaseFee + $store->roundPrice($baseFee) > $order->getBasePostnlCodFee()) {
                    $baseFee -= 0.0001;
                }

                /**
                 * Update the creditmemo totals with the new amounts.
                 */
                $creditmemo->setPostnlCodFee($fee)
                           ->setBasePostnlCodFee($baseFee)
                           ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                           ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

                $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
                      ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

                return $this;
            }
        }

        /**
         * If none of the above are true, we are creating a new creditmemo and need to show the fee amounts that may be
         * refunded (if any).
         */
        $fee     = $order->getPostnlCodFee() - $order->getPostnlCodFeeRefunded();
        $baseFee = $order->getBasePostnlCodFee() - $order->getBasePostnlCodFeeRefunded();

        if ($fee && $baseFee) {
            $creditmemo->setPostnlCodFee($fee)
                       ->setBasePostnlCodFee($baseFee)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

            $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
                  ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

            return $this;
        }

        return $this;
    }
}