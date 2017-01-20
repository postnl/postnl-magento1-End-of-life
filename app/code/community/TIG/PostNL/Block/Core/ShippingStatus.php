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
 *
 * @method boolean hasPostnlShipment()
 * @method boolean hasShipment()
 *
 * @method TIG_PostNL_Block_Core_ShippingStatus setPostnlShipment(TIG_PostNL_Model_Core_Shipment $value)
 * @method TIG_PostNL_Block_Core_ShippingStatus setShipment(Mage_Sales_Model_Order_Shipment $value)
 *
 * @method Mage_Sales_Model_Order_Shipment getShipment()
 */
class TIG_PostNL_Block_Core_ShippingStatus extends TIG_PostNL_Block_Core_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_core_shippingstatus';

    /**
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function getPostnlShipment()
    {
        if ($this->hasPostnlShipment()) {
            return $this->_getData('postnl_shipment');
        }

        if (!$this->hasShipment()) {
            return false;
        }

        $shipment = $this->getShipment();
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');

        $this->setPostnlShipment($postnlShipment);
        return $postnlShipment;
    }

    /**
     * Checks if a given shipment has been confirmed with PostNL
     *
     * @param Mage_Sales_Model_Order_Shipment
     *
     * @return boolean
     */
    public function isConfirmed()
    {
        $postnlShipment = $this->getPostnlShipment();
        if ($postnlShipment
            && $postnlShipment->getConfirmStatus() == $postnlShipment::CONFIRM_STATUS_CONFIRMED
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a given shipment has been confirmed with PostNL
     *
     * @param Mage_Sales_Model_Order_Shipment
     *
     * @return string
     */
    public function getConfirmedAt()
    {
        $postnlShipment = $this->getPostnlShipment();

        if (!$postnlShipment) {
            return false;
        }

        /** @var Mage_Core_Helper_Data $helper */
        $helper = Mage::helper('core');
        $confirmedAt = $helper->formatDate($postnlShipment->getConfirmedAt(), 'medium', false);

        return $confirmedAt;
    }

    /**
     * Checks if a given shipment has been confirmed with PostNL
     *
     * @param Mage_Sales_Model_Order_Shipment
     *
     * @return boolean
     */
    public function getTrackingUrl()
    {
        $postnlShipment = $this->getPostnlShipment();

        if (!$postnlShipment) {
            return '';
        }

        $barcodeUrl = $postnlShipment->getBarcodeUrl(true);

        $trackingUrl = "<a href={$barcodeUrl} title='mijnpakket' target='_blank'>"
                     . $this->__('here')
                     . '</a>';

        return $trackingUrl;
    }

    /**
     * @return array|TIG_PostNL_Model_Core_Resource_Shipment_Collection
     */
    public function getPostnlShipments()
    {
        /**
         * @var Mage_Sales_Model_Resource_Order_Shipment_Collection $shipments
         */
        $shipments = Mage::registry('current_order')->getShipmentsCollection();
        if (!$shipments) {
            return array();
        }

        $shipmentsIds = $shipments->getColumnValues('entity_id');
        $postnlShipments = Mage::getResourceModel('postnl_core/shipment_collection')
                               ->addFieldToFilter('shipment_id', array('in' => $shipmentsIds));

        return $postnlShipments;
    }

    /**
     * Check if the PostNL module is enabled. Otherwise return an empty string.
     *
     * @return string | Mage_Core_Block_Template::_toHtml()
     */
    protected function _toHtml()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
