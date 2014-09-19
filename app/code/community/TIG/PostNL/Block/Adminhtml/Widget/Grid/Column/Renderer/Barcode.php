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
class TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Barcode
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Additional column names used
     */
    const SHIPPING_METHOD_COLUMN = 'shipping_method';
    const POSTCODE_COLUMN        = 'postcode';
    const COUNTRY_ID_COLUMN      = 'country_id';
    const CONFIRM_STATUS_COLUMN  = 'confirm_status';

    /**
     * Renders the barcode column. This column will be empty for non-PostNL shipments.
     * If the shipment has been confirmed, it will be displayed as a track& trace URL.
     * Otherwise the bare code will be displayed.
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
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
            return '';
        }

        /**
         * If this is a buspakje shipment, a custom barcode is used that will not be displayed here.
         *
         * @var $postnlShipmentClassName TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentClassName = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($row->getData(self::CONFIRM_STATUS_COLUMN) == $postnlShipmentClassName::CONFIRM_STATUS_BUSPAKJE) {
            return '';
        }

        /**
         * Check if any data is available.
         */
        $value = $row->getData($this->getColumn()->getIndex());
        if (!$value) {
            $value = Mage::helper('postnl')->__('No barcode available.');
            return $value;
        }

        /**
         * If the shipment hasn't been confirmed yet, the barcode will not be known by PostNL track & trace.
         */
        if ($row->getData(self::CONFIRM_STATUS_COLUMN) != $postnlShipmentClassName::CONFIRM_STATUS_CONFIRMED) {
            return $value;
        }

        /**
         * Create a track & trace URL based on shipping destination
         */
        $countryCode = $row->getData(self::COUNTRY_ID_COLUMN);
        $postcode = $row->getData(self::POSTCODE_COLUMN);
        $destinationData = array(
            'countryCode' => $countryCode,
            'postcode'    => $postcode,
        );

        $barcodeUrl = Mage::helper('postnl/carrier')->getBarcodeUrl($value, $destinationData, false, true);

        $barcodeHtml = "<a href='{$barcodeUrl}' target='_blank'>{$value}</a>";

        return $barcodeHtml;
    }
}
