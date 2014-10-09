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
class TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee
    extends TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee_Abstract
{
    /**
     * Xpath to Idev's OneStepCheckout's 'display_tax_included' setting.
     */
    const XPATH_ONESTEPCHECKOUT_DISPLAY_TAX_INCLUDED = 'onestepcheckout/general/display_tax_included';

    /**
     * Module name used by OneStepCheckout.
     */
    const ONESTEPCHECKOUT_MODULE_NAME = 'onestepcheckout';

    /**
     * The code of this 'total'.
     *
     * @var string
     */
    protected $_totalCode = 'postnl_cod_fee';

    /**
     * Collect the PostNL COD fee for the given address.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        /**
         * We can only add the fee to the shipping address.
         */
        if ($address->getAddressType() != 'shipping') {
            return $this;
        }

        $quote = $address->getQuote();
        $store = $quote->getStore();

        if (!$quote->getId()) {
            return $this;
        }

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        /**
         * First, reset the fee amounts to 0 for this address and the quote.
         */
        $address->setPostnlCodFee(0)
                ->setBasePostnlCodFee(0);

        $quote->setPostnlCodFee(0)
              ->setBasePostnlCodFee(0);

        /**
         * Check if the order was placed using a PostNL COD payment method.
         */
        $paymentMethod = $quote->getPayment()->getMethod();

        $helper = Mage::helper('postnl/payment');
        $codPaymentMethods = $helper->getCodPaymentMethods();
        if (!in_array($paymentMethod, $codPaymentMethods)) {
            return $this;
        }

        /**
         * Get the fee amount.
         */
        $baseFee = $this->_getCodFee($quote);
        if ($baseFee <= 0) {
            return $this;
        }

        /**
         * Convert the fee to the base fee amount.
         */
        $fee = $store->convertPrice($baseFee);

        /**
         * Set the fee for the address and quote.
         */
        $address->setPostnlCodFee($fee)
                ->setBasePostnlCodFee($baseFee);

        $quote->setPostnlCodFee($fee)
              ->setBasePostnlCodFee($baseFee);

        /**
         * Update the address' grand total amounts.
         */
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseFee);
        $address->setGrandTotal($address->getGrandTotal() + $fee);

        return $this;
    }

    /**
     * Fetch the fee.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getPostnlCodFee();

        if ($amount <= 0) {
            return $this;
        }

        $storeId = $address->getQuote()->getStoreId();

        /**
         * Add the COD fee tax for OSC if the 'display_tax_included' setting is turned on.
         */
        if (Mage::app()->getRequest()->getModuleName() == self::ONESTEPCHECKOUT_MODULE_NAME
            && Mage::getStoreConfigFlag(self::XPATH_ONESTEPCHECKOUT_DISPLAY_TAX_INCLUDED, $storeId)
        ) {
            $amount += $address->getPostnlCodFeeTax();
        }

        $address->addTotal(
            array(
                'code'  => $this->getCode(),
                'title' => Mage::helper('postnl/payment')->getPostnlCodFeeLabel($storeId),
                'value' => $amount,
            )
        );

        return $this;
    }

    /**
     * Gets the configured PostNL COD fee excl. tax for a given quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return float|int
     */
    protected function _getCodFee(Mage_Sales_Model_Quote $quote)
    {
        $storeId = $quote->getStoreId();

        /**
         * Get the fee as configured by the merchant.
         */
        $fee = (float) Mage::getStoreConfig(self::XPATH_COD_FEE, $storeId);
        if ($fee <= 0) {
            return 0;
        }

        /**
         * If the fee is entered without tax, return the fee amount. Otherwise, we need to calculate and remove the tax.
         */
        $feeIsIncludingTax = $this->getFeeIsInclTax($storeId);
        if (!$feeIsIncludingTax) {
            return $fee;
        }

        /**
         * Build a tax request to calculate the fee tax.
         */
        $taxRequest = $this->_getCodFeeTaxRequest($quote);

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
        $feeTax = $this->_getCodFeeTax($quote->getShippingAddress(), $taxRate, $fee, true);
        $fee -= $feeTax;

        return $fee;
    }
}