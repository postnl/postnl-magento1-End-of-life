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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_ConfirmDate
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    /**
     * Additional column names used.
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
        $shippingMethod = $row->getData(self::SHIPPING_METHOD_COLUMN);
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        $value = $row->getData($this->getColumn()->getIndex());
        $value = new DateTime($value);
        $now   = new DateTime(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));

        $interval = $now->diff($value);

        /**
         * Check if the shipment should be confirmed somewhere in the future.
         */
        if (
            (($interval->days > 0 || $interval->h > 0) && !$interval->invert)
            || ($interval->days == 0 && $interval->h < 24) && $interval->invert
        ) {
            $confirmDate = clone $value;
            $diff = $now->diff($confirmDate);

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
                return Mage::helper('postnl')->__('Today');
            }

            /**
             * Check if it should be confirmed tomorrow.
             */
            if ($diffDays == 1) {
                $renderedValue = Mage::helper('postnl')->__('Tomorrow');

                return $renderedValue;
            }

            /**
             * Render the number of days before the shipment should be confirmed.
             */
            $renderedValue = Mage::helper('postnl')->__('%s days from now', $diffDays);

            return $renderedValue;
        }

        $timeZone = Mage::helper('postnl')->getStoreTimeZone($row->getData('store_id'), true);
        $value = $value->setTimezone($timeZone)->format('Y-m-d H:i:s');
        $row->setData($this->getColumn()->getIndex(), $value);

        /**
         * Finally, simply render the date.
         */
        return parent::render($row);
    }
}
