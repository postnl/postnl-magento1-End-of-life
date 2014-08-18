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
 *
 * @method boolean hasPostnlShipment()
 *
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setPostnlShipment(TIG_PostNL_Model_Core_Shipment $value)
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
        $onclick = "changeProductCode('{$this->getChangeProductCodeUrl()}')";

        $block = $this->getLayout()
                      ->createBlock('adminhtml/widget_button')
                      ->setData(
                          array(
                              'label'   => $this->__('Change'),
                              'class'   => 'btn-reset',
                              'onclick' => $onclick
                          )
                      );

        $this->setChild('change_product_code_button', $block);

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

        $postnlShipment = Mage::getModel('postnl_core/shipment')->loadByShipment($shipment);

        $this->setPostnlShipment($postnlShipment);
        return $postnlShipment;
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

        $productOptions = Mage::helper('postnl/cif')->getProductOptionsForShipment($shipment);

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
                $shipmentType = $this->__('Domestic');
                break;
            case $postnlShipment::SHIPMENT_TYPE_DOMESTIC_COD:
                $shipmentType = $this->__('Domestic');
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_AVOND:
                $shipmentType = $this->__('Domestic');
                $this->setSubType('Evening Delivery');
                break;
            case $postnlShipment::SHIPMENT_TYPE_AVOND_COD:
                $shipmentType = $this->__('Domestic');
                $this->setSubType('Evening Delivery');
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_PG:
                $shipmentType = $this->__('Post Office');
                break;
            case $postnlShipment::SHIPMENT_TYPE_PG_COD:
                $shipmentType = $this->__('Post Office');
                $this->setIsCod(true);
                break;
            case $postnlShipment::SHIPMENT_TYPE_PGE:
                $shipmentType = $this->__('Post Office');
                $this->setSubType('Early Pickup');
                break;
            case $postnlShipment::SHIPMENT_TYPE_PGE_COD:
                $shipmentType = $this->__('Post Office');
                $this->setSubType('Early Pickup');
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
        if (!Mage::helper('postnl')->checkIsPostnlActionAllowed(array('change_product_code'))) {
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
            'postnl_admin/adminhtml_shipment/changeProductCode',
            array(
                'shipment_id' => $this->getShipment()->getId()
            )
        );

        return $url;
    }
}