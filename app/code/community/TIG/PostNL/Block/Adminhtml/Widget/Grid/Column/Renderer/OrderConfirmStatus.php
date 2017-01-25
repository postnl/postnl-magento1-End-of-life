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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderConfirmStatus
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Additional column names used.
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const CONFIRM_DATE_COLUMN    = 'confirm_date';

    /**
     * Renders the column value as a Yes or No value
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /**
         * The shipment was not shipped using PostNL
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
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $values = $row->getData($this->getColumn()->getIndex());
        if (is_null($values)) {
            return Mage::helper('postnl')->__('No shipments found');
        }

        /**
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        /** @noinspection PhpParamsInspection */
        $postnlShipmentClass = Mage::app()->getConfig()->getModelClassName('postnl_core/shipment');
        $values = explode(',', $values);

        $labels = array();
        foreach ($values as $value) {
            if ($value == $postnlShipmentClass::CONFIRM_STATUS_CONFIRMED) {
                $labels[] = $helper->__('Confirmed');

                continue;
            }

            if ($value == $postnlShipmentClass::CONFIRM_STATUS_UNCONFIRMED) {
                $labels[] = $helper->__('Unconfirmed');

                continue;
            }

            if ($value == $postnlShipmentClass::CONFIRM_STATUS_CONFIRM_EXPIRED) {
                $labels[] = $helper->__('Confirmation Expired');
                continue;
            }

            if ($value == $postnlShipmentClass::CONFIRM_STATUS_BUSPAKJE) {
                $labels[] = $helper->__('No Confirmation Required');
                continue;
            }
        }

        $label = implode(',', $labels);

        return $label;
    }
}
