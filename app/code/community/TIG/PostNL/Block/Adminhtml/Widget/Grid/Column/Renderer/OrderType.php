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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderType
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Additional column names used
     */
    const SHIPPING_METHOD_COLUMN      = 'shipping_method';
    const IS_PAKJE_GEMAK_COLUMN       = 'is_pakje_gemak';
    const IS_PAKKETAUTOMAAT_COLUMN    = 'is_pakketautomaat';
    const DELIVERY_OPTION_TYPE_COLUMN = 'delivery_option_type';
    const PAYMENT_METHOD_COLUMN       = 'payment_method';

    /**
     * Renders the column value as a shipment type value (Domestic, EPS or GlobalPack)
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $column
         */
        $column = $this->getColumn();

        /**
         * The shipment was not shipped using PostNL.
         */
        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        if (!in_array($shippingMethod, $postnlShippingMethods)) {
            return '';
        }

        /**
         * Check if any data is available.
         */
        $value = $row->getData($column->getIndex());
        if (is_null($value) || $value === '') {
            return '';
        }

        $isCod = $this->_isCod($row);

        $helper = Mage::helper('postnl/cif');

        $optionType = $row->getData(self::DELIVERY_OPTION_TYPE_COLUMN);
        if ($optionType == 'Avond') {
            $label   = $helper->__('Domestic');
            $comment = $helper->__('Evening Delivery');
            $type    = 'avond';

            if ($isCod) {
                $comment .= ' + ' . $helper->__('COD');
                $type .= '_cod';
            }

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>" .
                "<br /><em>{$comment}</em>";

            return $renderedValue;
        }

        if ($optionType == 'PGE') {
            $label   = $helper->__('Post Office');
            $comment = $helper->__('Early Pickup');
            $type    = 'pge';

            if ($isCod) {
                $type .= '_cod';
            }

            if ($isCod) {
                $comment .= ' + ' . $helper->__('COD');
            }

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>" .
                "<br /><em>{$comment}</em>";

            return $renderedValue;
        }

        if ($row->getData(self::IS_PAKKETAUTOMAAT_COLUMN)) {
            $label = $helper->__('Parcel Dispenser');

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='pakketautomaat'>{$label}</b>";

            return $renderedValue;
        }

        if ($row->getData(self::IS_PAKJE_GEMAK_COLUMN)) {
            $label = $helper->__('Post Office');
            $type  = 'pg';

            if ($isCod) {
                $type .= '_cod';
            }

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

            if ($isCod) {
                $renderedValue .= '<br /><em>' . $helper->__('COD') . '</em>';
            }

            return $renderedValue;
        }

        /**
         * Check if this order is domestic.
         */
        if ($value == 'NL') {
            $label = $helper->__('Domestic');
            $type  = 'domestic';

            if ($isCod) {
                $type .= '_cod';
            }

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

            if ($isCod) {
                $renderedValue .= '<br /><em>' . $helper->__('COD') . '</em>';
            }

            return $renderedValue;
        }

        /**
         * Check if this order's shipping address is in an EU country.
         */
        $euCountries = $helper->getEuCountries();
        if (in_array($value, $euCountries)) {
            $label = $helper->__('EPS');

            $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='eps'>{$label}</b>";

            return $renderedValue;
        }

        /**
         * If none of the above apply, it's an international order.
         */
        $label = $helper->__('GlobalPack');

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='globalpack'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Checks if a specified order is placed using a PostNL COD payment method.
     *
     * @param Varien_Object $row
     *
     * @return bool
     */
    protected function _isCod($row)
    {
        $isCod = false;
        $paymentMethod = $row->getData(self::PAYMENT_METHOD_COLUMN);

        $codPaymentMethods = Mage::helper('postnl/payment')->getCodPaymentMethods();
        if (in_array($paymentMethod, $codPaymentMethods)) {
            $isCod = true;
        }

        return $isCod;
    }

    /**
     * Renders the <col> element of the column. Added check for $this->getColumn()->getDisplay() == 'none' that causes
     * the entire element to be hidden.
     *
     * @return string
     *
     * @see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::renderProperty()
     */
    public function renderProperty()
    {
        /**
         * @var Mage_Adminhtml_Block_Widget_Grid_Column $column
         */
        $column = $this->getColumn();

        $out = '';
        if ($column->hasData('display')) {
            $out .= " style='display:{$column->getDisplay()};'";
        }

        $width = $this->_defaultWidth;

        if ($column->hasData('width')) {
            $customWidth = $column->getData('width');
            if ((null === $customWidth) || (preg_match('/^[0-9]+%?$/', $customWidth))) {
                $width = $customWidth;
            }
            elseif (preg_match('/^([0-9]+)px$/', $customWidth, $matches)) {
                $width = (int)$matches[1];
            }
        }

        if (null !== $width) {
            $out .= ' width="' . $width . '"';
        }

        return $out;
    }
}
