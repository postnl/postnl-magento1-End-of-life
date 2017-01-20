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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_ConfirmDate
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
        /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
        $carrierHelper = Mage::helper('postnl/carrier');
        if (!$carrierHelper->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl/deliveryOptions');
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $value = $row->getData($this->getColumn()->getIndex());

        $value = new DateTime($value, new DateTimeZone('UTC'));

        /**
         * Update the row's value for the decorator later.
         */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $row->setData($this->getColumn()->getIndex(), $value->format('Y-m-d H:i:s'));

        $adminTimeZone = $helper->getStoreTimeZone(Mage_Core_Model_App::ADMIN_STORE_ID, true);
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $now->setTimezone($adminTimeZone);

        $valueCopy = clone $value;
        $valueCopy->setTimezone($adminTimeZone);

        /**
         * Check if today is the same date as the confirm date. N.B. only the date is checked, not the time.
         */
        if ($now->format('Y-m-d') == $valueCopy->format('Y-m-d')) {
            return $helper->__('Today');
        }

        /**
         * Check if the confirm date is tomorrow.
         */
        $tomorrow = clone $now;
        $tomorrow->add(new DateInterval('P1D'));
        if ($tomorrow->format('Y-m-d') == $valueCopy->format('Y-m-d')) {
            return $helper->__('Tomorrow');
        }

        /**
         * Set the time zone of the row to the same time zone as the admin for comparison.
         */
        $value->setTimezone($adminTimeZone);

        /**
         * Check if the confirm date is somewhere in the future.
         */
        if ($now < $value) {
            $diff = $now->diff($valueCopy);

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

            return $helper->__('%s days from now', $diffDays);
        }

        /**
         * Finally, simply render the date
         */
        $format = $this->_getFormat();

        $timeZone = $helper->getStoreTimeZone($row->getData('store_id'), true);
        $value = $value->setTimezone($timeZone)->format('Y-m-d H:i:s');
        try {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($this->getColumn()->getGmtoffset()) {
                $data = Mage::app()->getLocale()
                            ->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                /** @var Mage_Core_Model_Locale $localeModel */
                $localeModel = Mage::getSingleton('core/locale');
                /** @noinspection PhpUndefinedClassInspection */
                $data = $localeModel->date($value, Zend_Date::ISO_8601, null, false)->toString($format);
            }
        } catch (Exception $e) {
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($this->getColumn()->getTimezone()) {
                $data = Mage::app()->getLocale()
                            ->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                /** @var Mage_Core_Model_Locale $localeModel */
                $localeModel = Mage::getSingleton('core/locale');
                $data = $localeModel->date($value, null, null, false)->toString($format);
            }
        }
        return $data;
    }
}
