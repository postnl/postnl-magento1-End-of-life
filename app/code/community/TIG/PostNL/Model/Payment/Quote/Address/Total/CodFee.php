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
class TIG_PostNL_Model_Payment_Quote_Address_Total_CodFee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * The code of this 'total'.
     *
     * @var string
     */
    protected $_code = 'postnl_cod_fee';

    /**
     * Collect the PostNL COD fee for the given address.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this|Mage_Sales_Model_Quote_Address_Total_Abstract
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

        if ($quote->getPayment()->getMethod() != 'postnl_cod') {
            return $this;
        }

        /**
         * First, reset the fee amounts to 0 for this address.
         */
        $address->setPostnlCodFee(0)
                ->setBasePostnlCodFee(0);

        /**
         * Get the fee amount.
         *
         * @todo get the actual amount
         */
        $fee = 1;
        if ($fee <= 0) {
            return $this;
        }

        /**
         * Convert the fee to the base fee amount.
         */
        $baseFee = $store->convertPrice($fee, false);

        /**
         * Set the fee for the address and quote.
         */
        $address->setPostnlCodFee($fee)
                ->setBasePostnlCodFee($baseFee);

        $quote->setPostnlCodFee($fee)
              ->setBasePostnlCodFee($baseFee);

        /**
         * Update the address' grandtotal amounts.
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
}