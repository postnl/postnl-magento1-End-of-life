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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFee
    extends TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFee_Abstract
{
    /**
     * Xpath to the PostNL COD fee including tax setting.
     */
    const XPATH_COD_FEE_INCLUDING_TAX = 'tax/calculation/postnl_cod_fee_including_tax';

    /**
     * Get the COD fee total amount.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        /** @noinspection PhpUndefinedMethodInspection */
        $fee     = $creditmemo->getPostnlCodFee();
        /** @noinspection PhpUndefinedMethodInspection */
        $baseFee = $creditmemo->getBasePostnlCodFee();

        /**
         * If the creditmemo has a fee already, we only need to set the totals. This is the case for existing
         * creditmemos that are being viewed.
         */
        if ($fee && $baseFee) {
            $this->_updateCreditmemoTotals($creditmemo, $order, $fee, $baseFee);

            return $this;
        }

        /**
         * If we are currently in the backend and logged in, we need to check the POST parameters to see if any fee
         * amount is to be refunded.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        if ($helper && $session->isLoggedIn()) {
            /**
             * This is unfortunately the only way to determine the fee amount that needs to be refunded without
             * rewriting a core class. If anybody knows of a better way, please let us know at
             * servicedesk@tig.nl.
             */
            $creditmemoParameters = Mage::app()->getRequest()
                                               ->getParam('creditmemo', array());

            if (isset($creditmemoParameters['postnl_cod_fee'])
                && $creditmemoParameters['postnl_cod_fee'] !== null
                && $creditmemoParameters['postnl_cod_fee'] !== ''
            ) {
                $this->_updateCreditmemoTotalsFromParams($creditmemo, $order, $creditmemoParameters);

                return $this;
            }
        }

        /**
         * If none of the above are true, we are creating a new creditmemo and need to show the fee amounts that may be
         * refunded (if any).
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $fee     = $order->getPostnlCodFee() - $order->getPostnlCodFeeRefunded();
        /** @noinspection PhpUndefinedMethodInspection */
        $baseFee = $order->getBasePostnlCodFee() - $order->getBasePostnlCodFeeRefunded();

        if ($fee && $baseFee) {
            $this->_updateCreditmemoTotals($creditmemo, $order, $fee, $baseFee);

            return $this;
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order            $order
     * @param float                             $fee
     * @param float                             $baseFee
     *
     * @return $this
     */
    protected function _updateCreditmemoTotals(Mage_Sales_Model_Order_Creditmemo $creditmemo,
                                               Mage_Sales_Model_Order $order, $fee, $baseFee)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $creditmemo->setPostnlCodFee($fee)
                   ->setBasePostnlCodFee($baseFee)
                   ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                   ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $order->setPostnlCodFeeRefunded($order->getPostnlCodFeeRefunded() + $fee)
              ->setBasePostnlCodFeeRefunded($order->getBasePostnlCodFeeRefunded() + $baseFee);

        return $this;
    }

    /**
     * Update the creditmemo's totals based on POST params.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order            $order
     * @param array                             $creditmemoParameters
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _updateCreditmemoTotalsFromParams(Mage_Sales_Model_Order_Creditmemo $creditmemo,
                                                         Mage_Sales_Model_Order $order, array $creditmemoParameters)
    {
        /**
         * Get the fee amounts that are to be refunded.
         */
        $baseFee = (float) $creditmemoParameters['postnl_cod_fee'];

        /**
         * If the fee was entered incl. tax calculate the fee without tax.
         */
        if ($this->getFeeIsInclTax($order->getStore())) {
            $baseFee = $this->_getCodFeeExclTax($baseFee, $order);
        }

        /**
         * Get the order's COD fee amounts.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $orderFee             = $order->getPostnlCodFee();
        /** @noinspection PhpUndefinedMethodInspection */
        $orderFeeRefunded     = $order->getPostnlCodFeeRefunded();
        /** @noinspection PhpUndefinedMethodInspection */
        $orderBaseFee         = $order->getBasePostnlCodFee();
        /** @noinspection PhpUndefinedMethodInspection */
        $orderBaseFeeRefunded = $order->getBasePostnlCodFeeRefunded();

        /**
         * If the total amount refunded exceeds the available fee amount, we have a rounding error. Modify the fee
         * amounts accordingly.
         */
        $totalBaseFee = $baseFee - $orderBaseFee - $orderBaseFeeRefunded;
        if ($totalBaseFee < 0.0001 && $totalBaseFee > -0.0001) {
            $baseFee = $orderBaseFee - $orderBaseFeeRefunded;
        }

        $fee = $baseFee * $order->getBaseToOrderRate();

        $totalFee = $fee - $orderFee - $orderFeeRefunded;
        if ($totalFee < 0.0001 && $totalFee > -0.0001) {
            $fee = $orderFee - $orderFeeRefunded;
        }

        if (round($orderBaseFeeRefunded + $baseFee, 4) > $orderBaseFee) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Maximum PostNL COD fee amount available to refunds is %s.',
                    $order->formatPriceTxt(
                        $orderBaseFee - $orderBaseFeeRefunded
                    )
                ),
                'POSTNL-0178'
            );
        }

        /**
         * Update the creditmemo totals with the new amounts.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $creditmemo->setPostnlCodFee($fee)
                   ->setBasePostnlCodFee($baseFee)
                   ->setGrandTotal($creditmemo->getGrandTotal() + $fee)
                   ->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $order->setPostnlCodFeeRefunded($orderFeeRefunded + $fee)
              ->setBasePostnlCodFeeRefunded($orderBaseFeeRefunded + $baseFee);

        return $this;
    }

    /**
     * Gets the configured PostNL COD fee excl. tax for a given quote.
     *
     * @param float                  $fee
     * @param Mage_Sales_Model_Order $order
     *
     * @return float|int
     */
    protected function _getCodFeeExclTax($fee, Mage_Sales_Model_Order $order)
    {

        /**
         * Build a tax request to calculate the fee tax.
         */
        $taxRequest = $this->_getCodFeeTaxRequest($order);

        if (!$taxRequest) {
            return $fee;
        }

        /**
         * Get the tax rate for the request.
         */
        $taxRate = $this->_getCodFeeTaxRate($taxRequest);

        if (!$taxRate || $taxRate <= 0) {
            return $fee;
        }

        /**
         * Remove the tax from the fee.
         */
        $feeTax = $this->_getCodFeeTax($order->getShippingAddress(), $taxRate, $fee, true);
        $fee -= $feeTax;

        return $fee;
    }
}
