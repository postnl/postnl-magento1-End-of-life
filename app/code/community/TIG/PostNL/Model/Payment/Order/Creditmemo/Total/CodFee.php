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
}