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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderType
    extends TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Type_Abstract
{
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
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $column = $this->getColumn();

        /**
         * The shipment was not shipped using PostNL.
         */
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper = Mage::helper('postnl/carrier');
        if (!$helper->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        /**
         * Check if any data is available.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $value = $row->getData($column->getIndex());
        if (is_null($value) || $value === '') {
            return '';
        }

        /**
         * If this row has any corresponding shipments, the shipment_type column will be filled. Use the parent rendered
         * to render this column instead, as it will be more accurate.
         */
        if ($row->getData('shipment_type')) {
            $types        = explode(',', $row->getData('shipment_type'));
            $productCodes = explode(',', $row->getData('product_code'));

            $renderedValues = array();
            foreach ($types as $key => $type) {
                $rowDummy = clone $row;
                $rowDummy->setData('product_code', $productCodes[$key]);

                $renderedValue = $this->getShipmentTypeRenderedValue($type, $rowDummy);
                $renderedValue = $this->_addOptionComments($renderedValue, $row);

                $renderedValues[] = $renderedValue;
            }

            return implode('<br />', $renderedValues);
        }

        $renderedValue = $this->getOrderTypeRenderedValue($value, $row);
        $renderedValue = $this->_addOptionComments($renderedValue, $row);

        return $renderedValue;
    }
}
