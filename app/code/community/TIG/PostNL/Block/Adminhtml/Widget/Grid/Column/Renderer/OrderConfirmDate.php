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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderConfirmDate
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    /**
     * Additional column name used.
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';

    /**
     * Renders column.
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /** @var Mage_Sales_Model_Order $row */
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        $helper = Mage::helper('postnl/deliveryOptions');
        $value  = $row->getData($this->getColumn()->getIndex());

        /**
         * If we have no value, then no delivery date was chosen by the customer. In this case we can calculate when the
         * order could be shipped.
         */
        if (!$value) {
            $shippingDuration = $helper->getOrderShippingDuration($row);
            $deliveryDate = $helper->getDeliveryDate(
                $row->getCreatedAt(),
                $row->getStoreId(),
                false,
                true,
                true,
                $shippingDuration
            );

            $value = $helper->getValidDeliveryDate($deliveryDate)
                            ->sub(new DateInterval('P1D'));
        } else {
            $value = new DateTime($value);
        }

        /**
         * Check if the confirm date is valid.
         */
        $value = $helper->getValidConfirmDate($value);

        /**
         * Update the row's value for the decorator later.
         */
        $row->setData($this->getColumn()->getIndex(), $value->format('Y-m-d H:i:s'));

        $now = new DateTime();
        $now->setTimestamp(Mage::getModel('core/date')->gmtTimestamp());

        /**
         * Check if the shipment should be confirmed somewhere in the future.
         */
        $diff = $now->diff($value);
        if (
            (($diff->days > 0 || $diff->h > 0) && !$diff->invert)
            || ($diff->days == 0 && $diff->h < 24) && $diff->invert
        ) {
            /**
             * Get the number of days until the shipment should be confirmed.
             */
            $diffDays = $diff->format('%a');

            /**
             * If the difference is more than X days exactly, add a day.
             */
            if (($diff->h > 0 || $diff->i > 0 || $diff->s > 0) && !$diff->invert) {
                $diffDays++;
            }

            /**
             * Check if the shipment should be confirmed today.
             */
            if ($diffDays == 0) {
                return $helper->__('Today');
            }

            /**
             * Check if it should be confirmed tomorrow.
             */
            if ($diffDays == 1) {
                $renderedValue = $helper->__('Tomorrow');

                return $renderedValue;
            }

            /**
             * Render the number of days before the shipment should be confirmed.
             */
            $renderedValue = $helper->__('%s days from now', $diffDays);

            return $renderedValue;
        }

        /**
         * Finally, simply render the date
         */
        $format = $this->_getFormat();

        $timeZone = Mage::helper('postnl')->getStoreTimeZone($row->getData('store_id'), true);
        $value = $value->setTimezone($timeZone)->format('Y-m-d H:i:s');
        try {
            if($this->getColumn()->getGmtoffset()) {
                $data = Mage::app()->getLocale()
                            ->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                $data = Mage::getSingleton('core/locale')
                            ->date($value, Zend_Date::ISO_8601, null, false)->toString($format);
            }
        } catch (Exception $e) {
            if($this->getColumn()->getTimezone()) {
                $data = Mage::app()->getLocale()
                            ->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                $data = Mage::getSingleton('core/locale')->date($value, null, null, false)->toString($format);
            }
        }
        return $data;
    }
}
