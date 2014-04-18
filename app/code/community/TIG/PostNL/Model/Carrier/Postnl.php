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
 * PostNL shipping method model
 *
 * @method boolean                         hasQuote()
 * @method TIG_PostNL_Model_Carrier_Postnl setQuote(Mage_Sales_Model_Quote $value)
 * @method boolean                         hasPostnlOrder()
 * @method TIG_PostNL_Model_Carrier_Postnl setPostnlOrder(TIG_PostNL_Model_Core_Order $value)
 * @method boolean                         hasHelper()
 * @method TIG_PostNL_Model_Carrier_Postnl setHelper(TIG_PostNL_Helper_Carrier $value)
 */
class TIG_PostNL_Model_Carrier_Postnl extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Rate type (tablerate or flatrate).
     */
    const XML_PATH_RATE_TYPE = 'carriers/postnl/rate_type';

    /**
     * Whether to use Magento's tabelrates or PostNL's.
     */
    const XPATH_RATE_SOURCE = 'carriers/postnl/rate_source';

    /**
     * PostNL carrier code
     *
     * @var string
     */
    protected $_code = 'postnl';

    /**
     * Fixed price flag
     *
     * @var boolean
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected $_default_condition_name = 'package_weight';

    /**
     * @var array
     */
    protected $_conditionNames = array();

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        foreach (array_keys($this->getCode('condition_name')) as $k) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $this->setQuote($quote);

        return $quote;
    }

    /**
     * @return TIG_PostNL_Model_Core_Order
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            return $this->getData('postnl_order');
        }

        $quote = $this->getQuote();
        $postnlOrder = Mage::getModel('postnl_core/order');

        if ($quote->getId()) {
            $postnlOrder->load($quote->getId(), 'quote_id');
        }

        $this->setPostnlOrder($postnlOrder);
        return $postnlOrder;
    }

    /**
     * get PostNL Carrier helper
     *
     * @return TIG_PostNL_Helper_Carrier
     */
    public function getHelper()
    {
        if ($this->hasHelper()) {
            return $this->getData('helper');
        }

        $helper = Mage::helper('postnl/carrier');

        $this->setHelper($helper);
        return $helper;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Collect shipping rate
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @throws TIG_PostNL_Exception
     * @return Mage_Shipping_Model_Rate_Result|void
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /**
         * Several checks to see if shipping to the selected country is allowed based on the supported PostNL shipping products
         */
        $countryId = $request->getDestCountryId();
        $helper = $this->getHelper();
        if ($countryId) {
            $euCountries = Mage::helper('postnl/cif')->getEuCountries();

            if ($countryId == 'NL'
                && !$helper->canUseStandard()
            ) {
                return false;
            }

            if (in_array($countryId, $euCountries)
                && !$helper->canUseEps()
            ) {
                return false;
            }

            if ($countryId != 'NL'
                && !in_array($countryId, $euCountries)
                && !$helper->canUseGlobalPack()
            ) {
                return false;
            }
        }

        $rateType = Mage::getStoreConfig(self::XML_PATH_RATE_TYPE, Mage::app()->getStore()->getId());

        if ($rateType == 'flat') {
            $result = $this->_getFlatRate($request);
        }

        if ($rateType == 'table') {
            $result = $this->_getTableRate($request);
        }

        if (!isset($result)) {
            throw new TIG_PostNL_Exception(
                $helper->__('Invalid rate type requested: %s', $rateType),
                'POSTNL-0036'
            );
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getFlatRate($request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeBoxes += $item->getQty() * $child->getQty();
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');
        if ($this->getConfigData('type') == 'O') { // per order
            $shippingPrice = $this->getConfigData('price');
        } elseif ($this->getConfigData('type') == 'I') { // per item
            $shippingPrice = ($request->getPackageQty() * $this->getConfigData('price'))
                           - ($this->getFreeBoxes() * $this->getConfigData('price'));
        } else {
            $shippingPrice = false;
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('postnl');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('flatrate');
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            }

            $shippingPrice += $this->getPostnlFee();

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getTableRate(Mage_Shipping_Model_Rate_Request $request)
    {
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        $freePackageValue = false;
        if ($request->getAllItems()) {
            $freePackageValue = 0;
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
            }
            $oldValue = $request->getPackageValue();
            $request->setPackageValue($oldValue - $freePackageValue);
        }

        if ($freePackageValue) {
            $request->setPackageValue($request->getPackageValue() - $freePackageValue);
        }

        $conditionName = $this->getConfigData('condition_name');
        $request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);

         // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        $result = Mage::getModel('shipping/rate_result');
        $rate = $this->getRate($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        $method = Mage::getModel('shipping/rate_result_method');
        if (!empty($rate) && $rate['price'] >= 0) {
            if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }

            $shippingPrice += $this->getPostnlFee();

            $price = $shippingPrice;
            $cost = $rate['cost'];
        } elseif (empty($rate) && $request->getFreeShipping() === true) {
            /**
             * was applied promotion rule for whole cart
             * other shipping methods could be switched off at all
             * we must show table rate method with 0$ price, if grand_total more, than min table condition_value
             * free setPackageWeight() has already was taken into account
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rate = $this->getRate($request);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = Mage::getModel('shipping/rate_result_method');
            }

            $price = 0;
            $cost = 0;
        } else {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('tablerate');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);

            return $result;
        }

        $method->setCarrier('postnl');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('tablerate');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($price);
        $method->setCost($cost);

        $result->append($method);

        return $result;
    }

    /**
     * @return float|int
     */
    public function getPostnlFee()
    {
        $fee          = 0;
        $type         = null;
        $includingTax = false;

        $postnlOrder = $this->getPostnlOrder();
        if ($postnlOrder->getId() && $postnlOrder->getIsActive()) {
            $type = $postnlOrder->getType();
        } else {
            return $fee;
        }

        if (Mage::getSingleton('tax/config')->shippingPriceIncludesTax()) {
            $includingTax = true;
        }

        if ($type == 'PGE') {
            $fee = Mage::helper('postnl/deliveryOptions')->getExpressFee(false, $includingTax);
        } else if ($type == 'Avond' ) {
            $fee = Mage::helper('postnl/deliveryOptions')->getEveningFee(false, $includingTax);
        }

        return $fee;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array|bool
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $websiteId = $request->getWebsiteId();
        $website = Mage::getModel('core/website')->load($websiteId);

        $rateSource = $website->getConfig(self::XPATH_RATE_SOURCE);
        if ($rateSource == 'shipping_tablerate') {
            $rate = Mage::getResourceModel('shipping/carrier_tablerate')->getRate($request);
        } else {
            $rate = Mage::getResourceModel('postnl_carrier/tablerate')->getRate($request);
        }

        return $rate;
    }

    /**
     * @param        $type
     * @param string $code
     *
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
                'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
            ),

            'condition_name_short'=>array(
                'package_weight' => Mage::helper('shipping')->__('Weight (and above)'),
                'package_value'  => Mage::helper('shipping')->__('Order Subtotal (and above)'),
                'package_qty'    => Mage::helper('shipping')->__('# of Items (and above)'),
            ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    /**
     * Get array of allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'flatrate' => $this->getConfigData('name') . ' flat',
            'tablerate' => $this->getConfigData('name') . ' table'
        );
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     *
     * @return Mage_Shipping_Model_Tracking_Result_Status
     */
    public function getTrackingInfo($tracking)
    {
        $statusModel = Mage::getModel('shipping/tracking_result_status');
        $track = $this->_getTrackByNumber($tracking);
        $shipment = $track->getShipment();

        $locale = Mage::getStoreConfig('general/locale/code', $shipment->getStoreId());
        $lang = substr($locale, 0, 2);

        $shippingAddress = $shipment->getShippingAddress();

        $statusModel->setCarrier($track->getCarrierCode())
                    ->setCarrierTitle($this->getConfigData('name'))
                    ->setTracking($track->getTrackNumber())
                    ->setPopup(1)
                    ->setUrl($this->getHelper()->getBarcodeUrl($track->getTrackNumber(), $shippingAddress, $lang, false));

        return $statusModel;
    }

    /**
     * Load track object by tracking number
     *
     * @param string $number
     *
     * @return Mage_Sales_Model_Order_Shipment_Track
     */
    protected function _getTrackByNumber($number)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readConn = $coreResource->getConnection('core_read');

        $trackSelect = $readConn->select();
        $trackSelect->from($coreResource->getTableName('sales/shipment_track'), array('entity_id'));
        $trackSelect->where('track_number = ?', $number);

        $trackId = $readConn->fetchOne($trackSelect);

        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);

        return $track;
    }
}
