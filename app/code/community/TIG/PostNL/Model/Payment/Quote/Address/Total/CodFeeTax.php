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
class TIG_PostNL_Model_Payment_Quote_Address_Total_CodFeeTax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * The code of this 'total'.
     *
     * @var string
     */
    protected $_code = 'postnl_cod_fee_tax';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        /**
         * We can only add the fee to the shipping address.
         */
        if ($address->getAddressType() != "shipping") {
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

        if (!$address->getPostnlCodFee() || !$address->getBasePostnlCodFee()) {
            return $this;
        }

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        $this->_setAddress($address);

        $address->setPostnlCodFeeTax(0)
                ->setBasePostnlCodFeeTax(0);

        $quote->setPostnlCodFeeTax(0)
              ->setBasePostnlCodFeeTax(0);

        $customerTaxClass = $quote->getCustomerTaxClassId();
        /**
         * @todo replace with actual tax class
         */
        $codTaxClass      = 5;
        $billingAddress   = $quote->getBillingAddress();
        $taxCalculation   = Mage::getSingleton('tax/calculation');

        if (!$codTaxClass) {
            return $this;
        }

        $request = $taxCalculation->getRateRequest(
            $address,
            $billingAddress,
            $customerTaxClass,
            $store
        );

        $request->setProductClassId($codTaxClass);

        $rate = $taxCalculation->getRate($request);

        if (!$rate) {
            return $this;
        }

        $feeTax = $taxCalculation->calcTaxAmount(
            $address->getPostnlCodFee(),
            $rate,
            false,
            true
        );

        $baseFeeTax = $taxCalculation->calcTaxAmount(
            $address->getBasePostnlCodFee(),
            $rate,
            false,
            true
        );

        $address->setTaxAmount($address->getTaxAmount() + $feeTax)
                ->setBaseTaxAmount($address->getBaseTaxAmount() + $baseFeeTax)
                ->setGrandTotal($address->getGrandTotal())
                ->setBaseGrandTotal($address->getBaseGrandTotal() + $baseFeeTax)
                ->setPostnlCodFeeTax($feeTax)
                ->setBasePostnlCodFeeTax($baseFeeTax);

        $address->addTotalAmount('nominal_tax', $feeTax);
        $address->addBaseTotalAmount('nominal_tax', $baseFeeTax);

        $quote->setPostnlCodFeeTax($feeTax)
              ->setBasePostnlCodFeeTax($baseFeeTax);

        $appliedRates = $taxCalculation->getAppliedRates($request);
        $this->_saveAppliedTaxes(
            $address,
            $appliedRates,
            $feeTax,
            $baseFeeTax,
            $rate
        );

        return $this;
    }
}