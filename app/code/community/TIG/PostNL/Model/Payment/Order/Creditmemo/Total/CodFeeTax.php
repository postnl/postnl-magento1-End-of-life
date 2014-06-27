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
class TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFeeTax extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $feeTax     = $creditmemo->getPostnlCodFeeTax();
        $baseFeeTax = $creditmemo->getBasePostnlCodFeeTax();

        if ($feeTax && $baseFeeTax) {
            $creditmemo->setPostnlCodFeeTax($feeTax)
                       ->setBasePostnlCodFeeTax($baseFeeTax)
                       ->setTaxAmount($creditmemo->getTaxAmount() + $feeTax)
                       ->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseFeeTax)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $feeTax)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeTax);

            $order->setPostnlCodFeeTaxRefunded($order->getPostnlCodFeeTaxRefunded() + $feeTax)
                  ->setBasePostnlCodFeeTaxRefunded($order->getBasePostnlCodFeeTaxRefunded() + $baseFeeTax);

            return $this;
        }

        $fee     = $creditmemo->getPostnlCodFee();
        $baseFee = $creditmemo->getBasePostnlCodFee();

        if ($fee && $baseFee) {
            $totalBaseFee = $order->getBasePostnlCodFee();
            $ratio        = $fee / $totalBaseFee;

            $totalBaseFeeTax = $order->getBasePostnlCodFeeTax();
            $baseFeeTax      = $totalBaseFeeTax * $ratio;

            $totalFeeTax = $order->getBasePostnlCodFeeTax();
            $feeTax      = $totalFeeTax * $ratio;

            $store = $creditmemo->getStore();

            $roundedTotalFeeTax = $store->roundPrice($order->getPostnlCodFeeTaxRefunded())
                                + $store->roundPrice($feeTax);
            $roundedTotalBaseFeeTax = $store->roundPrice($order->getBasePostnlCodFeeTaxRefunded())
                                    + $store->roundPrice($baseFeeTax);

            if ($roundedTotalFeeTax > $order->getPostnlCodFeeTax()) {
                $feeTax -= 0.0001;
            }

            if ($roundedTotalBaseFeeTax > $order->getBasePostnlCodFeeTax()) {
                $baseFeeTax -= 0.0001;
            }

            $creditmemo->setPostnlCodFeeTax($feeTax)
                       ->setBasePostnlCodFeeTax($baseFeeTax)
                       ->setTaxAmount($creditmemo->getTaxAmount() + $feeTax)
                       ->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseFeeTax)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $feeTax)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeTax);

            $order->setPostnlCodFeeTaxRefunded($order->getPostnlCodFeeTaxRefunded() + $feeTax)
                  ->setBasePostnlCodFeeTaxRefunded($order->getBasePostnlCodFeeTaxRefunded() + $baseFeeTax);

            return $this;
        }

        $feeTax     = $order->getPostnlCodFeeTax() - $order->getPostnlCodFeeTaxRefunded();
        $baseFeeTax = $order->getBasePostnlCodFeeTax() - $order->getBasePostnlCodFeeTaxRefunded();

        if ($feeTax && $baseFeeTax) {
            $creditmemo->setPostnlCodFeeTax($feeTax)
                       ->setBasePostnlCodFeeTax($baseFeeTax)
                       ->setTaxAmount($creditmemo->getTaxAmount() + $feeTax)
                       ->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseFeeTax)
                       ->setGrandTotal($creditmemo->getGrandTotal() + $feeTax)
                       ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeTax);

            $order->setPostnlCodFeeTaxRefunded($order->getPostnlCodFeeTaxRefunded() + $feeTax)
                  ->setBasePostnlCodFeeTaxRefunded($order->getBasePostnlCodFeeTaxRefunded() + $baseFeeTax);

            return $this;
        }

        return $this;
    }
}