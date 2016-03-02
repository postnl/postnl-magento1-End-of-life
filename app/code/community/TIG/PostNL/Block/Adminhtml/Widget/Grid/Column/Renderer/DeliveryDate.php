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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_DeliveryDate
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    /**
     * Additional column names used
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const CONFIRM_DATE_COLUMN    = 'confirm_date';
    const COUNTRY_ID_COLUMN      = 'country_id';

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

        /** @var TIG_PostNL_Helper_Carrier $helper */
        $helper = Mage::helper('postnl/carrier');
        if (!$helper->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        $domesticCountry = $helper->getDomesticCountry();
        if ($row->getData(self::COUNTRY_ID_COLUMN) != $domesticCountry) {
            return Mage::helper('postnl')->__('N/A');
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $value = $row->getData($this->getColumn()->getIndex());

        /**
         * If no delivery date is specified, calculate the date as being 1 day after the confirm date
         */
        if (!$value) {
            $confirmDate  = $row->getData(self::CONFIRM_DATE_COLUMN);
            $confirmDate = new DateTime($confirmDate, new DateTimeZone('UTC'));
            $confirmDate->add(new DateInterval('P1D'));

            $deliveryDate = $confirmDate;
        } else {
            $deliveryDate = new DateTime($value, new DateTimeZone('UTC'));
        }

        $timeZone = $helper->getStoreTimeZone($row->getData('store_id'), true);
        $deliveryDate = $deliveryDate->setTimezone($timeZone)->format('Y-m-d H:i:s');

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $row->setData($this->getColumn()->getIndex(), $deliveryDate);

        /**
         * Finally, simply render the date
         */
        return parent::render($row);
    }
}
