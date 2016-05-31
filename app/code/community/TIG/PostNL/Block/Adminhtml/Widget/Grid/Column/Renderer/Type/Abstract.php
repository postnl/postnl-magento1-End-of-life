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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Type_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Additional column names used.
     */
    const SHIPPING_METHOD_COLUMN      = 'shipping_method';
    const PRODUCT_CODE_COLUMN         = 'product_code';
    const IS_PAKJE_GEMAK_COLUMN       = 'is_pakje_gemak';
    const IS_PAKKETAUTOMAAT_COLUMN    = 'is_pakketautomaat';
    const DELIVERY_OPTION_TYPE_COLUMN = 'delivery_option_type';
    const PAYMENT_METHOD_COLUMN       = 'payment_method';
    const OPTIONS_COLUMN              = 'options';
    const DELIVERY_DATE_COLUMN        = 'delivery_date';
    const COUNTRY_ID_COLUMN           = 'country_id';

    /**
     * Renders a type column for a shipment type.
     *
     * @param string        $type
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getShipmentTypeRenderedValue($type, Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label         = '';
        $comment       = false;
        /** @noinspection PhpParamsInspection */
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipmentClass */
        $postnlShipmentClass = Mage::app()->getConfig()->getModelClassName('postnl_core/shipment');
        switch ($type) {
            case $postnlShipmentClass::SHIPMENT_TYPE_DOMESTIC:
                $label = $helper->__('Domestic');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_DOMESTIC_COD:
                $label   = $helper->__('Domestic');
                $comment = $helper->__('COD');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_AVOND:
                $label   = $helper->__('Domestic');
                $comment = $helper->__('Evening Delivery');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_AVOND_COD:
                $label   = $helper->__('Domestic');
                $comment = $helper->__('Evening Delivery') . ' + ' . $helper->__('COD');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_PG:
                $label = $helper->__('Post Office');

                if ($row->getData(self::COUNTRY_ID_COLUMN) == 'BE') {
                    $comment = $helper->__('Belgium');
                    $type .= '_be';
                }
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_PG_COD:
                $label   = $helper->__('Post Office');
                $comment = $helper->__('COD');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_PGE:
                $label   = $helper->__('Post Office');
                $comment = $helper->__('Early Pickup');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_PGE_COD:
                $label   = $helper->__('Post Office');
                $comment = $helper->__('Early Pickup') . ' + ' . $helper->__('COD');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_PA:
                $label = $helper->__('Parcel Dispenser');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_EPS:
                $label = $helper->__('EPS');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_GLOBALPACK:
                $label = $helper->__('GlobalPack');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_BUSPAKJE:
                $label = $helper->__('Letter Box Parcel');

                if ($row->getData(self::PRODUCT_CODE_COLUMN) == '2928') {
                    $comment = $helper->__('Extra');
                }
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_SUNDAY:
                $label = $helper->__('Sunday Delivery');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_MONDAY:
                $label = $helper->__('Monday Delivery');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_SAMEDAY:
                $label = $helper->__('Same Day Delivery');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_FOOD:
                $label = $helper->__('Food Delivery');
                break;
            case $postnlShipmentClass::SHIPMENT_TYPE_COOLED:
                $label = $helper->__('Cooled Food Delivery');
                break;
        }

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";
        if ($comment) {
            $renderedValue .= "<br /><em>{$comment}</em>";
        }

        return $renderedValue;
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
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $column = $this->getColumn();

        $out = '';
        if ($column->hasData('display')) {
            /** @noinspection PhpUndefinedMethodInspection */
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

    /**
     * Get the rendered value for this row.
     *
     * @param string        $value
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getOrderTypeRenderedValue($value, Varien_Object $row)
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');

        /**
         * Try to render the value based on the delivery option type.
         */
        $domesticCountry = $helper->getDomesticCountry();
        $optionType = $row->getData(self::DELIVERY_OPTION_TYPE_COLUMN);
        if ($optionType == 'Avond') {
            return $this->_getAvondRenderedValue($row);
        } elseif ($optionType == 'PGE') {
            return $this->_getPgeRenderedValue($row);
        } elseif ($optionType == 'Sunday') {
            return $this->_getSundayRenderedValue($row);
        } elseif ($optionType == 'Monday') {
            return $this->_getMondayRenderedValue($row, $value);
        } elseif ($optionType == 'Sameday') {
            return $this->_getSameDayRenderedValue($row);
        } elseif ($optionType == 'Food') {
            return $this->_getFoodRenderedValue($row);
        } elseif ($optionType == 'Cooledfood') {
            return $this->_getCooledfoodRenderedValue($row);
        } elseif ($row->getData(self::IS_PAKKETAUTOMAAT_COLUMN)) {
            return $this->_getPaRenderedValue($row);
        } elseif ($row->getData(self::IS_PAKJE_GEMAK_COLUMN)) {
            return $this->_getPgRenderedValue($row, $value);
        }

        /**
         * Check if this order is domestic OR
         * Check if this is a BE to NL shipment with the "use dutch products" option active.
         */
        if (
            $value == $domesticCountry ||
            (
                $value == 'NL' &&
                $domesticCountry == 'BE' &&
                Mage::helper('postnl/deliveryOptions')->canUseDutchProducts()
            )
        ) {
            return $this->_getDomesticRenderedValue($row, $value);
        }

        /**
         * Check if this order's shipping address is in an EU country.
         */
        $euCountries = $helper->getEuCountries();
        if (in_array($value, $euCountries)) {
            return $this->_getEpsRenderedValue($row);
        }

        /**
         * If none of the above apply, it's an international order.
         */
        return $this->_getGlobalpackRenderedValue($row);
    }

    /**
     * Render this column for an Avond shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getAvondRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label   = $helper->__('Domestic');
        $comment = $helper->__('Evening Delivery');
        $type    = 'avond';

        if ($this->_isCod($row)) {
            $comment .= ' + ' . $helper->__('COD');
            $type .= '_cod';
        }

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>" .
            "<br /><em>{$comment}</em>";

        return $renderedValue;
    }

    /**
     * Render this column for a PGE shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getPgeRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label   = $helper->__('Post Office');
        $comment = $helper->__('Early Pickup');
        $type    = 'pge';

        if ($this->_isCod($row)) {
            $type .= '_cod';
            $comment .= ' + ' . $helper->__('COD');
        }

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>" .
            "<br /><em>{$comment}</em>";

        return $renderedValue;
    }

    /**
     * Render this column for a PA shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getPaRenderedValue(Varien_Object $row)
    {
        $label = Mage::helper('postnl')->__('Parcel Dispenser');

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='pakketautomaat'>{$label}" .
            "</b>";

        return $renderedValue;
    }

    /**
     * Render this column for a PGE shipment.
     *
     * @param Varien_Object $row
     * @param string        $value
     *
     * @return string
     */
    protected function _getPgRenderedValue(Varien_Object $row, $value)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Post Office');
        $type  = 'pg';

        $isCod = $this->_isCod($row);

        if ($isCod) {
            $type .= '_cod';
        }

        if ($value == 'BE') {
            $type .= '_be';
        }

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        if ($isCod) {
            $renderedValue .= '<br /><em>' . $helper->__('COD') . '</em>';
        }

        if ($value == 'BE') {
            $renderedValue .= '<br /><em>' . $helper->__('Belgium') . '</em>';
        }

        return $renderedValue;
    }

    /**
     * Render this column for a sunday shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getSundayRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Sunday Delivery');
        $type  = 'sunday';

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Render this column for monday shipments.
     *
     * @param Varien_Object $row
     * @param               $destination
     *
     * @return string
     */
    protected function _getMondayRenderedValue(Varien_Object $row, $destination)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Monday Delivery');
        return $this->_getDomesticRenderedValue($row, $destination, $label);
    }

    /**
     * Render this column for a same day delivery shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getSameDayRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Same Day Delivery');
        $type  = 'sameday';

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Render this column for a food delivery shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getFoodRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Food Delivery');
        $type = 'food';

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Render this column for a cooled food delivery shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getCooledfoodRenderedValue(Varien_Object $row)
    {
        $helper = Mage::helper('postnl');

        $label = $helper->__('Cooled Food Delivery');
        $type = 'cooledfood';

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Render this column for a domestic shipment.
     *
     * @param Varien_Object $row
     * @param string        $destination
     * @param null|string   $label
     *
     * @return string
     */
    protected function _getDomesticRenderedValue(Varien_Object $row, $destination, $label = null)
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::helper('postnl/deliveryOptions');

        if (!$label) {
            $label = $deliveryOptionsHelper->__('Domestic');
        }
        $type  = 'domestic';

        $isCod = $this->_isCod($row);

        if ($isCod) {
            $type .= '_cod';
        } elseif ($destination == 'NL' && $deliveryOptionsHelper->getBuspakjeCalculationMode() == 'automatic') {
            /**
             * If the buspakje calculation mode is set to automatic and the order fits as a buspakje, we should render
             * the column as such.
             */
            $orderItems = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($row->getId());
            if ($deliveryOptionsHelper->fitsAsBuspakje($orderItems)) {
                $deliveryDate = $row->getData(self::DELIVERY_DATE_COLUMN);
                $deliveryDate = DateTime::createFromFormat('Y-m-d H:i:s', $deliveryDate, new DateTimeZone('UTC'));

                if ($deliveryDate && $deliveryDate->format('N') !== '0' && $deliveryDate->format('N') !== '1') {
                    $label = $deliveryOptionsHelper->__('Letter Box Parcel');
                    $type  = 'buspakje';

                    return "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";
                }
            }
        }

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='{$type}'>{$label}</b>";

        if ($isCod) {
            $renderedValue .= '<br /><em>' . $deliveryOptionsHelper->__('COD') . '</em>';
        } elseif ($destination == 'NL') {
            /**
             * If the buspakje calculation mode is set to manual, we can only inform the merchant that this might be a
             * buspakje.
             */
            $orderItems = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($row->getId());
            if ($deliveryOptionsHelper->fitsAsBuspakje($orderItems)) {
                $deliveryDate = $row->getData(self::DELIVERY_DATE_COLUMN);
                $deliveryDate = DateTime::createFromFormat('Y-m-d H:i:s', $deliveryDate, new DateTimeZone('UTC'));

                if ($deliveryDate && $deliveryDate->format('N') !== '0' && $deliveryDate->format('N') !== '1') {
                    $renderedValue .= '<br /><em>(' . $deliveryOptionsHelper->__('possibly letter box parcel') . ')</em>';
                }
            }
        }

        return $renderedValue;
    }

    /**
     * Render this column for an EPS shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getEpsRenderedValue(Varien_Object $row)
    {
        $label = Mage::helper('postnl')->__('EPS');

        $renderedValue = "<b id='postnl-shipmenttype-{$row->getId()}' data-product-type='eps'>{$label}</b>";

        return $renderedValue;
    }

    /**
     * Render this column for a Globalpack shipment.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getGlobalpackRenderedValue(Varien_Object $row)
    {
        $label = Mage::helper('postnl')->__('GlobalPack');

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
    protected function _isCod(Varien_Object $row)
    {
        $isCod = false;
        $paymentMethod = $row->getData(self::PAYMENT_METHOD_COLUMN);

        /** @var TIG_PostNL_Helper_Payment $helper */
        $helper = Mage::helper('postnl/payment');
        $codPaymentMethods = $helper->getCodPaymentMethods();
        if (in_array($paymentMethod, $codPaymentMethods)) {
            $isCod = true;
        }

        return $isCod;
    }

    /**
     * Add additional comments for chosen options. Currently only the 'only_stated_address' option is supported, but
     * this will likely be expanded in future releases.
     *
     * @param string        $html
     * @param Varien_Object $row
     *
     * @return mixed
     */
    protected function _addOptionComments($html, Varien_Object $row)
    {
        $options = $row->getData(self::OPTIONS_COLUMN);
        if (empty($options)) {
            return $html;
        }

        $helper = Mage::helper('postnl');

        $options = unserialize($options);
        foreach ($options as $option => $value) {
            if (!$value) {
                continue;
            }

            switch ($option) {
                case 'only_stated_address':
                    $html .= '<br /><em>(' . $helper->__('deliver to stated address only') . ')</em>';
                    break;
                //no default
            }
        }

        return $html;
    }
}
