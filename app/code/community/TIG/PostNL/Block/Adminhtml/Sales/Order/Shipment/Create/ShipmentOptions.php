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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_shipment_create_shipmentoptions';

    /**
     * Get current shipment
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        if ($this->getData('shipment')) {
            return $this->getData('shipment');
        }

        $shipment = Mage::registry('current_shipment');

        $this->setShipment($shipment);
        return $shipment;
    }

    /**
     * Get available product options for the current shipment
     *
     * @return array
     */
    public function getProductOptions()
    {
        if ($this->getData('product_options')) {
            return $this->getData('product_options');
        }

        $shipment = $this->getShipment();

        $productOptions = Mage::helper('postnl/cif')->getProductOptionsForShipment($shipment);

        $this->setProductOptions($productOptions);
        return $productOptions;
    }

    /**
     * Get the default product option for the current shipment
     *
     * @return string
     */
    public function getDefaultProductOption()
    {
        if ($this->getData('default_product_option')) {
            return $this->getData('default_product_option');
        }

        $shipment = $this->getShipment();

        $productOption = Mage::helper('postnl/cif')->getDefaultProductOptionForShipment($shipment);

        $this->setDefaultProductOption($productOption);
        return $productOption;
    }

    /**
     * Gets an array of shipment types for use with GlobalPack shipments
     *
     * @return array
     */
    public function getShipmentTypes()
    {
        $shipmentTypes = Mage::helper('postnl/cif')->getShipmentTypes();

        return $shipmentTypes;
    }

    /**
     * Check if the current shipment is belgian
     *
     * @return boolean
     */
    public function isBelgium()
    {
        $shipment = $this->getShipment();
        if ($shipment->getShippingAddress()->getCountry() == 'BE') {
            return true;
        }

        return false;
    }

    /**
     * Gets the number of parcels in this shipment based on it's weight
     *
     * @return int
     */
    public function getParcelCount()
    {
        $shipment = $this->getShipment();

        $parcelCount = Mage::helper('postnl/cif')->getParcelCount($shipment);
        return $parcelCount;
    }

    /**
     * Do a few checks to see if the template should be rendered before actually rendering it
     *
     * @return string | parent::_toHtml()
     *
     * @see Mage_Adminhtml_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {
        $helper = Mage::helper('postnl');
        if (!$helper->isEnabled()) {
            return '';
        }

        $shipment = $this->getShipment();

        $postnlShippingMethods = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
        if (!in_array($shipment->getOrder()->getShippingMethod(), $postnlShippingMethods)) {
            return '';
        }

        if (Mage::helper('postnl/cif')->isGlobalShipment($shipment) && !$helper->isGlobalAllowed()) {
            return '';
        }

        return parent::_toHtml();
    }
}
