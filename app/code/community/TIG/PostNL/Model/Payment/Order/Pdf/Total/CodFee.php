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
 *
 * @method int|string                getFontSize()
 * @method Mage_Sales_Model_Order    getOrder()
 * @method string                    getAmountPrefix()
 * @method string                    getTitle()
 * @method Mage_Sales_Model_Abstract getSource()
 */
class TIG_PostNL_Model_Payment_Order_Pdf_Total_CodFee extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Display modes for the PostNL COD fee.
     */
    const DISPLAY_MODE_EXCL = 1;
    const DISPLAY_MODE_INCL = 2;
    const DISPLAY_MODE_BOTH = 3;

    /**
     * Xpath to the PostNL COD fee display mode setting.
     */
    const XPATH_DISPLAY_MODE_COD_FEE = 'tax/sales_display/postnl_cod_fee';

    /**
     * Get the PostNL COD fee total amounts to display on the pdf.
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $totals = array();

        $displayMode = $this->getDisplayMode();
        $baseLabel = Mage::helper('postnl/payment')->getPostnlCodFeeLabel($this->getOrder()->getStoreId());

        /**
         * Get the fee excl. tax.
         */
        if ($displayMode === self::DISPLAY_MODE_EXCL || $displayMode === self::DISPLAY_MODE_BOTH) {
            /**
             * Get the amount excl. tax and format it.
             */
            $amount = $this->getAmount();
            $formattedAmount = $this->getOrder()->formatPriceTxt($amount);
            if ($this->getAmountPrefix()) {
                $formattedAmount = $this->getAmountPrefix() . $formattedAmount;
            }

            /**
             * Determine the label.
             */
            $label = $baseLabel;
            if ($displayMode === self::DISPLAY_MODE_BOTH) {
                $label .= ' (' . $this->getTaxLabel(false) . ')';
            }
            $label .= ':';

            /**
             * Add the total amount.
             */
            $totals[] = array(
                'amount'    => $formattedAmount,
                'label'     => $label,
                'font_size' => $fontSize
            );
        }

        /**
         * Get the fee incl. tax.
         */
        if ($displayMode === self::DISPLAY_MODE_INCL || $displayMode === self::DISPLAY_MODE_BOTH) {
            /**
             * Get the amount incl. tax and format it.
             */
            $amount = $this->getAmount() + $this->getSource()->getPostnlCodFeeTax();
            $formattedAmount = $this->getOrder()->formatPriceTxt($amount);
            if ($this->getAmountPrefix()) {
                $formattedAmount = $this->getAmountPrefix() . $formattedAmount;
            }

            /**
             * Determine the label.
             */
            $label = $baseLabel;
            if ($displayMode === self::DISPLAY_MODE_BOTH) {
                $label .= ' (' . $this->getTaxLabel(true) . ')';
            }
            $label .= ':';

            /**
             * Add the total amount.
             */
            $totals[] = array(
                'amount'    => $formattedAmount,
                'label'     => $label,
                'font_size' => $fontSize
            );
        }

        return $totals;
    }

    /**
     * Get the display mode for the PostNL COD fee.
     *
     * @return int
     */
    public function getDisplayMode()
    {
        $displayMode = (int) Mage::getStoreConfig(self::XPATH_DISPLAY_MODE_COD_FEE, $this->getOrder()->getStoreId());

        return $displayMode;
    }

    /**
     * Get the tax label for either incl. or excl. tax.
     *
     * @param boolean $inclTax
     *
     * @return string
     */
    public function getTaxLabel($inclTax = false)
    {
        $taxLabel = Mage::helper('tax')->getIncExcText($inclTax, $this->getOrder()->getStoreId());

        return $taxLabel;
    }
}