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
class TIG_PostNL_Model_Core_Packingslip_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    /**
     * Draw item line.
     */
    public function draw()
    {
        /**
         * @var Mage_Sales_Model_Order_Invoice_Item $item
         */
        $order     = $this->getOrder();
        $item      = $this->getItem();
        $orderItem = $item->getOrderItem();
        $pdf       = $this->getPdf();
        $page      = $this->getPage();

        $orderItemQty = $orderItem->getQtyOrdered();
        $itemQty = $item->getQty();

        $lines = array(
            array(
                array(
                    'text' => Mage::helper('core/string')->str_split($item->getName(), 60, true, true),
                    'feed' => 20,
                    'align' => 'left',
                    'font_size' => 8,
                ),
                array(
                    'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
                    'feed'  => 275,
                    'align' => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'  => $order->formatPriceTxt($item->getPrice()),
                    'feed'  => 370,
                    'align' => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'  => $itemQty * 1,
                    'feed'  => 435,
                    'align' => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'  => $order->formatPriceTxt($item->getTaxAmount()),
                    'feed'  => 500,
                    'align' => 'right',
                    'font_size' => 8,
                ),
                array(
                    'text'  => $order->formatPriceTxt($item->getRowTotalInclTax()),
                    'feed'  => 578,
                    'align' => 'right',
                    'font_size' => 8,
                ),
            ),
        );

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 110
                );

                // draw options value
                if ($option['value']) {
                    $_printValue = isset($option['print_value'])
                        ? $option['print_value']
                        : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                            'feed' => 115
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
