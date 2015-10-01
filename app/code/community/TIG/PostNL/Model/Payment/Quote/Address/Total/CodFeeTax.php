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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Payment_Quote_Address_Total_CodFeeTax
    extends TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee_Abstract
{
    /**
     * The code of this 'total'.
     *
     * @var string
     */
    protected $_totalCode = 'postnl_cod_fee_tax';

    /**
     * Collect the PostNL COD fee tax for the given address.
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
        if ($address->getAddressType() !='shipping') {
            return $this;
        }

        $quote = $address->getQuote();
        $store = $quote->getStore();

        if (!$quote->getId()) {
            return $this;
        }

        /**
         * First, reset the fee amounts to 0 for this address and the quote.
         */
        $address->setPostnlCodFeeTax(0)
                ->setBasePostnlCodFeeTax(0);

        $quote->setPostnlCodFeeTax(0)
              ->setBasePostnlCodFeeTax(0);

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        if ($address->getPostnlCodFee() < 0.0001 || $address->getBasePostnlCodFee() < 0.0001) {
            return $this;
        }

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        /**
         * Get the tax request and corresponding tax rate.
         */
        $taxRequest = $this->_getCodFeeTaxRequest($quote);

        if (!$taxRequest) {
            return $this;
        }

        $taxRate = $this->_getCodFeeTaxRate($taxRequest);

        if (!$taxRate || $taxRate <= 0) {
            return $this;
        }

        /**
         * Calculate the tax for the fee using the tax rate.
         */
        $baseFee = (float) Mage::getStoreConfig(self::XPATH_COD_FEE, $store);
        $fee     = $store->convertPrice($baseFee);

        $feeIsIncltax = $this->getFeeIsInclTax($store);
        $feeTax     = $this->_getCodFeeTax($address, $taxRate, $fee, $feeIsIncltax);
        $baseFeeTax = $this->_getBaseCodFeeTax($address, $taxRate, $baseFee, $feeIsIncltax);

        /**
         * Get all taxes that were applied for this tax request.
         */
        $appliedRates = Mage::getSingleton('tax/calculation')
                            ->getAppliedRates($taxRequest);

        /**
         * Save the newly applied taxes.
         */
        $this->_saveAppliedTaxes(
            $address,
            $appliedRates,
            $feeTax,
            $baseFeeTax,
            $taxRate
        );

        /**
         * Update the total amounts.
         */
        $address->setTaxAmount($address->getTaxAmount() + $feeTax)
                ->setBaseTaxAmount($address->getBaseTaxAmount() + $baseFeeTax)
                ->setPostnlCodFeeTax($feeTax)
                ->setBasePostnlCodFeeTax($baseFeeTax);

        $address->addTotalAmount('postnl_cod_fee_tax', $feeTax);
        $address->addBaseTotalAmount('postnl_cod_fee_tax', $baseFeeTax);

        $quote->setPostnlCodFeeTax($feeTax)
              ->setBasePostnlCodFeeTax($baseFeeTax);

        return $this;
    }

    /**
     * Prevent this total from being fetched by Magento. It's amounts will already have been added to the general tax
     * amounts and the PostNL COD fee total.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }
}