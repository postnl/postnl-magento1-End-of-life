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
 * @method boolean hasPostnlOrder()
 *
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setPostnlShipment(TIG_PostNL_Model_Core_Shipment $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setPostnlOrder(TIG_PostNL_Model_Core_Order $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setIsCod(boolean $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setSubType(string $value)
 *
 * @method boolean getIsCod()
 * @method string  getSubType()
 */
class TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_DeliveryOptions
    extends TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_Create_ShipmentOptions
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_shipment_view_deliveryoptions';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/sales/order/shipment/view/delivery_options.phtml';

    /**
     * Prepares layout of block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $productCodeOnclick = "changeProductCode('{$this->getChangeProductCodeUrl()}')";
        $parcelCountOnclick = "changeParcelCount('{$this->getChangeParcelCountUrl()}')";

        /**
         * @var Mage_Adminhtml_Block_Widget_Button $changeProductCodeButton
         * @var Mage_Adminhtml_Block_Widget_Button $changeParcelCountButton
         */
        $changeProductCodeButton = $this->getLayout()
                                        ->createBlock('adminhtml/widget_button')
                                        ->setData(
                                            array(
                                                'label'   => $this->__('Change'),
                                                'class'   => 'btn-reset',
                                                'onclick' => $productCodeOnclick
                                            )
                                        );
        $changeParcelCountButton = $this->getLayout()
                                        ->createBlock('adminhtml/widget_button')
                                        ->setData(
                                            array(
                                                'label'   => $this->__('Change'),
                                                'class'   => 'btn-reset',
                                                'onclick' => $parcelCountOnclick
                                            )
                                        );

        $this->setChild('change_product_code_button', $changeProductCodeButton);
        $this->setChild('change_parcel_count_button', $changeParcelCountButton);

        return parent::_prepareLayout();
    }

    /**
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function getPostnlShipment()
    {
        if ($this->hasPostnlShipment()) {
            return $this->_getData('postnl_shipment');
        }

        $shipment = $this->getShipment();

        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->loadByShipment($shipment);

        $this->setPostnlShipment($postnlShipment);
        return $postnlShipment;
    }

    /**
     * @return TIG_PostNL_Model_Core_Order|false
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            return $this->_getData('postnl_order');
        }

        $postnlShipment = $this->getPostnlShipment();

        $postnlOrder = $postnlShipment->getPostnlOrder();

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

        $shipment = $this->getPostnlShipment();

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $productOptions = $helper->getProductOptionsForShipment($shipment);

        $this->setProductOptions($productOptions);
        return $productOptions;
    }

    /**
     * Get the shipment type for the current order.
     *
     * @return string
     */
    public function getShipmentType()
    {
        $postnlShipment = $this->getPostnlShipment();
        $shipmentType = $postnlShipment->getShipmentType();

        switch ($shipmentType) {
            case $postnlShipment::SHIPMENT_TYPE_DOMESTIC:
                $shipmentType  = $this->__('Domestic');
                break;
            case $postnlShipment::SHIPMENT_TYPE_DOMESTIC_COD:
                $shipmentType  = $this->__('Domestic');
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_AVOND:
                $shipmentType = $this->__('Domestic');
                $this->setSubType($this->__('Evening Delivery'));
                break;
            case $postnlShipment::SHIPMENT_TYPE_AVOND_COD:
                $shipmentType = $this->__('Domestic');
                $this->setSubType($this->__('Evening Delivery'));
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_PG:
                $shipmentType = $this->__('Post Office');

                if ($postnlShipment->isBelgiumShipment()) {
                    $this->setSubType($this->__('Belgium'));
                }
                break;
            case $postnlShipment::SHIPMENT_TYPE_PG_COD:
                $shipmentType = $this->__('Post Office');
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_PGE:
                $shipmentType = $this->__('Post Office');
                $this->setSubType($this->__('Early Pickup'));
                break;
            case $postnlShipment::SHIPMENT_TYPE_PGE_COD:
                $shipmentType = $this->__('Post Office');
                $this->setSubType($this->__('Early Pickup'));
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_PA:
                $shipmentType = $this->__('Parcel Dispenser');
                break;
            case $postnlShipment::SHIPMENT_TYPE_EPS:
                $shipmentType = $this->__('EPS');
                break;
            case $postnlShipment::SHIPMENT_TYPE_GLOBALPACK:
                $shipmentType = $this->__('GlobalPack');
                break;
            case $postnlShipment::SHIPMENT_TYPE_BUSPAKJE:
                $shipmentType = $this->__('Letter Box Parcel');
                break;
            case $postnlShipment::SHIPMENT_TYPE_SUNDAY:
                $shipmentType = $this->__('Sunday Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_MONDAY:
                $shipmentType = $this->__('Monday Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_SAMEDAY:
                $shipmentType = $this->__('Same Day Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_FOOD:
                $shipmentType = $this->__('Food Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_COOLED:
                $shipmentType = $this->__('Cooled Food Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_AGECHECK:
                $shipmentType = $this->__('Age Check');
                break;
            case $postnlShipment::SHIPMENT_TYPE_BIRTHDAYCHECK:
                $shipmentType = $this->__('Birthday Check');
                break;
            case $postnlShipment::SHIPMENT_TYPE_IDCHECK:
                $shipmentType = $this->__('ID Check');
                break;
        }

        return $shipmentType;
    }

    /**
     * Get the current order's sub type.
     *
     * @return boolean|string
     */
    public function getShipmentSubType()
    {
        $subType = $this->getSubType();

        if (!$subType) {
            return false;
        }

        $isCod = $this->getIsCod();
        if (!$isCod) {
            return $subType;
        }

        $subType .= ' + ' . $this->__('COD');
        return $subType;
    }

    /**
     * @return bool
     */
    public function canChangeProductCode()
    {
        $postnlShipment = $this->getPostnlShipment();

        /**
         * Check if the current user is allowed to perform this action.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->checkIsPostnlActionAllowed(array('change_product_code'))) {
            return false;
        }

        return $postnlShipment->canChangeProductCode();
    }

    /**
     * Retrieve the change_product_code_button html.
     *
     * @return string
     */
    public function getChangeProductCodeButtonHtml()
    {
        return $this->getChildHtml('change_product_code_button');
    }

    /**
     * Get the changeProductUrl for this shipment.
     *
     * @return string
     */
    public function getChangeProductCodeUrl()
    {
        $url = $this->getUrl(
            'adminhtml/postnlAdminhtml_shipment/changeProductCode',
            array(
                'shipment_id' => $this->getShipment()->getId()
            )
        );

        return $url;
    }

    /**
     * Check if the customer chose any additional options during checkout.
     *
     * @return bool
     */
    public function hasExtraOptions()
    {
        $postnlOrder = $this->getPostnlOrder();
        if (!$postnlOrder || !$postnlOrder->getId()) {
            return false;
        }

        $hasOptions = $postnlOrder->hasOptions();
        return $hasOptions;
    }

    /**
     * Get additional options the customer chose during checkout.
     *
     * @return array
     */
    public function getFormattedExtraOptions()
    {
        $postnlOptions = $this->getPostnlOrder();

        $options = $postnlOptions->getOptions();
        if (!$options) {
            return array();
        }

        $formattedOptions = array();
        foreach ($options as $option => $value) {
            if (!$value) {
                continue;
            }

            switch ($option) {
                case 'only_stated_address':
                    $formattedOptions[] = $this->__('deliver to stated address only');
                    break;
                //no default
            }
        }

        return $formattedOptions;
    }

    /**
     * Get whether the PostNL shipment's parcel count may be changed.
     *
     * @return boolean
     */
    public function canChangeParcelCount()
    {
        $postnlShipment = $this->getPostnlShipment();

        /**
         * Check if the current user is allowed to perform this action.
         */
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');
        if (!$helper->checkIsPostnlActionAllowed(array('change_parcel_count'))) {
            return false;
        }

        return $postnlShipment->canChangeParcelCount();
    }

    /**
     * Get the changeParcelCountUrl for this shipment.
     *
     * @return string
     */
    public function getChangeParcelCountUrl()
    {
        $url = $this->getUrl(
            'adminhtml/postnlAdminhtml_shipment/changeParcelCount',
            array(
                'shipment_id' => $this->getShipment()->getId()
            )
        );

        return $url;
    }

    /**
     * Retrieve the change_parcel_count_button html.
     *
     * @return string
     */
    public function getChangeParcelCountButtonHtml()
    {
        return $this->getChildHtml('change_parcel_count_button');
    }

    /**
     * Get delivery time information for this PostNL shipment.
     *
     * @return array|false
     */
    public function getDeliveryTimeInfo()
    {
        $postnlShipment = $this->getPostnlShipment();
        if (!$postnlShipment->hasExpectedDeliveryTimeStart()) {
            return false;
        }

        $info = array(
            'delivery_time_start'       => '',
            'delivery_time_end'         => '',
            'store_delivery_time_start' => '',
            'store_delivery_time_end'   => '',
            'timezone_differ'           => false,
        );

        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getSingleton('core/date');
        $utcTimeZone = new DateTimeZone('UTC');

        $amsterdamStartTime = new DateTime($postnlShipment->getExpectedDeliveryTimeStart(), $utcTimeZone);
        $amsterdamStartTime->setTimezone(new DateTimeZone('Europe/Amsterdam'));
        $info['delivery_time_start'] = $dateModel->date('H:i', $postnlShipment->getExpectedDeliveryTimeStart());
        $info['store_delivery_time_start'] = $amsterdamStartTime->format('H:i');

        if ($info['delivery_time_start'] != $info['store_delivery_time_start']) {
            $info['timezone_differ'] = true;
        }

        if (!$postnlShipment->hasExpectedDeliveryTimeEnd()) {
            return $info;
        }

        $amsterdamEndTime = new DateTime($postnlShipment->getExpectedDeliveryTimeEnd(), $utcTimeZone);
        $amsterdamEndTime->setTimezone(new DateTimeZone('Europe/Amsterdam'));
        $info['delivery_time_end'] = $dateModel->date('H:i', $postnlShipment->getExpectedDeliveryTimeEnd());
        $info['store_delivery_time_end'] = $amsterdamEndTime->format('H:i');

        return $info;
    }
}
