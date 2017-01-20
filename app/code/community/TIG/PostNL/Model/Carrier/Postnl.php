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
 * PostNL shipping method model
 *
 * @method boolean                         hasQuote()
 * @method TIG_PostNL_Model_Carrier_Postnl setQuote(Mage_Sales_Model_Quote $value)
 * @method boolean                         hasPostnlOrder()
 * @method TIG_PostNL_Model_Carrier_Postnl setPostnlOrder(TIG_PostNL_Model_Core_Order $value)
 * @method boolean                         hasHelper()
 * @method TIG_PostNL_Model_Carrier_Postnl setHelper(TIG_PostNL_Helper_Carrier $value)
 * @method TIG_PostNL_Model_Carrier_Postnl setFreeBoxes($freeBoxes)
 * @method int                             getFreeBoxes()
 */
class TIG_PostNL_Model_Carrier_Postnl extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Rate type (tablerate or flatrate).
     */
    const XPATH_RATE_TYPE = 'carriers/postnl/rate_type';

    /**
     * Whether to use Magento's tabelrates or PostNL's.
     */
    const XPATH_RATE_SOURCE = 'carriers/postnl/rate_source';

    /**
     * String values for parcel types.
     */
    const PARCEL_TYPE_REGULAR       = 'regular';
    const PARCEL_TYPE_LETTERBOX     = 'letter_box';
    const PARCEL_TYPE_FOOD          = 'food';
    const PARCEL_TYPE_PAKJE_GEMAK   = 'pakje_gemak';
    const PARCEL_TYPE_AGECHECK      = 'agecheck';
    const PARCEL_TYPE_BIRTHDAYCHECK = 'birthdaycheck';
    const PARCEL_TYPE_IDCHECK       = 'idcheck';

    /**
     * String values for different rate types.
     */
    const RATE_TYPE_FLAT   = 'flat';
    const RATE_TYPE_TABLE  = 'table';
    const RATE_TYPE_MATRIX = 'matrix';

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
     * @var
     */
    protected $_requestItems;

    /**
     * @var Mage_Shipping_Model_Rate_Request
     */
    protected $_request;

    /**
     * @var
     */
    protected $_freePackageValue;

    protected $_freeQty = 0;

    protected $_rateType;

    /**
     * @var Mage_Shipping_Model_Rate_Result
     */
    public $result;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        foreach (array_keys($this->getCode('condition_name')) as $k) {
            $this->_conditionNames[] = $k;
        }
        $this->result = Mage::getModel('shipping/rate_result');
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

        /** @var TIG_PostNL_Helper_Carrier $helper */
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

        $this->_request = $request;

        /**
         * Several checks to see if shipping to the selected country is allowed based on the supported PostNL shipping
         * products
         */
        $countryId = $this->_request->getDestCountryId();

        if (!$this->_isShippingAllowed()) {
            return false;
        }

        /**
         * Find the parcel type and set this on the request object.
         */
        $this->_findParcelType();

        /**
         * Get the parceltype
         */
        /** @noinspection PhpUndefinedMethodInspection */
        $parcelType = $this->_request->getParcelType();

        /**
         * If parcel_type is food, there can be no rate shown for non-domestic shipments.
         */
        if ($parcelType == self::PARCEL_TYPE_FOOD && $countryId != 'NL') {
            return $this->_addShippingRateNotFoundError();
        }

        /**
         * Which types of shipments are only allow to the Netherlands?
         */
        $idCheckTypes = array(
            self::PARCEL_TYPE_AGECHECK,
            self::PARCEL_TYPE_BIRTHDAYCHECK,
            self::PARCEL_TYPE_IDCHECK,
        );

        /**
         * If parcel_type is food, there can be no rate shown for non-domestic shipments.
         */
        /** @noinspection PhpUndefinedMethodInspection */
        if (in_array($parcelType, $idCheckTypes) && $countryId != 'NL') {
            return $this->_addShippingRateNotFoundError();
        }

        $this->_collectRate();

        return $this->result;
    }

    /**
     * Collect rate based on configured rate type
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _collectRate()
    {
        $helper = $this->getHelper();

        switch ($this->getRateType()) {
            case self::RATE_TYPE_FLAT:
                $this->_collectFlatRate();
                break;
            case self::RATE_TYPE_TABLE:
            case self::RATE_TYPE_MATRIX:
                $this->_collectDynamicRate();
                break;
            default:
                throw new TIG_PostNL_Exception(
                    $helper->__('Invalid rate type requested: %s', $this->_rateType),
                    'POSTNL-0036'
                );
        }
    }

    /**
     * @return mixed
     */
    public function getRateType()
    {
        if (isset($this->_rateType)) {
            return $this->_rateType;
        }

        $this->_rateType = Mage::getStoreConfig(self::XPATH_RATE_TYPE, Mage::app()->getStore()->getId());
        return $this->_rateType;
    }

    /**
     * Several checks to see if shipping to the selected country is allowed based on the supported PostNL shipping
     * products
     *
     * @return bool
     */
    protected function _isShippingAllowed()
    {
        $countryId = $this->_request->getDestCountryId();
        $helper = $this->getHelper();

        if ($countryId) {
            $domesticCountry = $helper->getDomesticCountry();
            /** @var TIG_PostNL_Helper_Cif $cifHelper */
            $cifHelper = Mage::helper('postnl/cif');
            $euCountries = $cifHelper->getEuCountries();

            if ($countryId == $domesticCountry
                && !$helper->canUseStandard()
            ) {
                return false;
            } elseif (
                $countryId != $domesticCountry
                && in_array($countryId, $euCountries)
                && !$helper->canUseEps()
            ) {
                return false;
            } elseif (
                $countryId != $domesticCountry
                && !in_array($countryId, $euCountries)
                && !$helper->canUseGlobalPack()
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find the parcel type and set this on the request object.
     */
    protected function _findParcelType()
    {
        /**
         * Determine the parcel type.
         */
        if ($this->getRequestItems()) {
            $item  = current($this->getRequestItems());
            $quote = $item->getQuote();

            /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
            $postnlOrder = Mage::getModel('postnl_core/order');
            $postnlOrder = $postnlOrder->loadByQuote($quote);

            if ($this->getHelper()->quoteIsAgeCheck()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_AGECHECK);
            } elseif ($this->getHelper()->quoteIsBirthdayCheck()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_BIRTHDAYCHECK);
            } elseif ($this->getHelper()->quoteIsIDCheck()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_IDCHECK);
            } elseif ($postnlOrder && $postnlOrder->getId() && $postnlOrder->getIsPakjeGemak()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_PAKJE_GEMAK);
            } elseif ($this->getHelper()->quoteIsBuspakje($quote)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_LETTERBOX);
            } elseif ($this->getHelper()->quoteIsFood()) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_FOOD);
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_request->setParcelType(self::PARCEL_TYPE_REGULAR);
            }
        }
    }

    /**
     * Collect shipping rates for the flat rate method.
     */
    protected function _collectFlatRate()
    {
        $freeBoxes = 0;
        if ($this->getRequestItems()) {
            foreach ($this->getRequestItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                /** @noinspection PhpUndefinedMethodInspection */
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    /** @var Mage_Sales_Model_Quote_Item $child */
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

        if ($this->getConfigData('type') == 'O') { // per order
            $shippingPrice = $this->getConfigData('price');
        } elseif ($this->getConfigData('type') == 'I') { // per item
            $shippingPrice = ($this->_request->getPackageQty() * $this->getConfigData('price'))
                           - ($this->getFreeBoxes() * $this->getConfigData('price'));
        } else {
            $shippingPrice = false;
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false) {
            /** @var Mage_Shipping_Model_Rate_Result_Method $method */
            $method = Mage::getModel('shipping/rate_result_method');

            /** @noinspection PhpUndefinedMethodInspection */
            $method->setCarrier('postnl');
            /** @noinspection PhpUndefinedMethodInspection */
            $method->setCarrierTitle($this->getConfigData('title'));

            /** @noinspection PhpUndefinedMethodInspection */
            $method->setMethod('flatrate');
            /** @noinspection PhpUndefinedMethodInspection */
            $method->setMethodTitle($this->getConfigData('name'));

            if ($this->_request->getFreeShipping() === true
                || $this->_request->getPackageQty() == $this->getFreeBoxes())
            {
                $shippingPrice = '0.00';
            }

            $method->setPrice($shippingPrice);
            /** @noinspection PhpUndefinedMethodInspection */
            $method->setCost($shippingPrice);

            $this->result->append($method);
        }
    }

    /**
     * @throws TIG_PostNL_Exception
     */
    protected function _collectDynamicRate()
    {
        $this->_calculatePackageValue();

        $conditionName = $this->getConfigData('condition_name');
        $this->_request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);

        // Package weight and qty free shipping
        $oldWeight = $this->_request->getPackageWeight();
        $oldQty = $this->_request->getPackageQty();

        $this->_request->setPackageWeight($this->_request->getFreeMethodWeight());
        $this->_request->setPackageQty($oldQty - $this->_freeQty);

        $this->result = Mage::getModel('shipping/rate_result');
        $rate = $this->_getDynamicRate();

        $this->_request->setPackageWeight($oldWeight);
        $this->_request->setPackageQty($oldQty);

        /** @var Mage_Shipping_Model_Rate_Result_Method $method */
        $method = Mage::getModel('shipping/rate_result_method');
        if (!empty($rate) && $rate['price'] >= 0) {
            if ($this->_request->getFreeShipping() === true || ($this->_request->getPackageQty() == $this->_freeQty)) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }

            $price = $shippingPrice;
            $cost = $rate['cost'];
        } elseif (empty($rate) && $this->_request->getFreeShipping() === true) {
            /**
             * was applied promotion rule for whole cart
             * other shipping methods could be switched off at all
             * we must show table rate method with 0$ price, if grand_total more, than min table condition_value
             * free setPackageWeight() has already was taken into account
             */
            $this->_request->setPackageValue($this->_freePackageValue);
            $this->_request->setPackageQty($this->_freeQty);
            $rate = $this->getRate();
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = Mage::getModel('shipping/rate_result_method');
            }

            $price = 0;
            $cost = 0;
        } else {
            $this->_addShippingRateNotFoundError();
            return;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $method->setCarrier('postnl');
        /** @noinspection PhpUndefinedMethodInspection */
        $method->setCarrierTitle($this->getConfigData('title'));

        $carrierMethod = '';
        switch ($this->getRateType()) {
            case self::RATE_TYPE_MATRIX:
                $carrierMethod = 'matrixrate';
                break;
            case self::RATE_TYPE_TABLE:
                $carrierMethod = 'tablerate';
                break;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $method->setMethod($carrierMethod);
        /** @noinspection PhpUndefinedMethodInspection */
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($price);
        /** @noinspection PhpUndefinedMethodInspection */
        $method->setCost($cost);

        $this->result->append($method);
    }

    /**
     * @return array|bool
     * @throws TIG_PostNL_Exception
     */
    protected function _getDynamicRate()
    {
        switch ($this->_rateType) {
            case self::RATE_TYPE_TABLE:
                return $this->getRate();
            case self::RATE_TYPE_MATRIX:
                return $this->getMatrixRate();
        }

        throw new TIG_PostNL_Exception(
            $this->getHelper()->__('Invalid rate type requested: %s', $this->_rateType),
            'POSTNL-0036'
        );
    }

    /**
     * Calculate the entire value of the package. Taking virtual items and free package value into consideration.
     */
    protected function _calculatePackageValue()
    {
        // exclude Virtual products price from Package value if pre-configured
        /**
         *
         */
        if (!$this->getConfigFlag('include_virtual_price') && $this->getRequestItems()) {
            $this->_excludeVirtualItems();
        }

        $this->_excludeFreePackageValue();
    }

    /**
     * Virtual items should not be considered when calculating package value.
     */
    protected function _excludeVirtualItems()
    {
        /**
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($this->getRequestItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    /**
                     * @var Mage_Sales_Model_Quote_Item $child
                     */
                    if ($child->getProduct()->isVirtual()) {
                        $this->_request->setPackageValue($this->_request->getPackageValue() - $child->getBaseRowTotal());
                    }
                }
            } elseif ($item->getProduct()->isVirtual()) {
                $this->_request->setPackageValue($this->_request->getPackageValue() - $item->getBaseRowTotal());
            }
        }
    }

    /**
     * If the request items contain free package value, substract this from the previously set package value.
     */
    protected function _excludeFreePackageValue()
    {
        // Free shipping by qty

        $freePackageValue = 0;
        if ($this->getRequestItems()) {

            $oldValue = $this->_request->getPackageValue();
            $this->_request->setPackageValue($oldValue - $freePackageValue);
        }

        if ($freePackageValue) {
            $this->_request->setPackageValue($this->_request->getPackageValue() - $freePackageValue);
        }
    }

    /**
     * @return float|int
     */
    protected function _getFreePackageValue()
    {
        if (isset($this->_freePackageValue)) {
            return $this->_freePackageValue;
        }

        $freeQty = 0;
        $freePackageValue = 0;

        /**
         * @var Mage_Sales_Model_Quote_Item $item
         */
        foreach ($this->getRequestItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                /**
                 * @var Mage_Sales_Model_Quote_Item $child
                 */
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

        $this->_freeQty = $freeQty;
        $this->_freePackageValue = $freePackageValue;

        return $this->_freePackageValue;
    }

    /**
     * @return array|bool
     */
    public function getRate()
    {
        $websiteId = $this->_request->getWebsiteId();
        /** @var Mage_Core_Model_Website $website */
        $website = Mage::getModel('core/website')->load($websiteId);

        $rateSource = $website->getConfig(self::XPATH_RATE_SOURCE);
        if ($rateSource == 'shipping_tablerate') {
            $rate = Mage::getResourceModel('shipping/carrier_tablerate')->getRate($this->_request);
        } else {
            $rate = Mage::getResourceModel('postnl_carrier/tablerate')->getRate($this->_request);
        }

        return $rate;
    }

    /**
     * @return array|bool
     */
    public function getMatrixRate()
    {
        $rate = Mage::getResourceModel('postnl_carrier/matrixrate')->getRate($this->_request);

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
            throw Mage::exception(
                'Mage_Shipping',
                Mage::helper('shipping')->__('Invalid Table Rate code type: %s', $type)
            );
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception(
                'Mage_Shipping',
                Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code)
            );
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
        $helper = Mage::helper('postnl');

        $methods = array(
            'flatrate'   => $this->getConfigData('name') . ' (' . $helper->__('flat rate') . ')',
            'tablerate'  => $this->getConfigData('name') . ' (' . $helper->__('table rate') . ')',
            'matrixrate' => $this->getConfigData('name') . ' (' . $helper->__('matrix rate') . ')',
        );

        return $methods;
    }

    /**
     * Get tracking information.
     *
     * @param string $tracking
     *
     * @return Mage_Shipping_Model_Tracking_Result_Status
     */
    public function getTrackingInfo($tracking)
    {
        /** @var Mage_Shipping_Model_Tracking_Result_Status $statusModel */
        $statusModel = Mage::getModel('shipping/tracking_result_status');
        $track       = $this->_getTrackByNumber($tracking);
        $shipment    = $track->getShipment();

        $shippingAddress = $shipment->getShippingAddress();
        /** @noinspection PhpUndefinedMethodInspection */
        $barcodeUrl = $this->getHelper()->getBarcodeUrl(
            $track->getTrackNumber(),
            $shippingAddress,
            false,
            false
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $statusModel->setCarrier($track->getCarrierCode())
                    ->setCarrierTitle($this->getConfigData('name'))
                    ->setTracking($track->getTrackNumber())
                    ->setPopup(1)
                    ->setUrl($barcodeUrl);

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
        /** @var Mage_Core_Model_Resource $coreResource */
        $coreResource = Mage::getSingleton('core/resource');
        $readConn = $coreResource->getConnection('core_read');

        $trackSelect = $readConn->select();
        $trackSelect->from($coreResource->getTableName('sales/shipment_track'), array('entity_id'));
        $trackSelect->where('track_number = ?', $number);

        $trackId = $readConn->fetchOne($trackSelect);

        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);

        return $track;
    }

    /**
     * Adds shipping rate not found error to the given result, or instantiate empty result with error if necessary.
     *
     * @return false|Mage_Core_Model_Abstract|null
     */
    protected function _addShippingRateNotFoundError()
    {
        /**
         * Initiate empty result model if not already set.
         */
        if (!$this->result) {
            $this->result = Mage::getModel('shipping/rate_result');
        }

        /** @var Mage_Shipping_Model_Rate_Result_Error $error */
        $error = Mage::getModel('shipping/rate_result_error');
        /** @noinspection PhpUndefinedMethodInspection */
        $error->setCarrier('postnl');
        /** @noinspection PhpUndefinedMethodInspection */
        $error->setCarrierTitle($this->getConfigData('title'));
        /** @noinspection PhpUndefinedMethodInspection */
        $error->setErrorMessage($this->getHelper()->__($this->_getShippingRateNotFoundErrorMessage()));
        $this->result->append($error);

        return $this->result;
    }

    /**
     * @return string
     */
    protected function _getShippingRateNotFoundErrorMessage()
    {
        if (!$this->_request) {
            return $this->getConfigData('specificerrmsg');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $parcelType = $this->_request->getParcelType();

        switch ($parcelType) {
            case self::PARCEL_TYPE_FOOD:
                return $this->getConfigData('foodspecificerrmsg');
            case self::PARCEL_TYPE_AGECHECK:
            case self::PARCEL_TYPE_BIRTHDAYCHECK:
            case self::PARCEL_TYPE_IDCHECK:
                return $this->getConfigData('idcheckspecificerrmsg');
            default:
                return $this->getConfigData('specificerrmsg');
        }
    }

    /**
     * @return Mage_Sales_Model_Quote_Item[]
     */
    public function getRequestItems()
    {
        if ($this->_requestItems) {
            return $this->_requestItems;
        }

        $this->_requestItems = $this->_request->getAllItems();

        return $this->_requestItems;
    }

}
