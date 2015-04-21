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
 *
 * @method Mage_Sales_Model_Order                                                                  getOrder()
 * @method Mage_Sales_Model_Order|Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo getSource()
 * @method int|string                                                                              getFontSize()
 * @method string                                                                                  getAmountPrefix()
 */
class TIG_PostNL_Model_Payment_Order_Pdf_Total_Tax extends Mage_Tax_Model_Sales_Pdf_Tax
{
    /**
     * Get array of arrays with tax information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getFullTaxInfo()
    {
        $fontSize       = $this->getFontSize() ? $this->getFontSize() : 7;

        if (method_exists($this, '_getCalculatedTaxes')) {
            $taxClassAmount = $this->_getCalculatedTaxes();
        } else {
            $taxClassAmount = Mage::helper('tax')->getCalculatedTaxes($this->getOrder());
        }

        if (method_exists($this, '_getShippingTax')) {
            $shippingTax = $this->_getShippingTax();
        } else {
            $shippingTax = Mage::helper('tax')->getShippingTax($this->getOrder());
        }

        $taxClassAmount = array_merge($taxClassAmount, $shippingTax);

        /**
         * Add the COD fee tax info.
         */
        $taxClassAmount = Mage::helper('postnl/payment')->addPostnlCodFeeTaxInfo(
            $taxClassAmount,
            $this->getSource(),
            $this->getOrder()
        );

        if (!empty($taxClassAmount)) {
            foreach ($taxClassAmount as &$tax) {
                $percent          = $tax['percent'] ? ' (' . $tax['percent']. '%)' : '';
                $tax['amount']    = $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($tax['tax_amount']);
                $tax['label']     = $this->_getTaxHelper()->__($tax['title']) . $percent . ':';
                $tax['font_size'] = $fontSize;
            }
        } else {
            $fullInfo = $this->_getFullRateInfo();
            $tax_info = array();

            if ($fullInfo) {
                foreach ($fullInfo as $info) {
                    if (isset($info['hidden']) && $info['hidden']) {
                        continue;
                    }

                    $_amount = $info['amount'];

                    foreach ($info['rates'] as $rate) {
                        $percent = $rate['percent'] ? ' (' . $rate['percent']. '%)' : '';

                        $tax_info[] = array(
                            'amount'    => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                            'label'     => $this->_getTaxHelper()->__($rate['title']) . $percent . ':',
                            'font_size' => $fontSize
                        );
                    }
                }
            }
            $taxClassAmount = $tax_info;
        }

        return $taxClassAmount;
    }

    /**
     * @return Mage_Tax_Helper_Data
     */
    protected function _getTaxHelper()
    {
        return Mage::helper('tax');
    }
}
