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
class TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee
    extends TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee_Abstract
{
    /**
     * Xpath to the PostNL COD fee including tax setting.
     */
    const XPATH_COD_FEE_INCLUDING_TAX = 'tax/calculation/postnl_cod_fee_including_tax';

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
        $fee = $this->_getCodFee($quote);
        if ($fee <= 0) {
            return $this;
        }

        /**
         * Convert the fee to the base fee amount.
         */
        $baseFee = $store->convertPrice($fee);

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
     * @return $this|array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getPostnlCodFee();

        if ($amount <= 0) {
            return $this;
        }

        $address->addTotal(
            array(
                'code'  => $this->getCode(),
                'title' => Mage::helper('postnl')->__('PostNL COD fee'),
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

        $fee = (float) Mage::getStoreConfig(self::XPATH_COD_FEE, $storeId);
        if ($fee <= 0) {
            return 0;
        }

        $feeIsIncludingTax = Mage::getStoreConfigFlag(self::XPATH_COD_FEE_INCLUDING_TAX, $storeId);
        if (!$feeIsIncludingTax) {
            return $fee;
        }

        $taxRequest = $this->_getCodFeeTaxRequest($quote);

        if (!$taxRequest) {
            return $fee;
        }

        $taxRate = $this->_getCodFeeTaxRate($taxRequest);

        if (!$taxRate || $taxRate <= 0) {
            return $fee;
        }

        $feeTax = $this->_getCodFeeTax($quote->getShippingAddress(), $taxRate, $fee);
        $fee -= $feeTax;

        return $fee;
    }
}