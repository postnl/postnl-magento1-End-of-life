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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
abstract class TIG_PostNL_Model_Payment_Order_Creditmemo_Total_CodFee_Abstract
    extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    /**
     * Xpath to the PostNL COD fee setting.
     */
    const XPATH_COD_FEE = 'payment/postnl_cod/fee';

    /**
     * Xpath to PostNL COD fee tax class.
     */
    const XPATH_COD_TAX_CLASS = 'tax/classes/postnl_cod_fee';

    /**
     * Xpath to the PostNL COD fee including tax setting.
     */
    const XPATH_COD_FEE_INCLUDING_TAX = 'tax/calculation/postnl_cod_fee_including_tax';

    /**
     * @var Mage_Tax_Model_Calculation
     */
    protected $_calculator;

    /**
     * @return Mage_Tax_Model_Calculation
     */
    public function getTaxCalculation()
    {
        $taxCalculation = $this->_calculator;
        if ($taxCalculation) {
            return $taxCalculation;
        }

        /** @var Mage_Tax_Model_Calculation $taxCalculation */
        $taxCalculation = Mage::getSingleton('tax/calculation');

        $this->setTaxCalculation($taxCalculation);
        return $taxCalculation;
    }

    /**
     * @param Mage_Tax_Model_Calculation $taxCalculation
     *
     * @return $this
     */
    public function setTaxCalculation(Mage_Tax_Model_Calculation $taxCalculation)
    {
        $this->_calculator = $taxCalculation;

        return $this;
    }

    /**
     * Get whether the PostNL COD fee is incl. tax.
     *
     * @param int|Mage_Core_Model_Store|null $store
     *
     * @return bool
     */
    public function getFeeIsInclTax($store = null)
    {
        if (is_null($store)) {
            $storeId = Mage::app()->getStore()->getId();
        } elseif ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        } else {
            $storeId = $store;
        }

        return Mage::getStoreConfigFlag(self::XPATH_COD_FEE_INCLUDING_TAX, $storeId);
    }

    /**
     * Get the tax request object for the current quote.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool|Varien_Object
     */
    protected function _getCodFeeTaxRequest(Mage_Sales_Model_Order $order)
    {
        $store = $order->getStore();
        $codTaxClass      = Mage::getStoreConfig(self::XPATH_COD_TAX_CLASS, $store);

        /**
         * If no tax class is configured for the COD fee, there is no tax to be calculated.
         */
        if (!$codTaxClass) {
            return false;
        }

        $taxCalculation   = $this->getTaxCalculation();
        /** @noinspection PhpUndefinedMethodInspection */
        $customerTaxClass = $order->getCustomerTaxClassId();
        $shippingAddress  = $order->getShippingAddress();
        $billingAddress   = $order->getBillingAddress();

        $request = $taxCalculation->getRateRequest(
            $shippingAddress,
            $billingAddress,
            $customerTaxClass,
            $store
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $request->setProductClassId($codTaxClass);

        return $request;
    }

    /**
     * Get the tax rate based on the previously created tax request.
     *
     * @param Varien_Object $request
     *
     * @return float
     */
    protected function _getCodFeeTaxRate($request)
    {
        $rate = $this->getTaxCalculation()->getRate($request);

        return $rate;
    }

    /**
     * Get the fee tax based on the shipping address and tax rate.
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param float                                $taxRate
     * @param float|null                           $fee
     * @param boolean                              $isInclTax
     *
     * @return float
     */
    protected function _getCodFeeTax($address, $taxRate, $fee = null, $isInclTax = false)
    {
        if (is_null($fee)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $fee = (float) $address->getPostnlCodFee();
        }

        $taxCalculation = $this->getTaxCalculation();

        $feeTax = $taxCalculation->calcTaxAmount(
            $fee,
            $taxRate,
            $isInclTax,
            false
        );

        return $feeTax;
    }

    /**
     * Get the base fee tax based on the shipping address and tax rate.
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param float                                $taxRate
     * @param float|null                           $fee
     * @param boolean                              $isInclTax
     *
     * @return float
     */
    protected function _getBaseCodFeeTax($address, $taxRate, $fee = null, $isInclTax = false)
    {
        if (is_null($fee)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $fee = (float) $address->getBasePostnlCodFee();
        }

        $taxCalculation = $this->getTaxCalculation();

        $baseFeeTax = $taxCalculation->calcTaxAmount(
            $fee,
            $taxRate,
            $isInclTax,
            false
        );

        return $baseFeeTax;
    }
}
