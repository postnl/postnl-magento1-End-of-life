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
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $fee     = $creditmemo->getPostnlCodFee();
        $baseFee = $creditmemo->getBasePostnlCodFee();

        if ($fee && $baseFee) {
            $creditmemo->setPostnlCodFee($fee)
                       ->setBasePostnlCodFee($baseFee)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

            $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
                  ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

            return $this;
        }

        if (Mage::helper('postnl')->isAdmin() && Mage::getSingleton('admin/session')->isLoggedIn()) {
            $request = Mage::app()->getRequest();
            $creditmemoParameters = $request->getParam('creditmemo', array());

            if (isset($creditmemoParameters['postnl_cod_fee'])
                && $creditmemoParameters['postnl_cod_fee'] !== null
                && $creditmemoParameters['postnl_cod_fee'] !== ''
            ) {
                $fee     = (float) $creditmemoParameters['postnl_cod_fee'];
                $baseFee = $creditmemo->getStore()->convertPrice($fee, false);

                $store = $creditmemo->getStore();
                $roundedTotalFee = $store->roundPrice($order->getPostnlCodFeeRefunded()) + $store->roundPrice($fee);
                $roundedTotalBaseFee = $store->roundPrice($order->getBasePostnlCodFeeRefunded())
                                     + $store->roundPrice($baseFee);

                if ($roundedTotalFee > $order->getPostnlCodFee()) {
                    $fee -= 0.0001;
                }

                if ($roundedTotalBaseFee + $store->roundPrice($baseFee) > $order->getBasePostnlCodFee()) {
                    $baseFee -= 0.0001;
                }

                $creditmemo->setPostnlCodFee($fee)
                           ->setBasePostnlCodFee($baseFee)
                           ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                           ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

                $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
                      ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

                return $this;
            }
        }

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

    public function _collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $allowedAmount          = $order->getShippingAmount()-$order->getShippingRefunded();
        $baseAllowedAmount      = $order->getBaseShippingAmount()-$order->getBaseShippingRefunded();

        $shipping               = $order->getShippingAmount();
        $baseShipping           = $order->getBaseShippingAmount();
        $shippingInclTax        = $order->getShippingInclTax();
        $baseShippingInclTax    = $order->getBaseShippingInclTax();

        $isShippingInclTax = Mage::getSingleton('tax/config')->displaySalesShippingInclTax($order->getStoreId());

        /**
         * Check if shipping amount was specified (from invoice or another source).
         * Using has magic method to allow setting 0 as shipping amount.
         */
        if ($creditmemo->hasBaseShippingAmount()) {
            $baseShippingAmount = Mage::app()->getStore()->roundPrice($creditmemo->getBaseShippingAmount());
            if ($isShippingInclTax && $baseShippingInclTax != 0) {
                $part = $baseShippingAmount/$baseShippingInclTax;
                $shippingInclTax    = Mage::app()->getStore()->roundPrice($shippingInclTax*$part);
                $baseShippingInclTax= $baseShippingAmount;
                $baseShippingAmount = Mage::app()->getStore()->roundPrice($baseShipping*$part);
            }
            /*
             * Rounded allowed shipping refund amount is the highest acceptable shipping refund amount.
             * Shipping refund amount shouldn't cause errors, if it doesn't exceed that limit.
             * Note: ($x < $y + 0.0001) means ($x <= $y) for floats
             */
            if ($baseShippingAmount < Mage::app()->getStore()->roundPrice($baseAllowedAmount) + 0.0001) {
                /*
                 * Shipping refund amount should be equated to allowed refund amount,
                 * if it exceeds that limit.
                 * Note: ($x > $y - 0.0001) means ($x >= $y) for floats
                 */
                if ($baseShippingAmount > $baseAllowedAmount - 0.0001) {
                    $shipping     = $allowedAmount;
                    $baseShipping = $baseAllowedAmount;
                } else {
                    if ($baseShipping != 0) {
                        $shipping = $shipping * $baseShippingAmount / $baseShipping;
                    }
                    $shipping     = Mage::app()->getStore()->roundPrice($shipping);
                    $baseShipping = $baseShippingAmount;
                }
            } else {
                $baseAllowedAmount = $order->getBaseCurrency()->format($baseAllowedAmount,null,false);
                Mage::throwException(
                    Mage::helper('sales')->__('Maximum shipping amount allowed to refund is: %s', $baseAllowedAmount)
                );
            }
        } else {
            if ($baseShipping != 0) {
                $allowedTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseAllowedTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();

                $shippingInclTax = Mage::app()->getStore()->roundPrice($allowedAmount + $allowedTaxAmount);
                $baseShippingInclTax = Mage::app()->getStore()->roundPrice($baseAllowedAmount + $baseAllowedTaxAmount);
            }
            $shipping           = $allowedAmount;
            $baseShipping       = $baseAllowedAmount;
        }

        $creditmemo->setShippingAmount($shipping);
        $creditmemo->setBaseShippingAmount($baseShipping);
        $creditmemo->setShippingInclTax($shippingInclTax);
        $creditmemo->setBaseShippingInclTax($baseShippingInclTax);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$shipping);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$baseShipping);
        return $this;
    }
}