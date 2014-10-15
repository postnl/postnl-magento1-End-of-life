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
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_ShippingStatus extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_shipment_view_shippingstatus';

    /**
     * Available status classes for the status bar html element
     */
    const CLASS_UNCONFIRMED  = '';
    const CLASS_COLLECTION   = 'status-collection';
    const CLASS_DISTRIBUTION = 'status-distribution';
    const CLASS_TRANSIT      = 'status-transit';
    const CLASS_DELIVERED    = 'status-delivered';
    const CLASS_NOT_POSTNL   = 'hidden';

    /**
     * Get the current shipping status for a shipment
      *
     * @param Mage_Sales_Model_Order_Shipment $shipment
      *
      * @return string
      */
    public function getShippingStatus($shipment)
     {
         /**
          * @var TIG_PostnL_Model_Core_Shipment $postnlShipment
          */
         $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');

        /**
         * Check if the postnl shipment exists. Otherwise it was probably not shipped using PostNL.
         * Even if it was, we would not be able to check the status of it anyway.
         */
        if (!$postnlShipment->getId()) {
            return self::CLASS_NOT_POSTNL;
        }

        switch ($postnlShipment->getShippingPhase()) {
            case 1:
                $class = self::CLASS_COLLECTION;
                break;
            case 2:
                $class = self::CLASS_DISTRIBUTION;
                break;
            case 3:
                $class = self::CLASS_TRANSIT;
                break;
            case 4:
                $class = self::CLASS_DELIVERED;
                break;
            default:
                $class = self::CLASS_UNCONFIRMED;
                break;
        }
        return $class;
    }

    /**
     * Checks if a given shipment has been confirmed with PostNL
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     */
    public function isConfirmed($shipment)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
         */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        if ($postnlShipment->getConfirmStatus() == $postnlShipment::CONFIRM_STATUS_CONFIRMED) {
            return true;
        }

        return false;
    }

    /**
     * Check if the PostNL module is enabled. Otherwise return an empty string.
     *
     * @return string | Mage_Core_Block_Template::_toHtml()
     */
    protected function _toHtml()
    {
        if (!Mage::helper('postnl')->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
