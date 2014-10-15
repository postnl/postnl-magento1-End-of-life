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
class TIG_PostNL_Model_Payment_Order_Invoice_Total_CodFee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        /**
         * The COD fee is always added to the first invoice, so if this order already has invoices, we don't have to add
         * anything.
         */
        if ($order->hasInvoices()) {
            return $this;
        }

        /**
         * Get the COD fee amounts.
         */
        $fee     = $order->getPostnlCodFee();
        $baseFee = $order->getBasePostnlCodFee();

        /**
         * If no COD fee is set, there is nothing to add/
         */
        if ($fee < 0.01 || $baseFee < 0.01) {
            return $this;
        }

        /**
         * Add the COD fee amounts to the invoice and update the amounts for the order.
         */
        $grandTotal = $invoice->getGrandTotal();
        $baseGrandTotal = $invoice->getBaseGrandTotal();

        $invoice->setPostnlCodFee($fee)
                ->setBasePostnlCodFee($baseFee)
                ->setGrandTotal($grandTotal + $fee)
                ->setBaseGrandTotal($baseGrandTotal + $baseFee);

        $order->setPostnlCodFeeInvoiced($fee)
              ->setBasePostnlCodFeeInvoiced($baseFee);

        return $this;
    }
}