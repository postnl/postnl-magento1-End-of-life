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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 *
 * @method boolean hasOrder()
 * @method boolean hasPostnlOrder()
 *
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setOrder(Mage_Sales_Model_Order $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setPostnlOrder(TIG_PostNL_Model_Core_Order $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setIsCod(boolean $value)
 * @method TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions setSubType(string $value)
 *
 * @method boolean getIsCod()
 * @method string  getSubType()
 */
class TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_sales_order_view_deliveryoptions';

    /**
     * @var string
     */
    protected $_template = 'TIG/PostNL/sales/order/view/delivery_options.phtml';

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->_getData('order');
        }

        $order = Mage::registry('current_order');

        $this->setOrder($order);
        return $order;
    }

    /**
     * @return TIG_PostNL_Model_Core_Order
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            return $this->_getData('postnl_order');
        }

        $order = $this->getOrder();

        $postnlOrder = Mage::getModel('postnl_core/order')->loadByOrder($order);

        $this->setPostnlOrder($postnlOrder);
        return $postnlOrder;
    }

    /**
     * Get the shipment type for the current order.
     *
     * @return string
     */
    public function getShipmentType()
    {
        $order       = $this->getOrder();
        $postnlOrder = $this->getPostnlOrder();

        $paymentMethod = $order->getPayment()->getMethod();
        $codPaymentMethods = Mage::helper('postnl/payment')->getCodPaymentMethods();
        if (in_array($paymentMethod, $codPaymentMethods)) {
            $this->setIsCod(true);
        }

        $shipmentType = false;
        switch ($postnlOrder->getType()) {
            case 'PA':
                $shipmentType = $this->__('Parcel Dispenser');
                break;
            case 'PGE':
                $this->setSubType($this->__('Early Pickup'));
                $shipmentType = $this->__('Post Office');
                break;
            case 'PG':
                $shipmentType = $this->__('Post Office');
                break;
            case 'Avond':
                $this->setSubType($this->__('Evening Delivery'));
                $shipmentType = $this->__('Domestic');
                break;
            case 'Overdag':
                $shipmentType = $this->__('Domestic');
                break;
        }

        if ($shipmentType) {
            return $shipmentType;
        }

        $countryId = $order->getShippingAddress()->getCountryId();

        if ($countryId == 'NL') {
            $shipmentType = $this->__('Domestic');

            return $shipmentType;
        }

        $euCountries = Mage::helper('postnl/cif')->getEuCountries();
        if (in_array($countryId, $euCountries)) {
            $shipmentType = $this->__('EPS');

            return $shipmentType;
        }

        $shipmentType = $this->__('GlobalPack');

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
     * Check if the customer chose any additional options during checkout.
     *
     * @return bool
     */
    public function hasExtraOptions()
    {
        $postnlOrder = $this->getPostnlOrder();

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
     * Get delivery time information for this PostNL shipment.
     *
     * @return array|false
     */
    public function getDeliveryTimeInfo()
    {
        $postnlOrder = $this->getPostnlOrder();
        if (!$postnlOrder->hasExpectedDeliveryTimeStart()) {
            return false;
        }

        $info = array(
            'delivery_time_start'       => '',
            'delivery_time_end'         => '',
            'store_delivery_time_start' => '',
            'store_delivery_time_end'   => '',
            'timezone_differ'           => false,
        );

        $dateModel = Mage::getSingleton('core/date');
        $storeTimezone = Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE,
            $postnlOrder->getStoreId()
        );
        $storeTimezone = new DateTimeZone($storeTimezone);
        $utcTimeZone = new DateTimeZone('UTC');

        $storeStartTime = new DateTime($postnlOrder->getExpectedDeliveryTimeStart(), $utcTimeZone);
        $storeStartTime->setTimezone($storeTimezone);
        $info['delivery_time_start'] = $dateModel->date('H:i', $postnlOrder->getExpectedDeliveryTimeStart());
        $info['store_delivery_time_start'] = $storeStartTime->format('H:i');

        if ($info['delivery_time_start'] != $info['store_delivery_time_start']) {
            $info['timezone_differ'] = true;
        }

        if (!$postnlOrder->hasExpectedDeliveryTimeEnd()) {
            return $info;
        }

        $storeEndTime = new DateTime($postnlOrder->getExpectedDeliveryTimeEnd(), $utcTimeZone);
        $storeEndTime->setTimezone($storeTimezone);
        $info['delivery_time_end'] = $dateModel->date('H:i', $postnlOrder->getExpectedDeliveryTimeEnd());
        $info['store_delivery_time_end'] = $storeEndTime->format('H:i');

        return $info;
    }
}