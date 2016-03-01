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
 *
 * @method boolean hasShipment()
 * @method boolean hasPostnlOrder()
 * @method boolean hasProductOptions()
 * @method boolean hasBuspakjeProductOptions()
 * @method boolean hasDefaultProductOption()
 * @method boolean hasDefaultBuspakjeOption()
 *
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setShipment(Mage_Sales_Model_Order_Shipment $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setPostnlOrder(TIG_PostNL_Model_Core_Order $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setProductOptions(array $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setDefaultProductOption(string $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setBuspakjeProductOptions(array $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions setDefaultBuspakjeOption(array $value)
 */
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * Xpath to the buspakje calculation mode setting.
     */
    const XPATH_BUSPAKJE_CALC_MODE = 'postnl/delivery_options/buspakje_calculation_mode';

    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_shipment_create_shipmentoptions';

    /**
     * Get current shipment
     *
     * @return Mage_Sales_Model_Order_Shipment.
     */
    public function getShipment()
    {
        if ($this->hasShipment()) {
            return $this->_getData('shipment');
        }

        $shipment = Mage::registry('current_shipment');

        $this->setShipment($shipment);
        return $shipment;
    }

    /**
     * Get current shipment
     *
     * @return TIG_PostNL_Model_Core_Order.
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            return $this->_getData('postnl_order');
        }

        $shipment = $this->getShipment();

        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        $postnlOrder = Mage::getModel('postnl_core/order')->load($shipment->getOrderId(), 'order_id');

        $this->setPostnlOrder($postnlOrder);
        return $postnlOrder;
    }

    /**
     * Get available product options for the current shipment.
     *
     * @return array
     */
    public function getProductOptions()
    {
        if ($this->hasProductOptions()) {
            return $this->_getData('product_options');
        }

        $shipment = $this->getShipment();

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $productOptions = $helper->getProductOptionsForShipment($shipment);

        $this->setProductOptions($productOptions);
        return $productOptions;
    }

    /**
     * Gets all allowed buspakje product options.
     *
     * @return array
     */
    public function getBuspakjeProductOptions()
    {
        if ($this->hasBuspakjeProductOptions()) {
            return $this->getData('buspakje_product_options');
        }

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $productOptions = $helper->getBuspakjeProductCodes(false);

        $this->setBuspakjeProductOptions($productOptions);
        return $productOptions;
    }

    /**
     * Get the default product option for the current shipment.
     *
     * @return string
     */
    public function getDefaultProductOption()
    {
        if ($this->hasDefaultProductOption()) {
            return $this->_getData('default_product_option');
        }

        $shipment = $this->getShipment();

        try {
            /** @var TIG_PostNL_Helper_Cif $helper */
            $helper = Mage::helper('postnl/cif');
            $productOption = $helper->getDefaultProductOptionForShipment($shipment);
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $productOption = '';
        }

        $this->setDefaultProductOption($productOption);
        return $productOption;
    }

    /**
     * Get the default product option for the current shipment.
     *
     * @return string
     */
    public function getDefaultBuspakjeOption()
    {
        if ($this->hasDefaultBuspakjeOption()) {
            return $this->_getData('default_buspakje_option');
        }

        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->setShipmentType('buspakje')
                       ->setStoreId($this->getShipment()->getStoreId());

        try {
            $productOption = $postnlShipment->getDefaultProductCode();
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $productOption = '';
        }

        $this->setDefaultBuspakjeOption($productOption);
        return $productOption;
    }

    /**
     * Gets an array of shipment types for use with GlobalPack shipments.
     *
     * @return array
     */
    public function getShipmentTypes()
    {
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $shipmentTypes = $helper->getShipmentTypes();

        return $shipmentTypes;
    }

    /**
     * Check if the current shipment is belgian.
     *
     * @return boolean
     */
    public function isBelgium()
    {
        $shipment = $this->getShipment();
        if ($shipment->getShippingAddress()->getCountryId() == 'BE') {
            return true;
        }

        return false;
    }

    /**
     * Gets the number of parcels in this shipment based on it's weight.
     *
     * @return int
     */
    public function getParcelCount()
    {
        $shipment = $this->getShipment();

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $parcelCount = (int) $helper->getParcelCount($shipment);
        if ($parcelCount < 1) {
            $parcelCount = 1;
        }

        return $parcelCount;
    }

    /**
     * Check whether the current shipment would fit as a buspakje.
     *
     * @return bool
     */
    public function getFitsAsBuspakje()
    {
        $shipment = $this->getShipment();
        $items = $shipment->getAllItems();

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        $orderItems = array();
        foreach ($items as $item) {
            $orderItems[] = $item->getOrderItem()->setQtyOrdered($item->getQty());
        }

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        $fits = $helper->fitsAsBuspakje($orderItems, true);

        if (!$fits) {
            return $fits;
        }

        $postnlOrder = $this->getPostnlOrder();

        if (!$postnlOrder || !$postnlOrder->getId()) {
            return true;
        }

        $deliveryDate = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $postnlOrder->getDeliveryDate(),
            new DateTimeZone('UTC')
        );
        $deliveryDate->setTimezone(new DateTimeZone('Europe/Amsterdam'));

        if ($deliveryDate->format('N') === '0' || $deliveryDate->format('N') === '1') {
            return false;
        }

        return true;
    }

    /**
     * Gets the configured calculation mode for buspakje shipments.
     *
     * @return mixed
     */
    public function getBuspakjeCalcMode()
    {
        $calcMode = Mage::getStoreConfig(self::XPATH_BUSPAKJE_CALC_MODE, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $calcMode;
    }

    /**
     * Do a few checks to see if the template should be rendered before actually rendering it.
     *
     * @return string
     *
     * @see Mage_Adminhtml_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->isEnabled()) {
            return '';
        }

        $shipment = $this->getShipment();

        /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
        $carrierHelper = Mage::helper('postnl/carrier');
        if (!$carrierHelper->isPostnlShippingMethod($shipment->getOrder()->getShippingMethod())) {
            return '';
        }

        /** @var TIG_PostNL_Helper_Cif $cifHelper */
        $cifHelper = Mage::helper('postnl/cif');
        if ($cifHelper->isGlobalShipment($shipment) && !$helper->isGlobalAllowed()) {
            return '';
        }

        return parent::_toHtml();
    }
}
