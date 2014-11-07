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
 * @copyright Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * @package   TIG
 * @module    PostNL
 * @author    Total Internet Group
 *
 * PostNL Shipment base class. Contains the majority of PostNL shipping functionality
 *
 * Supported events:
 *  - postnl_shipment_generatebarcode_before
 *  - postnl_shipment_generatebarcode_after
 *  - postnl_shipment_generatelabel_before
 *  - postnl_shipment_generatelabel_after
 *  - postnl_shipment_register_confirmation_before
 *  - postnl_shipment_register_confirmation_after
 *  - postnl_shipment_confirm_before
 *  - postnl_shipment_confirm_after
 *  - postnl_shipment_confirmandgeneratelabel_before
 *  - postnl_shipment_confirmandgeneratelabel_after
 *  - postnl_shipment_updateshippingstatus_before
 *  - postnl_shipment_updateshippingstatus_after
 *  - postnl_shipment_updatecompleteshippingstatus_before
 *  - postnl_shipment_updatecompleteshippingstatus_after
 *  - postnl_shipment_updateshippingphase_before
 *  - postnl_shipment_updateshippingphase_after
 *  - postnl_shipment_setshippingphase_*
 *  - postnl_shipment_savelabels_before
 *  - postnl_shipment_savelabels_after
 *  - postnl_shipment_saveadditionaloptions_after
 *  - postnl_shipment_add_track_and_trace_email_vars
 *  - postnl_shipment_send_track_and_trace_email_before
 *  - postnl_shipment_send_track_and_trace_email_after
 *
 * @method bool                           getIsDutchShipment()
 * @method bool                           getIsEuShipment()
 * @method bool                           getIsGlobalShipment()
 * @method int                            getParcelCount()
 * @method string|null                    getConfirmedStatus
 * @method string                         getStatusHistoryUpdatedAt()
 * @method string                         getConfirmStatus()
 * @method string                         getCreatedAt()
 * @method int                            getIsPakjeGemak()
 * @method string                         getConfirmDate()
 * @method string                         getUpdatedAt()
 * @method string                         getConfirmedAt()
 * @method int                            getEntityId()
 * @method int                            getIsParcelwareExported()
 * @method int                            getTrackAndTraceEmailSent()
 * @method int                            getShippingPhase()
 * @method int                            getTreatAsAbandoned()
 * @method int|null                       getShipmentId
 * @method int                            getLabelsPrinted()
 * @method bool|int                       getIsPakketautomaat()
 * @method bool                           getIsBuspakjeShipment()
 *
 * @method TIG_PostNL_Model_Core_Shipment setLabelsPrinted(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setTreatAsAbandoned(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setTrackAndTraceEmailSent(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setIsParcelwareExported(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setEntityId(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setConfirmedAt(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setUpdatedAt(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setProductCode(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setIsPakjeGemak(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setCreatedAt(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setShipmentId(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setMainBarcode(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setConfirmStatus(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setStatusHistoryUpdatedAt(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setShipment(Mage_Sales_Model_Order_Shipment $value)
 * @method TIG_PostNL_Model_Core_Shipment setShippingAddress(Mage_Sales_Model_Order_Address $value)
 * @method TIG_PostNL_Model_Core_Shipment setPakjeGemakAddress(Mage_Sales_Model_Order_Address $value)
 * @method TIG_PostNL_Model_Core_Shipment setStoreId(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setOrderId(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setShipmentBaseGrandTotal(float $value)
 * @method TIG_PostNL_Model_Core_Shipment setBarcodeUrl(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setPostnlOrder(mixed $value)
 * @method TIG_PostNL_Model_Core_Shipment setLabelCollection(TIG_PostNL_Model_Core_Resource_Shipment_LabeL_Collection $value)
 * @method TIG_PostNL_Model_Core_Shipment setDeliveryDate(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setIsPakketautomaat(bool $value)
 * @method TIG_PostNL_Model_Core_Shipment setShipmentType(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setOrder(Mage_Sales_Model_Order $value)
 * @method TIG_PostNL_Model_Core_Shipment setIsBuspakje(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setShipmentIncrementId(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setIsBuspakjeShipment(bool $value)
 * @method TIG_PostNL_Model_Core_Shipment setDefaultProductCode(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setLabels(array $value)
 * @method TIG_PostNL_Model_Core_Shipment setProductOption(string $value)
 * @method TIG_PostNL_Model_Core_Shipment setPayment(Mage_Sales_Model_Order_Payment $value)
 *
 * @method bool                           hasBarcodeUrl()
 * @method bool                           hasPostnlOrder()
 * @method bool                           hasShipment()
 * @method bool                           hasShipmentBaseGrandTotal()
 * @method bool                           hasGlobalpackShipmentType()
 * @method bool                           hasProductCode()
 * @method bool                           hasShippingAddress()
 * @method bool                           hasPakjeGemakAddress()
 * @method bool                           hasStoreId()
 * @method bool                           hasOrderId()
 * @method bool                           hasExtraCoverAmount()
 * @method bool                           hasLabelCollection()
 * @method bool                           hasIsPakketautomaat()
 * @method bool                           hasDeliveryDate()
 * @method bool                           hasShipmentType()
 * @method bool                           hasOrder()
 * @method bool                           hasMainBarcode()
 * @method bool                           hasShipmentIncrementId()
 * @method bool                           hasIsBuspakjeShipment()
 * @method bool                           hasDefaultProductCode()
 * @method bool                           hasProductOption()
 * @method bool                           hasPayment()
 */
class TIG_PostNL_Model_Core_Shipment extends Mage_Core_Model_Abstract
{
    /**
     * Carrier code used by postnl.
     */
    const POSTNL_CARRIER_CODE = 'postnl';

    /**
     * Possible confirm statuses.
     */
    const CONFIRM_STATUS_CONFIRMED       = 'confirmed';
    const CONFIRM_STATUS_UNCONFIRMED     = 'unconfirmed';
    const CONFIRM_STATUS_CONFIRM_EXPIRED = 'confirm_expired';
    const CONFIRM_STATUS_BUSPAKJE        = 'buspakje';

    /**
     * Possible shipping phases.
     */
    const SHIPPING_PHASE_COLLECTION     = '1';
    const SHIPPING_PHASE_SORTING        = '2';
    const SHIPPING_PHASE_DISTRIBUTION   = '3';
    const SHIPPING_PHASE_DELIVERED      = '4';
    const SHIPPING_PHASE_NOT_APPLICABLE = '99';

    /**
     * Possible shipment types.
     */
    const SHIPMENT_TYPE_DOMESTIC     = 'domestic';
    const SHIPMENT_TYPE_DOMESTIC_COD = 'domestic_cod';
    const SHIPMENT_TYPE_AVOND        = 'avond';
    const SHIPMENT_TYPE_AVOND_COD    = 'avond_cod';
    const SHIPMENT_TYPE_PG           = 'pg';
    const SHIPMENT_TYPE_PG_COD       = 'pg_cod';
    const SHIPMENT_TYPE_PGE          = 'pge';
    const SHIPMENT_TYPE_PGE_COD      = 'pge_cod';
    const SHIPMENT_TYPE_PA           = 'pa';
    const SHIPMENT_TYPE_EPS          = 'eps';
    const SHIPMENT_TYPE_GLOBALPACK   = 'globalpack';
    const SHIPMENT_TYPE_BUSPAKJE     = 'buspakje';

    /**
     * Xpaths to default product options settings.
     */
    const XPATH_DEFAULT_STANDARD_PRODUCT_OPTION       = 'postnl/grid/default_product_option';
    const XPATH_DEFAULT_STANDARD_COD_PRODUCT_OPTION   = 'postnl/cod/default_cod_product_option';
    const XPATH_DEFAULT_EVENING_PRODUCT_OPTION        = 'postnl/grid/default_evening_product_option';
    const XPATH_DEFAULT_EVENING_COD_PRODUCT_OPTION    = 'postnl/cod/default_evening_cod_product_option';
    const XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION     = 'postnl/grid/default_pakjegemak_product_option';
    const XPATH_DEFAULT_PAKJEGEMAK_COD_PRODUCT_OPTION = 'postnl/cod/default_pakjegemak_cod_product_option';
    const XPATH_DEFAULT_PGE_PRODUCT_OPTION            = 'postnl/grid/default_pge_product_option';
    const XPATH_DEFAULT_PGE_COD_PRODUCT_OPTION        = 'postnl/cod/default_pge_cod_product_option';
    const XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION = 'postnl/delivery_options/default_pakketautomaat_product_option';
    const XPATH_DEFAULT_EU_PRODUCT_OPTION             = 'postnl/grid/default_eu_product_option';
    const XPATH_DEFAULT_EU_BE_PRODUCT_OPTION          = 'postnl/grid/default_eu_be_product_option';
    const XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION         = 'postnl/cif_globalpack_settings/default_global_product_option';
    const XPATH_DEFAULT_BUSPAKJE_PRODUCT_OPTION       = 'postnl/grid/default_buspakje_product_option';
    const XPATH_USE_ALTERNATIVE_DEFAULT               = 'postnl/grid/use_alternative_default';
    const XPATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT        = 'postnl/grid/alternative_default_max_amount';
    const XPATH_ALTERNATIVE_DEFAULT_OPTION            = 'postnl/grid/alternative_default_option';
    const XPATH_DEFAULT_STATED_ADDRESS_ONLY_OPTION    = 'postnl/grid/default_stated_address_only_product_option';

    /**
     * Xpath to weight per parcel config setting.
     */
    const XPATH_WEIGHT_PER_PARCEL = 'postnl/packing_slip/weight_per_parcel';

    /**
     * Xpath to setting that determines whether or not to send track and trace emails.
     */
    const XPATH_SEND_TRACK_AND_TRACE_EMAIL = 'postnl/track_and_trace/send_track_and_trace_email';

    /**
     * Xpath to track and trace email settings.
     */
    const XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE = 'postnl/track_and_trace/track_and_trace_email_template';
    const XPATH_EMAIL_COPY                     = 'postnl/track_and_trace/send_copy';
    const XPATH_EMAIL_COPY_TO                  = 'postnl/track_and_trace/copy_to';
    const XPATH_EMAIL_COPY_METHOD              = 'postnl/track_and_trace/copy_method';

    /**
     * Xpath to maximum allowed parcel count settings.
     */
    const XPATH_MAX_PARCEL_COUNT = 'postnl/advanced/max_parcel_count';

    /**
     * Xpath to default GlobalPack shipment type.
     */
    const XPATH_DEFAULT_SHIPMENT_TYPE = 'postnl/cif_globalpack_settings/default_shipment_type';

    /**
     * CIF warning code returned when an EPS combi label is not available.
     */
    const EPS_COMBI_LABEL_WARNING_CODE = 'LIRS_0';

    /**
     * Newly added 'pakje_gemak' address type.
     */
    const ADDRESS_TYPE_PAKJEGEMAK = 'pakje_gemak';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_shipment';

    /**
     * Array of product codes that have extra cover.
     *
     * @var array
     */
    protected $_extraCoverProductCodes = array(
        '3087',
        '3094',
        '3091',
        '3097',
        '3536',
        '3546',
        '3534',
        '3544',
        '4945',
    );

    /**
     * Array of labels that need to be saved all at once.
     *
     * @var array
     */
    protected $_labelsToSave = array();

    /**
     * Contains an instance of TIG_PostNL_Model_Core_Shipment_Process which locks a shipment and prevents it from being
     * modified.
     *
     * @var TIG_PostNL_Model_Core_Shipment_Process
     */
    protected $_process;

    /**
     * For certain product codes a custom barcode is required.
     *
     * @var array
     */
    protected $_customBarcodes;

    /**
     * Flag to prevent this shipment from being saved.
     *
     * @var bool
     */
    protected $_preventSaving = false;

    /**
     * Initialize the shipment
     */
    public function _construct()
    {
        $this->_init('postnl_core/shipment');
    }

    /*******************************************************************************************************************
     * GETTER METHODS
     ******************************************************************************************************************/

    /**
     * Retrieves a Mage_Sales_Model_Order_Shipment entity linked to the PostNL shipment.
     *
     * @param boolean $throwException
     *
     * @return Mage_Sales_Model_Order_Shipment|null
     *
     * @throws TIG_PostNL_Exception
     */
    public function getShipment($throwException = true)
    {
        if ($this->hasShipment()) {
            return $this->_getData('shipment');
        }

        $shipmentId = $this->getShipmentId();
        if (!$shipmentId && $throwException) {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('No shipment found for PostNL shipment #%d.', $this->getId()),
                'POSTNL-0176'
            );
        } elseif (!$shipmentId) {
            return null;
        }

        /**
         * @var Mage_Sales_Model_Order_Shipment $shipment
         */
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

        $this->setShipment($shipment);
        return $shipment;
    }

    /**
     * Retrieves a Mage_Sales_Model_Order entity linked to this PostNL shipment.
     *
     * @param boolean $throwException
     *
     * @return Mage_Sales_Model_Order|null
     *
     * @throws TIG_PostNL_Exception
     */
    public function getOrder($throwException = true)
    {
        if ($this->hasOrder()) {
            return $this->_getData('order');
        }

        /**
         * Get the order ID belonging to this PostNL shipment. If no order ID is found, attempt to get it from the
         * Magento shipment.
         */
        $orderId = $this->getOrderId();
        if (!$orderId) {
            $orderId = $this->getShipment()->getOrderId();

            $this->setOrderId($orderId);
        }

        if (!$orderId && $throwException) {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('No order found for PostNL shipment #%d.', $this->getId()),
                'POSTNL-0177'
            );
        } elseif (!$orderId) {
            return null;
        }

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order')->load($orderId);

        $this->setOrder($order);
        return $order;
    }

    /**
     * Retrieve the payment model associated with this shipment's order.
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function getPayment()
    {
        if ($this->hasPayment()) {
            return $this->_getData('payment');
        }
        $orderId = $this->getOrderId();

        $payment = Mage::getModel('sales/order_payment')
                       ->load($orderId, 'parent_id');

        $this->setPayment($payment);
        return $payment;
    }

    /**
     * Retrieves the linked Shipment's shipping address
     *
     * @return Mage_Sales_Model_Order_Address|null
     */
    public function getShippingAddress()
    {
        if ($this->hasShippingAddress()) {
            return $this->_getData('shipping_address');
        }

        if (!$this->getShipment(false)) {
            return null;
        }

        $shippingAddress = $this->getShipment()->getShippingAddress();

        $this->setShippingAddress($shippingAddress);
        return $shippingAddress;
    }

    /**
     * Gets an optional address with the pakje_gemak address type
     *
     * @return boolean|Mage_Sales_Model_Order_Address
     */
    public function getPakjeGemakAddress()
    {
        if ($this->hasPakjeGemakAddress()) {
            return $this->_getData('pakje_gemak_address');
        }

        $order = $this->getOrder(false);
        if (!$order) {
            return null;
        }

        /**
         * @var Mage_Sales_Model_Order_Address $address
         */
        $addresses = $order->getAddressesCollection();
        foreach ($addresses as $address) {
            if ($address->getAddressType() == self::ADDRESS_TYPE_PAKJEGEMAK) {
                $this->setPakjeGemakAddress($address);
                return $address;
            }
        }

        return false;
    }

    /**
     * Get the set store ID. If no store ID is set and a shipment is available,
     * that shipment's store ID will be returned. Otherwise the current store
     * ID is returned.
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasStoreId()) {
            return $this->_getData('store_id');
        }

        if ($this->getShipment(false)) {
            $storeId = $this->getShipment()->getStoreId();

            $this->setStoreId($storeId);
            return $storeId;
        }

        $storeId = Mage::app()->getStore()->getId();

        $this->setStoreId($storeId);
        return $storeId;
    }

    /**
     * Gets the order ID from the associated Mage_Sales_Model_Order_Shipment object
     *
     * @return int|null
     */
    public function getOrderId()
    {
        if ($this->hasOrderId() && $this->_getData('order_id')) {
            return $this->_getData('order_id');
        }

        $shipment = $this->getShipment(false);
        if (!$shipment || !$shipment->getOrderId()) {
            return null;
        }

        $orderId = $shipment->getOrderId();

        $this->setOrderId($orderId);
        return $orderId;
    }

    /**
     * Gets the increment ID of this shipment's Magento shipment if available.
     *
     * @return null|string
     */
    public function getShipmentIncrementId()
    {
        if ($this->hasShipmentIncrementId()) {
            return $this->_getData('shipment_increment_id');
        }

        $shipment = $this->getShipment(false);
        if (!$shipment || !$shipment->getIncrementId()) {
            return null;
        }

        $incrementId = $shipment->getIncrementId();

        $this->setShipmentIncrementId($incrementId);
        return $incrementId;
    }

    /**
     * Alias for TIG_PostNL_Model_Core_Shipment::getShipmentIncrementId().
     *
     * This method is not used internally. It is only provided for other developers who are used to calling
     * getIncrementId() directly.
     *
     * @return null|string
     */
    public function getIncrementId()
    {
        return $this->getShipmentIncrementId();
    }

    /**
     * Gets a PostNL helper object
     *
     * @param string $type
     *
     * @return TIG_PosTNL_Helper_Data|TIG_PosTNL_Helper_Cif|TIG_PosTNL_Helper_Carrier|TIG_PosTNL_Helper_DeliveryOptions
     *         |TIG_PosTNL_Helper_AddressValidation|TIG_PosTNL_Helper_Checkout|TIG_PosTNL_Helper_Mijnpakket
     *         |TIG_PosTNL_Helper_Parcelware|TIG_PosTNL_Helper_Payment|TIG_PosTNL_Helper_Webservices
     */
    public function getHelper($type = 'data')
    {
        if ($this->hasData('helper_' . $type)) {
            return $this->_getData('helper_' . $type);
        }

        $helper = Mage::helper('postnl/' . $type);

        $this->setDataUsingMethod('helper_' . $type, $helper);
        return $helper;
    }

    /**
     * Gets the process used for locking and unlocking this shipment
     *
     * @return TIG_PostNL_Model_Core_Shipment_Process
     */
    public function getProcess()
    {
        $process = $this->_process;
        if (is_null($process)) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment_Process $process
             */
            $process = Mage::getModel('postnl_core/shipment_process')
                           ->setId($this->getId());

            $this->setProcess($process);
        }

        return $process;
    }

    /**
     * Get an array of labels that have to be saved together
     *
     * @return array
     */
    public function getLabelsToSave()
    {
        return $this->_labelsToSave;
    }

    /**
     * Get all product codes that have extra cover
     *
     * @return array
     */
    public function getExtraCoverProductCodes()
    {
        return $this->_extraCoverProductCodes;
    }

    /**
     * get an array of all product codes that need a custom barcode.
     *
     * @return array
     */
    public function getCustomBarcodes()
    {
        if ($this->_customBarcodes) {
            return $this->_customBarcodes;
        }

        $customBarcodes = $this->getHelper()->getCustomBarcodes();

        $this->_customBarcodes = $customBarcodes;
        return $customBarcodes;
    }

    /**
     * Get this shipment's product code. If no code is available, generate the code.
     *
     * @return int
     */
    public function getProductCode()
    {
        if ($this->hasProductCode()) {
            return $this->_getData('product_code');
        }

        $productCode = $this->_getProductCode();

        $this->setProductCode($productCode);
        return $productCode;
    }

    /**
     * Get the product option linked to this shipment's product code.
     *
     * @return string|null
     */
    public function getProductOption()
    {
        if ($this->hasProductOption()) {
            return $this->_getData('product_option');
        }

        /**
         * Get this shipment's product code.
         */
        $productCode = $this->getProductCode();
        if (!$productCode) {
            return null;
        }

        /**
         * Get all product options.
         */
        $productOptions = Mage::getModel('postnl_core/system_config_source_allProductOptions')
                              ->getOptions(array(), true);

        if (isset($productOptions[$productCode])) {
            $productOption = $productOptions[$productCode];
        } else {
            $productOption = $productCode;
        }

        $this->setProductOption($productOption);
        return $productOption;
    }

    /**
     * Get the current shipment's type.
     *
     * @param boolean $checkBuspakje
     *
     * @return string
     */
    public function getShipmentType($checkBuspakje = true)
    {
        if ($this->hasShipmentType()) {
            return $this->_getData('shipment_type');
        }

        if (!$this->getShipment(false)) {
            return null;
        }

        $shipmentType = $this->_getShipmentType($checkBuspakje);

        $this->setShipmentType($shipmentType);
        return $shipmentType;
    }

    /**
     * Determines the current shipment's shipment type based on several attributes, including shipping destination and
     * payment method.
     *
     * @param boolean $checkBuspakje
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getShipmentType($checkBuspakje = true)
    {
        if ($this->isCod()) {
            if ($this->isPgeShipment()) {
                return self::SHIPMENT_TYPE_PGE_COD;
            }

            if ($this->isAvondShipment()) {
                return self::SHIPMENT_TYPE_AVOND_COD;
            }

            if ($this->isPakjeGemakShipment()) {
                return self::SHIPMENT_TYPE_PG_COD;
            }

            if ($this->isDutchShipment()) {
                return self::SHIPMENT_TYPE_DOMESTIC_COD;
            }
        }

        if ($this->isPgeShipment()) {
            return self::SHIPMENT_TYPE_PGE;
        }

        if ($this->isAvondShipment()) {
            return self::SHIPMENT_TYPE_AVOND;
        }

        if ($this->isPakjeGemakShipment()) {
            return self::SHIPMENT_TYPE_PG;
        }

        if ($this->isPakketautomaatShipment()) {
            return self::SHIPMENT_TYPE_PA;
        }

        if ($checkBuspakje && $this->isBuspakjeShipment()) {
            return self::SHIPMENT_TYPE_BUSPAKJE;
        }

        if ($this->isDutchShipment()) {
            return self::SHIPMENT_TYPE_DOMESTIC;
        }

        if ($this->isEuShipment()) {
            return self::SHIPMENT_TYPE_EPS;
        }

        if ($this->isGlobalShipment() && $this->getHelper('cif')->isGlobalAllowed()) {
            return self::SHIPMENT_TYPE_GLOBALPACK;
        }

        throw new TIG_PostNL_Exception(
            $this->getHelper()->__('No valid shipment type found for shipment #%s.', $this->getId()),
            'POSTNL-0167'
        );
    }

    /**
     * Returns the formatted shipping phase of the current shipment.
     *
     * @return null|string
     */
    public function getFormattedShippingPhase()
    {
        $shippingPhase = $this->getShippingPhase();
        if (!$shippingPhase) {
            return null;
        }

        $shippingPhases = $this->getHelper('cif')->getShippingPhases();
        if (array_key_exists($shippingPhase, $shippingPhases)) {
            return $shippingPhases[$shippingPhase];
        }

        return null;
    }

    /**
     * Gets all shipping labels associated with this shipment
     *
     * @return array Array of TIG_PostNL_Model_Core_Shipment_Label objects
     */
    public function getLabels()
    {
        if ($this->hasLabels(false)) {
            return $this->_getData('labels');
        }

        $labelCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');
        $labelCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));

        /**
         * If the 'labels_printed' flag is false, yet there are labels present something has gone wrong.
         * Delete the labels so the extension will generate new ones.
         */
        if (!$this->getLabelsPrinted() && $labelCollection->getSize() > 0) {
            $this->deleteLabels();
            return array();
        }

        $labels = $labelCollection->getItems();

        $this->setLabels($labels);
        return $labels;
    }

    /**
     * Get the amount of extra cover this shipment has.
     *
     * @return int|float
     */
    public function getExtraCoverAmount()
    {
        if ($this->hasExtraCoverAmount()) {
            return $this->_getData('extra_cover_amount');
        }

        return 0;
    }

    /**
     * Calculates the total weight of this shipment
     *
     * @param boolean $standardize Whether or not to convert the weight to kg
     * @param boolean $toGrams whether or not to convert the standardized weight to g
     *
     * @return float|int
     */
    public function getTotalWeight($standardize = false, $toGrams = false)
    {
        /**
         * get all items in the shipment
         */
        $items = $this->getShipment()->getAllItems();

        /**
         * calculate the total weight
         */
        $weight = 0;
        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty());
        }

        if ($standardize !== true) {
            return $weight;
        }

        /**
         * Standardize the weight to kg or g.
         *
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $helper = $this->getHelper();
        $weight = $helper->standardizeWeight(
            $weight,
            $this->getStoreId(),
            $toGrams
        );

        return $weight;
    }

    /**
     * Calculates a shipment's base grand total based on it's shipment items
     *
     * @return float|null
     */
    public function getShipmentBaseGrandTotal()
    {
        if ($this->hasShipmentBaseGrandTotal()) {
            return $this->_getData('shipment_base_grand_total');
        }

        /**
         * Check if this PostNL shipment has a linked Mage_Sales_Model_Order_Shipment object
         */
        $shipment = $this->getShipment(false);
        if (!$shipment) {
            return null;
        }

        /**
         * Loop through all associated shipment items and add each item's row total to the shipment's total
         */
        $baseGrandTotal = 0;
        $shipmentItems = $shipment->getAllItems();

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem
         */
        foreach ($shipmentItems as $shipmentItem) {
            $qty = $shipmentItem->getQty();

            /**
             * The base price of a shipment item is only available through it's associated order item
             */
            $basePrice = $shipmentItem->getOrderItem()->getBasePrice();

            /**
             * Calculate and add the shipment item's row total
             */
            $totalBasePrice = $basePrice * $qty;
            $baseGrandTotal += $totalBasePrice;
        }

        $this->setShipmentBaseGrandTotal($baseGrandTotal);
        return $baseGrandTotal;
    }

    /**
     * Gets the url for this shipment's main barcode.
     *
     * @param boolean $forceNl
     *
     * @return string
     *
     * @see TIG_PostNL_Helper_Carrier::getBarcodeUrl()
     */
    public function getBarcodeUrl($forceNl = false)
    {
        if ($this->hasBarcodeUrl()) {
            return $this->_getData('barcode_url');
        }

        $barcode = $this->getMainBarcode();
        if (!$barcode) {
            return false;
        }

        /**
         * @var TIG_PostNL_Helper_Carrier $helper
         */
        $helper = $this->getHelper('carrier');

        $url = '';
        $shippingAddress = $this->getShippingAddress();
        if ($shippingAddress) {
            $url = $helper->getBarcodeUrl($barcode, $shippingAddress, false, $forceNl);
        }

        $this->setBarcodeUrl($url);
        return $url;
    }

    /**
     * Gets the shipment's shipment type for international shipments. If no shipment type is defined, use the default
     * value. This in turn defaults to 'Commercial Goods' if none is specified.
     *
     * @return string|null
     */
    public function getGlobalpackShipmentType()
    {
        if ($this->hasGlobalpackShipmentType()) {
            return $this->_getData('globalpack_shipment_type');
        }

        if (!$this->isGlobalShipment()) {
            return null;
        }

        $defaultShipmentType = Mage::getStoreConfig(self::XPATH_DEFAULT_SHIPMENT_TYPE, $this->getStoreId());
        if (!$defaultShipmentType) {
            $defaultShipmentType = 'Commercial Goods';
        }

        return $defaultShipmentType;
    }

    /**
     * Gets the default product code for this shipment from the module's configuration.
     *
     * The flow for determining the default product code is as follows:
     *  - For domestic COD, Avond COD, PakjeGemak, PakjeGemak COD, PakjeGemak Express, PakjeGemak Express COD,
     *    Pakketautomaat, GlobalPack and Buspakje shipments we have a specific settings in the config that determines
     *    the product code.
     *  - For Avond shipments we first have to check if an option has been saved to the PostNL order table. Otherwise we
     *    use the configured default product option for Avond shipments.
     *  - For EPS shipments we first check if the destination is Belgium and if the 'EPS BE only' option is available.
     *    Otherwise we use the configured default product option for EPS shipments.
     *  - For domestic shipments we first check if an option has been saved to the PostNL order table. If not, we need
     *    to check if an alternative default option has been configured and if the shipment meets the requirements to
     *    use the alternative option. Finally, we get the configured default option for domestic shipments.
     *
     * We then check if the product option determined by this code is actually valid for this shipment. If not, an error
     * is thrown.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return string
     */
    public function getDefaultProductCode()
    {
        if ($this->hasDefaultProductCode()) {
            return $this->_getData('default_product_code');
        }

        $storeId = $this->getStoreId();

        $shipmentType = $this->getShipmentType();

        $postnlOrder = $this->getPostnlOrder();

        $xpath = false;
        switch ($shipmentType) {
            case self::SHIPMENT_TYPE_DOMESTIC_COD:
                $xpath = self::XPATH_DEFAULT_STANDARD_COD_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_AVOND:
                if ($postnlOrder && $postnlOrder->hasOptions()) {
                    $xpath = $this->_getDefaultProductCodeXpathByOptions();
                }

                if (!$xpath) {
                    $xpath = self::XPATH_DEFAULT_EVENING_PRODUCT_OPTION;
                }
                break;
            case self::SHIPMENT_TYPE_AVOND_COD:
                $xpath = self::XPATH_DEFAULT_EVENING_COD_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_PG:
                $xpath = self::XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_PG_COD:
                $xpath = self::XPATH_DEFAULT_PAKJEGEMAK_COD_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_PGE:
                $xpath = self::XPATH_DEFAULT_PGE_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_PGE_COD:
                $xpath = self::XPATH_DEFAULT_PGE_COD_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_PA:
                $xpath = self::XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_EPS:
                $shippingAddress = $this->getShippingAddress();
                if ($this->getHelper()->canUseEpsBEOnlyOption($this->getStoreId())
                    && $shippingAddress
                    && $shippingAddress->getCountryId() == 'BE'
                ) {
                    $xpath = self::XPATH_DEFAULT_EU_BE_PRODUCT_OPTION;
                } else {
                    $xpath = self::XPATH_DEFAULT_EU_PRODUCT_OPTION;
                }
                break;
            case self::SHIPMENT_TYPE_GLOBALPACK:
                $xpath = self::XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION;
                break;
            case self::SHIPMENT_TYPE_BUSPAKJE:
                $xpath = self::XPATH_DEFAULT_BUSPAKJE_PRODUCT_OPTION;
                break;
            //no default
        }

        /**
         * If the shipment is not EU or global, it's dutch (AKA a 'standard' shipment).         *
         */
        if (!$xpath && $postnlOrder && $postnlOrder->hasOptions()) {
            $xpath = $this->_getDefaultProductCodeXpathByOptions();
        }

        /**
         * Dutch shipments may use an alternative default option when the shipment's base grand total exceeds a
         * specified amount.
         */
        $useAlternativeDefault = Mage::getStoreConfig(self::XPATH_USE_ALTERNATIVE_DEFAULT, $storeId);
        if (!$xpath && $useAlternativeDefault) {
            /**
             * Alternative default option usage is enabled.
             */
            $maxShipmentAmount = Mage::getStoreConfig(self::XPATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT, $storeId);
            if ($this->getShipmentBaseGrandTotal() > $maxShipmentAmount) {
                /**
                 * The shipment's base grand total exceeds the specified amount: use the alternative default.
                 */
                $xpath = self::XPATH_ALTERNATIVE_DEFAULT_OPTION;
            }
        }

        /**
         * If we still don't have an xpath, the shipment is a regular domestic shipment.
         */
        if (!$xpath) {
            $xpath = self::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION;
        }

        /**
         * Get the product code configured to the xpath.
         */
        $productCode = Mage::getStoreConfig($xpath, $storeId);

        /**
         * If no default product code was found, try to use another product code that is available.
         */
        if (!$productCode) {
            Zend_Debug::dump($xpath);exit;
            $availableProductCodes = $this->getAllowedProductCodes();

            /**
             * If no other product codes are available for this shipment type, throw an error.
             */
            if (empty($availableProductCodes)) {
                Zend_Debug::dump($availableProductCodes);exit;
                throw new TIG_PostNL_Exception(
                    $this->getHelper()->__(
                        "No default product options are available for this shipment. Please check that you have " .
                        "correctly configured the available product options in the PostNL extension's configuration."
                    ),
                    'POSTNL-0188'
                );
            }

            $productCode = current($availableProductCodes);

            /**
             * Add a warning message indicating that another product code was used and that the merchant should probably
             * check their configuration.
             *
             * @var TIG_PostNL_Helper_Data $helper
             */
            $helper = $this->getHelper();
            $helper->addSessionMessage(
                'adminhtml',
                'POSTNL-0189',
                'warning',
                $helper->__(
                    'The default product option was not available for this shipment, so another product option was ' .
                    'chosen. Please check if the default product options are configured correctly in the PostNL ' .
                    "extension's configuration."
                )
            );
        }

        $this->_checkProductCodeAllowed($productCode);

        $this->setDefaultProductCode($productCode);
        return $productCode;
    }

    /**
     * Gets the xpath for the default product option by saved PostNL Order options. Currently only the
     * 'only_stated_address' option is supported, but this may be expanded in future releases.
     *
     * If multiple options are applicable, the first applicable option is applied.
     *
     * @return bool|string
     */
    protected function _getDefaultProductCodeXpathByOptions()
    {
        $postnlOrder = $this->getPostnlOrder();

        /**
         * If this shipment has no PostNL order or that order doesn't have any options, do nothing.
         */
        if (!$postnlOrder || !$postnlOrder->hasOptions()) {
            return false;
        }

        /**
         * If the options are empty, do nothing.
         */
        $options = $postnlOrder->getOptions();
        if (empty($options)) {
            return false;
        }

        /**
         * Unserialize the options and check loop through them.
         */
        foreach ($options as $option => $value) {
            /**
             * If the option has no true value, move on to the next option.
             */
            if (!$value) {
                continue;
            }

            switch ($option) {
                case 'only_stated_address':
                    return self::XPATH_DEFAULT_STATED_ADDRESS_ONLY_OPTION;
                    break;
                //no default
            }
        }

        return false;
    }

    /**
     * Gets this shipment's main barcode.
     *
     * @return string|null
     */
    public function getMainBarcode()
    {
        if ($this->hasMainBarcode()) {
            return $this->_getData('main_barcode');
        }

        /**
         * Check if the current product code needs a custom barcode.
         */
        $customBarcodes = $this->getCustomBarcodes();
        $productCode    = $this->getProductCode();

        if (array_key_exists($productCode, $customBarcodes)) {
            return $customBarcodes[$productCode];
        }

        return null;
    }

    /**
     * Get a specific barcode for this shipment
     *
     * @param int|null $barcodeNumber Which barcode to get
     *
     * @return string|null
     */
    public function getBarcode($barcodeNumber = null)
    {
        if (is_null($barcodeNumber) || $barcodeNumber == 0) {
            $barcode = $this->getMainBarcode();
            return $barcode;
        }

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Barcode $barcode
         */
        $barcode = Mage::getModel('postnl_core/shipment_barcode');
        $barcode->loadByParentAndBarcodeNumber($this->getId(), $barcodeNumber);

        return $barcode->getBarcode();
    }

    /**
     * Get all barcodes associated with this shipment
     *
     * @param boolean $asObject Optional value to get the barcodes as entities, rather than an array of values
     *
     * @return array
     */
    public function getBarcodes($asObject = false)
    {
        $barcodeCollection = Mage::getResourceModel('postnl_core/shipment_barcode_collection');
        $barcodeCollection->addFieldToSelect(array('barcode', 'barcode_number'))
                          ->addFieldToFilter('parent_id', array('eq' => $this->getId()));

        $barcodeCollection->getSelect()->order('barcode_number ASC');

        if ($asObject === false) {
            $barcodeNumbers = $barcodeCollection->getColumnValues('barcode_number');
            $barcodes       = $barcodeCollection->getColumnValues('barcode');

            /**
             * Combine the arrays so that the barcode numbers are the keys and the barcodes themselves are the values
             */
            $barcodeArray = array_combine($barcodeNumbers, $barcodes);
            $barcodeArray[0] = $this->getMainBarcode();

            return $barcodeArray;
        }

        /**
         * Return all barcode entities.
         * N.B. Does not contain the main barcode as it is not part of the collection
         */
        return $barcodeCollection->getItems();
    }

    /**
     * Alias for magic getIsParcelwareExported()
     *
     * @return string
     */
    public function getIsExported()
    {
        return $this->getIsParcelwareExported();
    }

    /**
     * Gets a PostNL order associated with this shipment (if any exist)
     *
     * @return false|TIG_PostNL_Model_Core_Order
     */
    public function getPostnlOrder()
    {
        if ($this->hasPostnlOrder()) {
            return $this->_getData('postnl_order');
        }

        $postnlOrder = Mage::getModel('postnl_core/order')->load($this->getOrderId(), 'order_id');
        if (!$postnlOrder->getId()) {
            $this->setPostnlOrder(false);

            return false;
        }

        $this->setPostnlOrder($postnlOrder);
        return $postnlOrder;
    }

    /**
     * Get collection object for this shipment's labels.
     *
     * @return TIG_PostNL_Model_Core_Resource_Shipment_Label_Collection
     */
    public function getLabelCollection()
    {
        if ($this->hasLabelCollection()) {
            return $this->_getData('label_collection');
        }

        $labelCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');
        $labelCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));

        $this->setLabelCollection($labelCollection);
        return $labelCollection;
    }

    /**
     * Gets allowed product codes for the current shipment.
     *
     * @return array
     */
    public function getAllowedProductCodes()
    {
        $allowedProductOptions = $this->getAllowedProductOptions();

        $productCodes = array_keys($allowedProductOptions);
        return $productCodes;
    }

    /**
     * Gets allowed product options for the current shipment.
     *
     * @param boolean $flat
     * @param boolean $checkBuspakje
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    public function getAllowedProductOptions($flat = true, $checkBuspakje = false)
    {
        $cifHelper = $this->getHelper('cif');

        $shipmentType = $this->getShipmentType($checkBuspakje);
        switch ($shipmentType) {
            case self::SHIPMENT_TYPE_DOMESTIC:
                $allowedProductCodes = $cifHelper->getStandardProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_DOMESTIC_COD:
                $allowedProductCodes = $cifHelper->getStandardCodProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_AVOND:
                $allowedProductCodes = $cifHelper->getAvondProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_AVOND_COD:
                $allowedProductCodes = $cifHelper->getAvondCodProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_PG:
                $allowedProductCodes = $cifHelper->getPakjeGemakProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_PG_COD:
                $allowedProductCodes = $cifHelper->getPakjeGemakCodProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_PGE:
                $allowedProductCodes = $cifHelper->getPgeProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_PGE_COD:
                $allowedProductCodes = $cifHelper->getPgeCodProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_PA:
                $allowedProductCodes = $cifHelper->getPakketautomaatProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_EPS:
                $allowedProductCodes = $cifHelper->getEuProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_GLOBALPACK:
                $allowedProductCodes = $cifHelper->getGlobalProductCodes($flat);
                break;
            case self::SHIPMENT_TYPE_BUSPAKJE:
                $allowedProductCodes = $cifHelper->getBuspakjeProductCodes($flat);
                break;
            default:
                $allowedProductCodes = array();
                break;
        }

        return $allowedProductCodes;
    }

    /**
     * Gets the delivery date for this shipment.
     *
     * @return string
     */
    public function getDeliveryDate()
    {
        if ($this->hasDeliveryDate()) {
            return $this->_getData('delivery_date');
        }

        /**
         * Try to get the delivery date for a PostNL order.
         */
        $postnlOrder = $this->getPostnlOrder();
        if ($postnlOrder && $postnlOrder->hasDeliveryDate()) {
            $deliveryDate = $postnlOrder->getDeliveryDate();

            $this->setDeliveryDate($deliveryDate);
            return $deliveryDate;
        }

        /**
         * @var TIG_PostNL_Helper_DeliveryOptions $helper
         */
        $helper = $this->getHelper('deliveryOptions');
        $orderDate = Mage::getSingleton('core/date')->date(null, $this->getOrder()->getCreatedAt());
        $deliveryDate = $helper->getDeliveryDate($orderDate, $this->getStoreId());

        $deliveryDate = $helper->getValidDeliveryDate($deliveryDate)->format('Y-m-d H:i:s');

        return $deliveryDate;
    }

    /**
     * Get whether this shipment is a buspakje shipment. If no value is set, calculate the correct value and set it.
     *
     * @return bool
     */
    public function getIsBuspakje()
    {
        $isBuspakje = $this->_getData('is_buspakje');

        if (!is_null($isBuspakje)) {
            return $isBuspakje;
        }

        $isBuspakje = $this->_getIsBuspakje();

        $this->setIsBuspakje($isBuspakje);
        return $isBuspakje;
    }

    /**
     * Gets the default extra cover amount for this shipment.
     *
     * @return float|int
     */
    public function getDefaultExtraCoverAmount()
    {
        if ($this->isGlobalShipment()) {
            return 200;
        }

        $shipmentAmount = $this->getShipmentBaseGrandTotal();
        $extraCoverAmount = ceil($shipmentAmount / 500) * 500;

        return $extraCoverAmount;
    }

    /**
     * Getter for the '_preventSaving' class variable.
     *
     * @return bool
     */
    public function getPreventSaving()
    {
        return $this->_preventSaving;
    }

    /*******************************************************************************************************************
     * SETTER METHODS
     ******************************************************************************************************************/

    /**
     * Set an array of labels that are to be saved together
     *
     * @param array $labels
     *
     * @return $this
     */
    public function setLabelsToSave($labels)
    {
        $this->_labelsToSave = $labels;

        return $this;
    }

    /**
     * Set an extra cover amount
     *
     * @param int|null $amount
     *
     * @return false|TIG_PostNL_Model_Core_Shipment
     */
    public function setExtraCoverAmount($amount = null)
    {
        /**
         * Check if extra cover is allowed for this shipment
         */
        if (!$this->isExtraCover()) {
            return false;
        }

        if (is_null($amount)) {
            $amount = $this->getDefaultExtraCoverAmount();
        }

        $this->setData('extra_cover_amount', $amount);

        return $this;
    }

    /**
     * Sets a shipment's shipment type. This is required for GlobalPack shipments
     *
     * @param string $type
     *
     * @return $this
     */
    public function setGlobalpackShipmentType($type)
    {
        /**
         * Only global shipments have a shipment type
         */
        if (!$this->isGlobalShipment()) {
            return $this;
        }

        /**
         * Convert shipment type to CIF-compatible version
         */
        $shipmentType = str_replace('_', ' ', $type);
        $shipmentType = ucwords($shipmentType);

        $this->setData('globalpack_shipment_type', $shipmentType);
        return $this;
    }

    /**
     * Set this shipment's parcel count. Verifies that the requested amount does not exceed the maximum allowed.
     *
     * @param int $count
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function setParcelCount($count)
    {
        $maxParcelCount = Mage::getStoreConfig(self::XPATH_MAX_PARCEL_COUNT, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$maxParcelCount) {
            $this->setData('parcel_count', $count);
            return $this;
        }

        if ($count > $maxParcelCount) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Number of parcels not allowed. Amount requested: %s, maximum allowed: %s.',
                    $count,
                    $maxParcelCount
                ),
                'POSTNL-0068'
            );
        }

        $this->setData('parcel_count', $count);
        return $this;
    }

    /**
     * Alias for magic setIsParcelwareExported()
     *
     * @param mixed $isExported
     *
     * @return $this
     */
    public function setIsExported($isExported)
    {
        return $this->setIsParcelwareExported($isExported);
    }

    /**
     * Sets the process used for locking and unlocking this shipment
     *
     * @param TIG_PostNL_Model_Core_Shipment_Process
     *
     * @return $this
     */
    public function setProcess(TIG_PostNL_Model_Core_Shipment_Process $process)
    {
        $this->_process = $process;

        return $this;
    }

    /**
     * Sets the confirm date. If no value is supplied, check if this shipment has an associated PostNL order which might
     * have a confirm date specified. Otherwise calculate the confirm date based on the delivery date or the current
     * timestamp.
     *
     * @param boolean|string $date
     *
     * @return $this
     */
    public function setConfirmDate($date = false)
    {
        if ($date !== false) {
            $this->setData('confirm_date', $date);
            return $this;
        }

        /**
         * If this shipment has an associated PostNL order with a confirm date, use that.
         */
        $postnlOrder = $this->getPostnlOrder();
        if ($postnlOrder && $postnlOrder->hasConfirmDate()) {
            $confirmDate = new DateTime($postnlOrder->getConfirmDate());

            $this->setData('confirm_date', $confirmDate->getTimestamp());
            return $this;
        }

        /**
         * Get the requested delivery date for this shipment.
         */
        $deliveryDate = $this->getDeliveryDate();

        /**
         * Calculate the confirm based on the delivery date.
         */
        $deliveryDate = new DateTime($deliveryDate);

        $confirmDate = clone $deliveryDate;
        $confirmDate = $confirmDate->sub(new DateInterval('P1D'));

        $this->getHelper('deliveryOptions')->getValidConfirmDate($confirmDate);

        $this->setData('confirm_date', $confirmDate->getTimestamp());
        return $this;
    }

    /**
     * Sets the current shipment's phase. Triggers an event if the phase is valid.
     *
     * @param mixed $phase
     *
     * @return $this
     */
    public function setShippingPhase($phase)
    {
        $this->setData('shipping_phase', $phase);

        if (is_numeric($phase) && $phase != $this->getShippingPhase()) {
            $phases = $this->getHelper('cif')->getShippingPhaseCodes();

            if (array_key_exists($phase, $phases)) {
                $phaseName = $phases[$phase];
                Mage::dispatchEvent('postnl_shipment_setshippingphase_' . $phaseName, array('shipment' => $this));
            }
        }

        return $this;
    }

    /**
     * Setter for the '_preventSaving' class variable.
     *
     * @param boolean $value
     *
     * @return $this
     */
    public function setPreventSaving($value)
    {
        $this->_preventSaving = (bool) $value;

        return $this;
    }

    /*******************************************************************************************************************
     * HAS- METHODS
     ******************************************************************************************************************/

    /**
     * Check if the shipment has any associated labels
     *
     * @param boolean $checkCollection
     *
     * @return boolean
     */
    public function hasLabels($checkCollection = true)
    {
        if ($this->_getData('labels')) {
            return true;
        }

        if (!$checkCollection) {
            return false;
        }

        $labelCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');
        $labelCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));

        if ($labelCollection->getSize() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Checks if this shipment has extra cover
     *
     * @return boolean
     */
    public function hasExtraCover()
    {
        $productCode = $this->getProductCode();
        $extraCoverProductCodes = $this->getExtraCoverProductCodes();

        if (!in_array($productCode, $extraCoverProductCodes)) {
            return false;
        }

        if ($this->getExtraCoverAmount() < 1) {
            return false;
        }

        return true;
    }

    /**
     * Check if this shipment has a label of a given type
     *
     * @param string $labelType
     *
     * @return boolean
     */
    public function hasLabelType($labelType)
    {
        /**
         * @var Mage_Core_Model_Resource $coreResource
         */
        $coreResource = Mage::getSingleton('core/resource');
        $readConn = $coreResource->getConnection('core/read');

        $select = $readConn->select();
        $select->from($coreResource->getTableName('postnl_core/shipment_label', array('label_id')))
               ->where('`label_type` = ?', $labelType)
               ->where('`parent_id` = ?', $this->getId());

        $label = $readConn->fetchOne($select);

        if ($label === false) {
            return false;
        }

        return true;
    }

    /**
     * Checks if this shipment uses a custom barcode.
     *
     * @return bool
     */
    public function hasCustomBarcode()
    {
        $productCode = $this->getProductCode();

        $customBarcodes         = $this->getCustomBarcodes();
        $disallowedProductCodes = array_keys($customBarcodes);

        if (in_array($productCode, $disallowedProductCodes)) {
            return true;
        }

        return false;
    }

    /*******************************************************************************************************************
     * IS- AND CAN- METHODS
     ******************************************************************************************************************/

    /**
     * Alias for magic getIsPakjeGemak()
     *
     * Please note the difference between this method and TIG_PostNL_Model_Core_Shipment::isPakjeGemakShipment()
     *
     * @return integer
     */
    public function isPakjeGemak()
    {
        return $this->getIsPakjeGemak();
    }

    /**
     * Check if the shipping destination of this shipment is NL
     *
     * @return boolean
     */
    public function isDutchShipment()
    {
        if ($this->getIsDutchShipment()) {
            return true;
        }

        $shippingAddress = $this->getShippingAddress();
        if (!$shippingAddress) {
            return false;
        }

        $shippingDestination = $shippingAddress->getCountryId();

        if ($shippingDestination == 'NL') {
            return true;
        }

        return false;
    }

    /**
     * Check if the shipping destination of this shipment is a EU country
     *
     * @return boolean
     */
    public function isEuShipment()
    {
        if ($this->getIsEuShipment()) {
            return true;
        }

        $shippingAddress = $this->getShippingAddress();
        if (!$shippingAddress) {
            return false;
        }

        $shippingDestination = $shippingAddress->getCountryId();

        /**
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $helper = $this->getHelper('cif');
        $euCountries = $helper->getEuCountries();

        if (in_array($shippingDestination, $euCountries)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the shipping destination of this shipment is global (not NL or EU)
     *
     * @return boolean
     */
    public function isGlobalShipment()
    {
        if ($this->getIsGlobalShipment()) {
            return true;
        }

        if (!$this->isDutchShipment() && !$this->isEuShipment()) {
            return true;
        }

        return false;
    }

    /**
     * Check if this shipment is a PakjeGemak Express shipment.
     *
     * @return bool
     */
    public function isPgeShipment()
    {
        /**
         * We can check the PostNL order's type to see if it's PakjeGemak Express.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = $this->getPostnlOrder();
        if (!$postnlOrder
            || !$postnlOrder->getId()
        ) {
            return false;
        }

        $type = $postnlOrder->getType();
        if ($type != 'PGE') {
            return false;
        }

        return true;
    }

    /**
     * Check if this shipment is an evening delivery shipment.
     *
     * @return bool
     */
    public function isAvondShipment()
    {
        /**
         * We can check the PostNL order's type to see if it's evening delivery.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = $this->getPostnlOrder();
        if (!$postnlOrder
            || !$postnlOrder->getId()
        ) {
            return false;
        }

        $type = $postnlOrder->getType();
        if ($type != 'Avond') {
            return false;
        }

        return true;
    }

    /**
     * Check if the current shipment is a PakjeGemak shipment.
     *
     * @return boolean
     */
    public function isPakjeGemakShipment()
    {
        if ($this->getIsPakjeGemak()) {
            return true;
        }

        /**
         * If the order was placed using PostNL Checkout, we can check if it was a PakjeGemak order directly.
         *
         * @var TIG_PostNL_Model_Core_Order $postnlOrder
         */
        $postnlOrder = $this->getPostnlOrder();
        if ($postnlOrder
            && $postnlOrder->getId()
            && $postnlOrder->getIsPakjeGemak()
        ) {
            return true;
        }

        /**
         * Otherwise we need to check the product code by comparing it to known PakjeGemak product codes.
         *
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $helper = $this->getHelper('cif');
        $pakjeGemakProductCodes = $helper->getPakjeGemakProductCodes();
        $productCode = $this->_getData('product_code');

        if (!$productCode) {
            return false;
        }

        if (in_array($productCode, $pakjeGemakProductCodes)) {
            $this->setIsPakjeGemak(true);
            return true;
        }

        return false;
    }

    /**
     * Check if this shipment is a pakketautomaat shipment.
     *
     * @return boolean
     */
    public function isPakketautomaatShipment()
    {
        if ($this->hasIsPakketautomaat()) {
            return $this->getIsPakketautomaat();
        }

        $postnlOrder = $this->getPostnlOrder();
        if (!$postnlOrder
            || !$postnlOrder->getId()
            || !$postnlOrder->getType()
        ) {
            $this->setIsPakketautomaat(false);
            return false;
        }

        $type = $postnlOrder->getType();
        if ($type == 'PA') {
            $this->setIsPakketautomaat(true);
            return true;
        }

        return false;
    }

    /**
     * Check if this shipment is a buspakje shipment.
     *
     * @return boolean
     */
    public function isBuspakjeShipment()
    {
        if ($this->hasIsBuspakjeShipment()) {
            return $this->getIsBuspakjeShipment();
        }

        $isBuspakje = $this->getIsBuspakje();

        $this->setIsBuspakjeShipment($isBuspakje);
        return $isBuspakje;
    }

    /**
     * Checks if this shipment is a COD shipment.
     *
     * @return boolean
     */
    public function isCod()
    {
        $codPaymentMethods = Mage::helper('postnl/payment')->getCodPaymentMethods();

        /**
         * @var Mage_Sales_Model_Order_Payment $payment
         */
        $payment = $this->getPayment();
        $paymentMethod = $payment->getMethod();

        if (in_array($paymentMethod, $codPaymentMethods)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if this shipment is confirmed
     *
     * @return boolean
     */
    public function isConfirmed()
    {
        $confirmedStatus = $this->getConfirmStatus();
        if ($confirmedStatus === self::CONFIRM_STATUS_CONFIRMED) {
            return true;
        }

        return false;
    }

    /**
     * Alias for isParcelwareExported()
     *
     * @return boolean
     */
    public function isExported()
    {
        return $this->isParcelwareExported();
    }

    /**
     * Checks if this shipment has been exported to parcelware
     *
     * @return boolean
     */
    public function isParcelwareExported()
    {
        $isExported = (bool) $this->getIsParcelwareExported();

        return $isExported;
    }

    /**
     * Check if this shipment is an extra cover shipment.
     *
     * @return boolean
     */
    public function isExtraCover()
    {
        $productCode = $this->getProductCode();
        if (!$productCode) {
            return false;
        }

        $extraCoverProductCodes = $this->getExtraCoverProductCodes();
        if (in_array($productCode, $extraCoverProductCodes)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current entity may generate a barcode.
     *
     * @return boolean
     */
    public function canGenerateBarcode()
    {
        if (!$this->getShipmentId() && !$this->getShipment(false)) {
            return false;
        }

        if (!$this->canGenerateBarcodeForProductCode()) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether a barcode may be generated for a given product code.
     *
     * @param bool|string|int $productCode
     *
     * @return bool
     */
    public function canGenerateBarcodeForProductCode($productCode = false)
    {
        if (!$productCode) {
            $productCode = $this->getProductCode();
        }

        $customBarcodes = $this->getCustomBarcodes();
        $disallowedProductCodes = array_keys($customBarcodes);
        if (in_array($productCode, $disallowedProductCodes)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current entity can be confirmed.
     *
     * @param bool $skipEuCheck
     *
     * @return boolean
     */
    public function canConfirm($skipEuCheck = false)
    {
        if ($this->isLocked()) {
            return false;
        }

        if ($this->getConfirmStatus() == self::CONFIRM_STATUS_CONFIRMED) {
            return false;
        }

        if (!$this->getShipmentId() && !$this->getShipment(false)) {
            return false;
        }

        if (!$this->getMainBarcode()) {
            return false;
        }

        if ($skipEuCheck === false
            && $this->isEuShipment()
            && !$this->getLabelsPrinted()
        ) {
            return false;
        }

        if ($this->hasCustomBarcode()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment is eligible for a shipping status update.
     * Unconfirmed shipments, shipments whose labels are not yet printed or shipments that are already delivered are
     * ineligible. Also shipments which use a custom barcode are ineligible.
     *
     * @return boolean
     */
    public function canUpdateShippingStatus()
    {
        if ($this->isLocked()) {
            return false;
        }

        if (self::CONFIRM_STATUS_CONFIRMED != $this->getConfirmStatus()) {
            return false;
        }

        if (self::SHIPPING_PHASE_DELIVERED == $this->getShippingPhase()) {
            return false;
        }

        if (!$this->getLabelsPrinted()) {
            return false;
        }

        if (!$this->hasLabels()) {
            return false;
        }

        if (!$this->getMainBarcode()) {
            return false;
        }

        $customBarcodes = $this->getCustomBarcodes();
        $productCode    = $this->getProductCode();

        if (array_key_exists($productCode, $customBarcodes)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment is eligible for a complete shipping status update.
     *
     * @return boolean
     */
    public function canUpdateCompleteShippingStatus()
    {
        if ($this->isLocked()) {
            return false;
        }

        if (self::CONFIRM_STATUS_CONFIRMED != $this->getConfirmStatus()) {
            return false;
        }

        if (!$this->getLabelsPrinted()) {
            return false;
        }

        if (!$this->getMainBarcode()) {
            return false;
        }

        $customBarcodes = $this->getCustomBarcodes();
        $productCode    = $this->getProductCode();

        if (array_key_exists($productCode, $customBarcodes)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether a tracking code may be added to this shipment.
     *
     * @return bool
     */
    public function canAddTrackingCode()
    {
        /**
         * If this shipment has a custom barcode, we can't add tracking info.
         */
        if ($this->hasCustomBarcode()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment can send a track & trace email to the customer.
     *
     * @param boolean $ignoreAlreadySent Flag to ignore the 'already sent' check
     * @param boolean $ignoreConfig      Flag to ignore the 'send_track_and_trace_email' config setting.
     *
     * @return boolean
     */
    public function canSendTrackAndTraceEmail($ignoreAlreadySent = false, $ignoreConfig = false)
    {
        if ($this->isLocked()) {
            return false;
        }

        /**
         * If the email is already sent, don't send it again.
         */
        if ($ignoreAlreadySent !== true && $this->getTrackAndTraceEmailSent()) {
            return false;
        }

        /**
         * If we have no barcode, there is no point in sending the track and trace email.
         */
        if (!$this->getMainBarcode()) {
            return false;
        }

        /**
         * If this shipment uses a custom barcode we can't send the track and trace email, because custom barcodes can't
         * be tracked.
         */
        if ($this->hasCustomBarcode()) {
            return false;
        }

        /**
         * Make sure sending the email is allowed in the config.
         */
        if ($ignoreConfig !== true) {
            $storeId = $this->getStoreId();
            $canSendTrackAndTrace = Mage::getStoreConfig(self::XPATH_SEND_TRACK_AND_TRACE_EMAIL, $storeId);
            if (!$canSendTrackAndTrace) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if this shipment's confirmation status can be reset.
     *
     * @return boolean
     */
    public function canResetConfirmation()
    {
        $confirmStatus = $this->getConfirmStatus();
        if ($confirmStatus == self::CONFIRM_STATUS_CONFIRM_EXPIRED) {
            return false;
        }

        if ($confirmStatus == self::CONFIRM_STATUS_UNCONFIRMED) {
            return false;
        }

        $shippingPhase = $this->getShippingPhase();
        if ($shippingPhase == self::SHIPPING_PHASE_DELIVERED
            || $shippingPhase == self::SHIPPING_PHASE_DISTRIBUTION
            || $shippingPhase == self::SHIPPING_PHASE_SORTING
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment can be converted to a buspakje shipment.
     *
     * @return bool
     */
    public function canConvertShipmentToBuspakje()
    {
        $shipmentType = $this->getShipmentType();
        if ($shipmentType != self::SHIPMENT_TYPE_DOMESTIC) {
            return false;
        }

        $shippingPhase = $this->getShippingPhase();
        if ($shippingPhase == self::SHIPPING_PHASE_DELIVERED
            || $shippingPhase == self::SHIPPING_PHASE_DISTRIBUTION
            || $shippingPhase == self::SHIPPING_PHASE_SORTING
        ) {
            return false;
        }

        if (!$this->getHelper()->canUseBuspakje()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment can be converted to a package shipment.
     *
     * @return bool
     */
    public function canConvertShipmentToPackage()
    {
        $shipmentType = $this->getShipmentType();
        if ($shipmentType != self::SHIPMENT_TYPE_BUSPAKJE) {
            return false;
        }

        $shippingPhase = $this->getShippingPhase();
        if ($shippingPhase == self::SHIPPING_PHASE_DELIVERED
            || $shippingPhase == self::SHIPPING_PHASE_DISTRIBUTION
            || $shippingPhase == self::SHIPPING_PHASE_SORTING
        ) {
            return false;
        }

        if (!$this->getHelper()->canUseStandard()) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current shipment's product code may be changed.
     *
     * @param boolean $checkAvailableProductOptions
     *
     * @return boolean
     */
    public function canChangeProductCode($checkAvailableProductOptions = true)
    {
        if ($this->isConfirmed()) {
            return false;
        }

        $shippingPhase = $this->getShippingPhase();
        if ($shippingPhase == self::SHIPPING_PHASE_DELIVERED
            || $shippingPhase == self::SHIPPING_PHASE_DISTRIBUTION
            || $shippingPhase == self::SHIPPING_PHASE_SORTING
        ) {
            return false;
        }

        if (!$checkAvailableProductOptions) {
            return true;
        }

        /**
         * Get all available product options and this shipment's current product code.
         */
        $availableProductOptions = $this->getAllowedProductOptions();
        $productCode = $this->getProductCode();

        if (count($availableProductOptions) === 1 && isset($availableProductOptions[$productCode])) {
            /**
             * If only 1 product option is available and it's the same as the current product code, then we can't change
             * it.
             */
            return false;
        } elseif (count($availableProductOptions)) {
            /**
             * If there are any available product options, then we can change it.
             */
            return true;
        }

        return false;
    }

    /*******************************************************************************************************************
     * SHIPMENT LOCKING AND UNLOCKING FUNCTIONS
     ******************************************************************************************************************/

    /**
     * Lock this shipment to prevent simultaneous execution
     *
     * @return $this
     */
    public function lock()
    {
        $process = $this->getProcess();
        $process->lockAndBlock();

        $this->isLocked();
        return $this;
    }

    /**
     * Unlock this shipment
     *
     * @return $this
     */
    public function unlock()
    {
        $process = $this->getProcess();
        $process->unlock();

        return $this;
    }

    /**
     * Check if this shipment is locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        $process = $this->getProcess();
        $isLocked = $process->isLocked();

        return $isLocked;
    }

    /*******************************************************************************************************************
     * CIF FUNCTIONALITY METHODS
     ******************************************************************************************************************/

    /**
     * Generates barcodes for this postnl shipment.
     * Barcodes are the basis for all CIF functionality and must therefore be generated before any further action is
     * possible.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function generateBarcodes()
    {
        if (!$this->canGenerateBarcode()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The generateBarcodes action is currently unavailable.'),
                'POSTNL-0069'
            );
        }

        $this->lock();

        Mage::dispatchEvent('postnl_shipment_generatebarcode_before', array('shipment' => $this));

        /**
         * Generate and save the main barcode.
         */
        $mainBarcode = $this->_generateBarcode();
        $this->setMainBarcode($mainBarcode);

        $parcelCount = $this->getParcelCount();
        if (!$parcelCount) {
            $parcelCount = $this->_calculateParcelCount();
        }

        /**
         * If this shipment consists of a single parcel we only need the main barcode.
         */
        if ($parcelCount < 2) {
            Mage::dispatchEvent('postnl_shipment_generatebarcode_after', array('shipment' => $this));
            $this->unlock();

            return $this;
        }

        /**
         * Generate a barcode for each parcel and save it.
         */
        for ($i = 1; $i < $parcelCount; $i++) {
            $barcode = $this->_generateBarcode();
            $this->_addBarcode($barcode, $i);
        }

        Mage::dispatchEvent('postnl_shipment_generatebarcode_after', array('shipment' => $this));
        $this->unlock();
        return $this;
    }

    /**
     * Generates a single barcode for this postnl shipment.
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     *
     */
    protected function _generateBarcode()
    {
        $shipment = $this->getShipment();

        /**
         * @var TIG_PostNL_Model_Core_Cif $cif
         * @var TIG_PostNL_Helper_Cif     $helper
         */
        $cif = Mage::getModel('postnl_core/cif');
        $cif->setStoreId($this->getStoreId());
        $helper = $this->getHelper('cif');
        $barcodeType = $helper->getBarcodeTypeForShipment($this);

        $barcode = $cif->generateBarcode($shipment, $barcodeType);

        if (!$barcode) {
            throw new TIG_PostNL_Exception(
                $helper->__('Unable to generate barcode for this shipment: %s', $shipment->getId()),
                'POSTNL-0070'
            );
        }

        /**
         * If the generated barcode already exists a new one needs to be generated.
         */
        if ($helper->barcodeExists($barcode)) {
            return $this->_generateBarcode();
        }

        return $barcode;
    }

    /**
     * Generates a shipping labels for a shipment without confirming it with PostNL.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function generateLabel()
    {
        $this->lock();

        Mage::dispatchEvent('postnl_shipment_generatelabel_before', array('shipment' => $this));

        $parcelCount = $this->getParcelCount();
        if (!$parcelCount) {
            $parcelCount = $this->_calculateParcelCount();
        }

        /**
         * Generate labels purely for the main shipment
         */
        if ($parcelCount < 2) {
            $labels = $this->_generateLabel();
            $this->addLabels($labels);

            $this->_saveLabels();

            Mage::dispatchEvent('postnl_shipment_generatelabel_after', array('shipment' => $this));

            $this->unlock();
            return $this;
        }

        /**
         * Generate labels for each parcel in the shipment
         */
        for ($i = 0; $i < $parcelCount; $i++) {
            $labels = $this->_generateLabel(false, $i);
            $this->addLabels($labels);
        }

        $this->_saveLabels();

        Mage::dispatchEvent('postnl_shipment_generatelabel_after', array('shipment' => $this));

        $this->unlock();
        return $this;
    }

    /**
     * Get a shipping label from PostNL for a single parcel or a whole shipment.
     *
     * @param boolean       $confirm       Whether or not to also confirm the shipment.
     * @param bool|int|null $barcodeNumber An optional barcode number. If this parameter is null, the main barcode will
     *                                     be used.
     *
     * @throws TIG_PostNL_Exception
     * @return array
     */
    protected function _generateLabel($confirm = false, $barcodeNumber = false)
    {
        $mainBarcode = $this->getMainBarcode();

        /**
         * if $barcodeNumber is false, this is a single parcel shipment
         */
        if ($barcodeNumber === false) {
            $barcode = $mainBarcode;
        } else {
            $barcode = $this->getBarcode($barcodeNumber);
            $barcodeNumber++; //while barcode numbers start at 0, shipment numbers start at 1
        }

        /**
         * @var TIG_PostNL_Model_Core_Cif $cif
         */
        $cif = Mage::getModel('postnl_core/cif');
        $cif->setStoreId($this->getStoreId());

        /**
         * @var StdClass $result
         */
        if ($confirm === false) {
            $result = $cif->generateLabelsWithoutConfirm($this, $barcode, $mainBarcode, $barcodeNumber);
        } else {
            $result = $cif->generateLabels($this, $barcode, $mainBarcode, $barcodeNumber);
        }

        if (!isset($result->Labels, $result->Labels->Label)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'The confirmAndPrintLabel action returned an invalid response: %s',
                    var_export($result, true)
                ),
                'POSTNL-0071'
            );
        }
        $labels = $result->Labels->Label;

        /**
         * If this is an EU shipment and a non-combi label was returned, the product code needs to be updated.
         */
        if ($this->isEuShipment() && !$this->_isCombiLabel()) {
            $this->setProductCode($result->ProductCodeDelivery);
        }

        return $labels;
    }

    /**
     * Manually confirms a shipment without communicating with PostNL. This should be used if you wish to update the
     * confirmation status in Magento, while actually confirming the shipment through other means, such as Parcelware.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function registerConfirmation()
    {
        Mage::dispatchEvent('postnl_shipment_register_confirmation_before', array('shipment' => $this));

        /**
         * @var Mage_Core_Model_Date $dateModel
         */
        $dateModel = Mage::getModel('core/date');
        $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
             ->setConfirmedAt($dateModel->gmtTimestamp());

        Mage::dispatchEvent('postnl_shipment_register_confirmation_after', array('shipment' => $this));

        return $this;
    }

    /**
     * Confirm the shipment with PostNL without generating new labels
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function confirm()
    {
        if (!$this->canConfirm()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The confirm action is currently unavailable.'),
                'POSTNL-0109'
            );
        }

        $this->lock();

        Mage::dispatchEvent('postnl_shipment_confirm_before', array('shipment' => $this));

        $parcelCount = $this->getParcelCount();
        if (!$parcelCount) {
            $parcelCount = $this->_calculateParcelCount();
        }

        /**
         * Only confirm the main shipment
         */
        if ($parcelCount < 2) {
            $this->_confirm();

            $this->registerConfirmation();

            Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));

            $this->unlock();
            return $this;
        }

        /**
         * Confirm each parcel in the shipment separately
         */
        for ($i = 0; $i < $parcelCount; $i++) {
            $this->_confirm($i);
        }

        $this->registerConfirmation();

        Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));

        $this->unlock();
        return $this;
    }

    /**
     * Confirms the shipment using CIF.
     *
     * @param bool|int|null $barcodeNumber
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    protected function _confirm($barcodeNumber = false)
    {
        $mainBarcode = $this->getMainBarcode();

        /**
         * if $barcodeNumber is false, this is a single parcel shipment
         */
        if ($barcodeNumber === false) {
            $barcode = $mainBarcode;
        } else {
            $barcode = $this->getBarcode($barcodeNumber);
            $barcodeNumber++; //while barcode numbers start at 0, shipment numbers start at 1
        }

        /**
         * @var TIG_PostNL_Model_Core_Cif $cif
         * @var StdClass                  $result
         */
        $cif = Mage::getModel('postnl_core/cif');
        $cif->setStoreId($this->getStoreId());
        $result = $cif->confirmShipment($this, $barcode, $mainBarcode, $barcodeNumber);

        $responseShipment = $result->ConfirmingResponseShipment;

        /**
         * If the ConfirmingResponseShipment is an object, it means only one shipment was confirmed and the returned
         * barcode has to be the shipment's main barcode.
         */
        if (is_object($responseShipment)
            && isset($responseShipment->Barcode)
            && $responseShipment->Barcode == $barcode
        ) {
            return $this;
        }

        /**
         * If the ConfirmingResponseShipment is an array, it may indicate multiple shipments were confirmed. We need to
         * check the first shipment's barcode to see if it matches the main barcode.
         */
        if (is_array($responseShipment)) {
            $mainResponseShipment = reset($responseShipment);

            if (is_object($mainResponseShipment)
                && isset($mainResponseShipment->Barcode)
                && $mainResponseShipment->Barcode == $barcode
            ) {
                return $this;
            }
        }

        /**
         * The response was not valid; throw an exception
         */
        throw new TIG_PostNL_Exception(
            Mage::helper('postnl')->__('Invalid confirm response received: %s', var_export($result, true)),
            'POSTNL-0072'
        );
    }

    /**
     * Generates a shipping label and confirms the shipment with postNL.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function confirmAndGenerateLabel()
    {
        if (!$this->canConfirm(true)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The confirm action is currently unavailable.'),
                'POSTNL-0110'
            );
        }

        $this->lock();

        Mage::dispatchEvent('postnl_shipment_confirm_before', array('shipment' => $this));
        Mage::dispatchEvent('postnl_shipment_confirmandgeneratelabel_before', array('shipment' => $this));

        $parcelCount = $this->getParcelCount();
        if (!$parcelCount) {
            $parcelCount = $this->_calculateParcelCount();
        }

        /**
         * @var Mage_Core_Model_Date $dateModel
         */
        $dateModel = Mage::getModel('core/date');

        /**
         * Confirm and generate labels purely for the main shipment
         */
        if ($parcelCount < 2) {
            $labels = $this->_generateLabel(true);
            $this->addLabels($labels);

            $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
                 ->setConfirmedAt($dateModel->gmtTimestamp());

            $this->_saveLabels();

            Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));
            Mage::dispatchEvent('postnl_shipment_confirmandgeneratelabel_after', array('shipment' => $this));

            $this->unlock();
            return $this;
        }

        /**
         * Confirm and generate labels for each parcel in the shipment
         */
        for ($i = 0; $i < $parcelCount; $i++) {
            $labels = $this->_generateLabel(true, $i);
            $this->addLabels($labels);
        }

        $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
             ->setConfirmedAt($dateModel->gmtTimestamp());

        $this->_saveLabels();

        Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));
        Mage::dispatchEvent('postnl_shipment_confirmandgeneratelabel_after', array('shipment' => $this));

        $this->unlock();
        return $this;
    }

    /**
     * Requests a shipping status update for this shipment
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function updateShippingStatus()
    {
        if (!$this->canUpdateShippingStatus()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The updateShippingStatus action is currently unavailable.'),
                'POSTNL-0073'
            );
        }

        $this->lock();

        Mage::dispatchEvent('postnl_shipment_updateshippingstatus_before', array('shipment' => $this));

        /**
         * @var TIG_PostNL_Model_Core_Cif $cif
         */
        $cif = Mage::getModel('postnl_core/cif');
        $cif->setStoreId($this->getStoreId());
        $result = $cif->getShipmentStatus($this);

        $currentPhase = $result->Status->CurrentPhaseCode;

        if (!$currentPhase) {
            return $this;
        }

        Mage::dispatchEvent(
            'postnl_shipment_updateshippingphase_before',
            array('shipment' => $this, 'phase' => $currentPhase)
        );
        $this->setShippingPhase($currentPhase);
        Mage::dispatchEvent(
            'postnl_shipment_updateshippingphase_after',
            array('shipment' => $this, 'phase' => $currentPhase)
        );

        Mage::dispatchEvent('postnl_shipment_updateshippingstatus_after', array('shipment' => $this));

        $this->unlock();
        return $this;
    }

    /**
     * Update this shipment's status history
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function updateCompleteShippingStatus()
    {
        if (!$this->canUpdateCompleteShippingStatus()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The updateCompleteShippingStatus action is currently unavailable.'),
                'POSTNL-0074'
            );
        }

        $this->lock();

        Mage::dispatchEvent('postnl_shipment_updatecompleteshippingstatus_before', array('shipment' => $this));

        /**
         * @var TIG_PostNL_Model_Core_Cif $cif
         * @var StdClass $result
         */
        $cif = Mage::getModel('postnl_core/cif');
        $cif->setStoreId($this->getStoreId());
        $result = $cif->getCompleteShipmentStatus($this);

        /**
         * Update the shipment's shipping phase
         */
        $currentPhase = $result->Status->CurrentPhaseCode;
        Mage::dispatchEvent(
            'postnl_shipment_updateshippingphase_before',
            array('shipment' => $this, 'phase' => $currentPhase)
        );
        $this->setShippingPhase($currentPhase);
        Mage::dispatchEvent(
            'postnl_shipment_updateshippingphase_after',
            array('shipment' => $this, 'phase' => $currentPhase)
        );

        if (!isset($result->Events->CompleteStatusResponseEvent)) {
            $this->unlock();
            return $this;
        }

        /**
         * get the complete event history
         */
        $completeStatusHistory = $result->Events->CompleteStatusResponseEvent;
        $completeStatusHistory = $this->_sortStatusHistory($completeStatusHistory);

        /**
         * @var MAge_Core_Model_Date $dateModel
         */
        $dateModel = Mage::getModel('core/date');

        /**
         * Update the shipments status history
         */
        foreach ($completeStatusHistory as $status) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment_Status_History $statusHistory
             */
            $statusHistory = Mage::getModel('postnl_core/shipment_status_history');

            /**
             * Check if a status history item exists for the given code and shipment id.
             * If not, create a new one
             */
            if (!$statusHistory->statusHistoryIsNew($this->getId(), $status)) {
                continue;
            }

            $timestamp = $dateModel->gmtTimestamp($status->TimeStamp);
            $statusHistory->setParentId($this->getId())
                          ->setCode($status->Code)
                          ->setDescription($status->Description)
                          ->setLocationCode($status->LocationCode)
                          ->setDestinationLocationCode($status->DestinationLocationCode)
                          ->setRouteCode($status->RouteCode)
                          ->setRouteName($status->RouteName)
                          ->setTimestamp($timestamp)
                          ->save();
        }

        $this->setStatusHistoryUpdatedAt($dateModel->gmtTimestamp());

        Mage::dispatchEvent('postnl_shipment_updatecompleteshippingstatus_after', array('shipment' => $this));

        $this->unlock();

        return $this;
    }

    /*******************************************************************************************************************
     * TRACKING METHODS
     ******************************************************************************************************************/

    /**
     * Adds Magento tracking information to the order containing the previously retrieved barcode
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function addTrackingCodeToShipment()
    {
        if (!$this->canAddTrackingCode()) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The addTrackingCodeToShipment action is currently unavailable.'),
                'POSTNL-0180'
            );
        }

        $shipment = $this->getShipment();
        $barcode = $this->getMainBarcode();

        if (!$shipment || !$barcode) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to add tracking info: no barcode or shipment available.'),
                'POSTNL-0075'
            );
        }

        $carrierCode = self::POSTNL_CARRIER_CODE;
        $carrierTitle = Mage::getStoreConfig('carriers/' . $carrierCode . '/name', $shipment->getStoreId());

        $data = array(
            'carrier_code' => $carrierCode,
            'title'        => $carrierTitle,
            'number'       => $barcode,
        );

        /**
         * @var Mage_Sales_Model_Order_Shipment_Track $track
         */
        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
        $shipment->addTrack($track);

        /**
         * Save the Mage_Sales_Order_Shipment object and the TIG_PostNL_Model_Core_Shipment objects simultaneously.
         *
         * @var Mage_Core_Model_Resource_Transaction $transaction
         */
        $transaction = Mage::getModel('core/resource_transaction');
        $transaction->addObject($this)
                    ->addObject($shipment)
                    ->save();

        return $this;
    }

    /**
     * Send a track & trace email to the customer containing a link to the 'mijnpakket' environment where they
     * can track their shipment.
     *
     * @param boolean $ignoreAlreadySent Flag to ignore the 'already sent' check.
     * @param boolean $ignoreConfig      Flag to ignore the configuration settings related to track&trace e-mails.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function sendTrackAndTraceEmail($ignoreAlreadySent = false, $ignoreConfig = false)
    {
        $helper = Mage::helper('postnl');
        if (!$this->canSendTrackAndTraceEmail($ignoreAlreadySent, $ignoreConfig)) {
            throw new TIG_PostNL_Exception(
                $helper->__('The sendTrackAndTraceEmail action is currently unavailable.'),
                'POSTNL-0076'
            );
        }

        $storeId = $this->getStoreId();

        $template = Mage::getStoreConfig(self::XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE, $storeId);

        try {
            /**
             * @var Mage_Sales_Model_Order $order
             */
            $shippingAddress = $this->getShippingAddress();
            $shipment        = $this->getShipment();
            $order           = $this->getOrder();
            if (!$order || !$shipment || !$shippingAddress) {
                throw new TIG_PostNL_Exception(
                    $helper->__('Unable to send track & trace email due to missing shipment parameters.'),
                    'POSTNL-0200'
                );
            }

            $payment          = $order->getPayment();
            $paymentBlockHtml = '';
            if ($payment) {
                /** @noinspection PhpUndefinedMethodInspection */
                $paymentBlock = Mage::helper('payment')
                                    ->getInfoBlock($payment)
                                    ->setIsSecureMode(true);

                /** @noinspection PhpUndefinedMethodInspection */
                $paymentBlock->getMethod()
                             ->setStore($storeId);

                /**
                 * @var Mage_Payment_Block_Info $paymentBlock
                 */
                $paymentBlockHtml = $paymentBlock->toHtml();
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $templateVariables = array(
                'postnlshipment'   => $this,
                'barcode'          => $this->getMainBarcode(),
                'barcode_url'      => $this->getBarcodeUrl(false),
                'shipment'         => $shipment,
                'order'            => $order,
                'payment_html'     => $paymentBlockHtml,
                'customer'         => $order->getCustomer(),
                'quote'            => $order->getQuote(),
                'shipment_comment' => '', /** @todo add last shipment comment */
                'billing'          => $order->getBillingAddress(),
                'shipping'         => $order->getShippingAddress(),
                'pakje_gemak'      => $this->getPakjeGemakAddress(),
            );

            $templateVariables = new Varien_Object($templateVariables);
            Mage::dispatchEvent(
                'postnl_shipment_add_track_and_trace_email_vars',
                array(
                    'vars'            => $templateVariables,
                    'postnl_shipment' => $this,
                )
            );

            // Get the destination email addresses to send copies to
            $copy       = Mage::getStoreConfigFlag(self::XPATH_EMAIL_COPY, $storeId);
            $copyTo     = explode(',', Mage::getStoreConfig(self::XPATH_EMAIL_COPY_TO, $storeId));
            $copyMethod = Mage::getStoreConfig(self::XPATH_EMAIL_COPY_METHOD, $storeId);

            $mailer = Mage::getModel('core/email_template_mailer');
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $shippingAddress->getName());

            if ($copy && !empty($copyTo) && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }

            $mailer->addEmailInfo($emailInfo);

            if ($copy && !empty($copyTo) && $copyMethod == 'copy') {
                foreach ($copyTo as $email) {
                    $emailInfo = Mage::getModel('core/email_info');
                    $emailInfo->addTo($email);
                    $mailer->addEmailInfo($emailInfo);
                }
            }

            /**
             * Set all required parameters.
             */
            $mailer->setSender(Mage::getStoreConfig($order::XML_PATH_EMAIL_IDENTITY, $storeId))
                   ->setStoreId($storeId)
                   ->setTemplateId($template)
                   ->setTemplateParams($templateVariables->getData());

            Mage::dispatchEvent(
                'postnl_shipment_send_track_and_trace_email_before',
                array(
                    'postnl_shipment' => $this,
                    'mailer'          => $mailer,
                )
            );

            /**
             * Send the emails.
             */
            $mailer->send();

            Mage::dispatchEvent(
                'postnl_shipment_send_track_and_trace_email_after',
                array(
                    'postnl_shipment' => $this,
                )
            );

            /**
             * Add a comment to the order and shipment that the track & trace email has been sent.
             */
            $order->addStatusHistoryComment(
                       $helper->__(
                           'PostNL track & trace email has been sent for shipment #%s.',
                           $shipment->getIncrementId()
                       )
                   )
                   ->setIsCustomerNotified(1)
                   ->save();

            $shipment->addComment(
                         $helper->__('PostNL track & trace email has been sent.'),
                         true
                     )
                     ->save();
        } catch (Exception $e) {
            $helper->logException($e);
            throw new TIG_PostNL_Exception(
                $helper->__(
                    'Unable to send track and trace email for shipment #',
                    $this->getShipmentId()
                ),
                'POSTNL-0077'
            );
        }

        /**
         * Set the 'email sent' flag to true for this shipment.
         */
        if (!$this->getShipment()->getEmailSent()) {
            $this->getShipment()
                 ->setEmailSent(true)
                 ->save();
        }

        return $this;
    }

    /*******************************************************************************************************************
     * BARCODE PROCESSING METHODS
     ******************************************************************************************************************/

    /**
     * Add a barcode to this shipment's barcode collection
     *
     * @param string $barcode The barcode to add
     * @param int $barcodeNumber The number of this barcode
     *
     * @return $this
     */
    protected function _addBarcode($barcode, $barcodeNumber)
    {
        /**
         * @var TIG_PostNL_Model_Core_Shipment_Barcode $barcodeModel
         */
        $barcodeModel = Mage::getModel('postnl_core/shipment_barcode');
        $barcodeModel->setParentId($this->getId())
                     ->setBarcode($barcode)
                     ->setBarcodeNumber($barcodeNumber)
                     ->save();

        return $this;
    }

    /*******************************************************************************************************************
     * LABEL PROCESSING METHODS
     ******************************************************************************************************************/

    /**
     * Add labels to this shipment
     *
     * @param mixed $labels An array of labels or a single label object
     *
     * @return $this
     */
    public function addLabels($labels)
    {
        if (is_object($labels)) {
            /**
             * Add a single label
             */
            $this->_addLabel($labels);
            return $this;
        }

        /**
         * Add multiple labels
         */
        foreach ($labels as $label) {
            $this->_addLabel($label);
        }

        return $this;
    }

    /**
     * Add a label to this shipment
     *
     * @param stdClass $label
     *
     * @return $this
     */
    protected function _addLabel($label)
    {
        $labelType = $label->Labeltype;

        if ($this->_isCombiLabel()) {
            $labelType = 'Label-combi';
        }

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $postnlLabel
         */
        $postnlLabel = Mage::getModel('postnl_core/shipment_label');
        $postnlLabel->setParentId($this->getId())
                    ->setLabel(base64_encode($label->Content))
                    ->setLabelType($labelType);

        $labels = $this->getLabels();
        $labels[] = $postnlLabel;
        $this->setLabels($labels);

        $this->_addLabelToSave($postnlLabel);

        return $this;
    }

    /**
     * Store the label in an array to be saved later
     *
     * @param TIG_PostNL_Model_Core_Shipment_Label $label
     *
     * @return $this
     */
    protected function _addLabelToSave($label)
    {
        $labelsToSave = $this->getLabelsToSave();

        $labelsToSave[] = $label;

        $this->setLabelsToSave($labelsToSave);

        return $this;
    }

    /**
     * Save all newly added labels at once
     *
     * @return $this
     */
    protected function _saveLabels()
    {
        /**
         * @var Mage_Core_Model_Resource_Transaction $transactionSave
         */
        $transactionSave = Mage::getModel('core/resource_transaction');

        /**
         * Add all labels to the transaction
         */
        $labelsToSave = $this->getLabelsToSave();

        Mage::dispatchEvent(
            'postnl_shipment_savelabels_before',
            array('shipment' => $this, 'labels' => $labelsToSave)
        );

        foreach ($labelsToSave as $label) {
            $transactionSave->addObject($label);
        }

        /**
         * Save the transaction
         */
        $transactionSave->save();

        Mage::dispatchEvent(
            'postnl_shipment_savelabels_after',
            array('shipment' => $this, 'labels' => $labelsToSave)
        );

        return $this;
    }

    /**
     * Check if the returned label is a combi-label.
     *
     * @param TIG_PostNL_Model_Core_Shipment_label
     *
     * @return boolean
     */
    protected function _isCombiLabel()
    {
        if (!$this->isEuShipment()) {
            return false;
        }

        /**
         * All EU shipments will by default request a combi label. If no warnings were sent by CIF it means everything
         * went as expected and a combi-label was returned.
         */
        $warnings = Mage::registry('postnl_cif_warnings');
        if (!$warnings) {
            return true;
        }

        /**
         * Check each warning if the code matches the EPS combi label warning code
         */
        foreach ($warnings as $warning) {
            if (isset($warning['code']) && $warning['code'] === self::EPS_COMBI_LABEL_WARNING_CODE) {
                return false;
            }
        }

        return true;
    }

    /*******************************************************************************************************************
     * STATUS PROCESSING METHODS
     ******************************************************************************************************************/

    /**
     * Sort a status history array based on the time the status was assigned
     *
     * @param array $statusHistory
     *
     * @return array
     */
    protected function _sortStatusHistory($statusHistory)
    {
        /**
         * Add all status objects to a temporary array with the status's timestamp as the key
         */
        $sortedHistory = array();
        foreach ($statusHistory as $status) {
            $timestamp = $status->TimeStamp;
            $timestamp = strtotime($timestamp);

            $sortedHistory[$timestamp] = $status;
        }

        /**
         * Sort the array based on the timestamps
         */
        ksort($sortedHistory);

        /**
         * Return only the values (the statuses) of the array
         */
        return array_values($sortedHistory);
    }

    /*******************************************************************************************************************
     * BUSPAKJE METHODS
     ******************************************************************************************************************/

    /**
     * Calculate whether this shipment is a buspakje shipment.
     *
     * @return bool
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getIsBuspakje()
    {
        if (!$this->isDutchShipment()
            || $this->isPakketautomaatShipment()
            || $this->isPakjeGemakShipment()
            || $this->isCod()
        ) {
            return false;
        }

        if (!$this->getHelper()->canUseBuspakje()) {
            return false;
        }

        $shipmentItems = $this->getShipment()
                              ->getItemsCollection();

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        $orderItems = array();
        foreach ($shipmentItems as $item) {
            $orderItem = $item->getOrderItem();

            if ($orderItem->getChildrenItems()) {
                $orderItems = array_merge($orderItems, $orderItem->getChildrenItems());
                continue;
            }

            $orderItems[] = $orderItem;
        }

        if (!$this->getHelper()->fitsAsBuspakje($orderItems)) {
            return false;
        }

        return true;
    }

    /*******************************************************************************************************************
     * PRODUCT CODE METHODS
     ******************************************************************************************************************/

    /**
     * Gets the product code for this shipment. If specific options have been selected those will be used. Otherwise the
     * default options will be used from system/config
     *
     * @return int
     */
    protected function _getProductCode()
    {
        /**
         * Product options were set manually by the user.
         */
        if (Mage::registry('postnl_product_option')) {
            $productCode = Mage::registry('postnl_product_option');

            if (is_array($productCode)) {
                $productCode = $this->_extractProductCodeForType($productCode);
            }

            $this->_checkProductCodeAllowed($productCode);

            return $productCode;
        }

        /**
         * Use default options.
         */
        $productCode = $this->getDefaultProductCode();

        return $productCode;
    }

    /**
     * Extracts a chosen product code from an array of product codes indexed by shipment type.
     *
     * @param array $codes
     *
     * @return string
     */
    protected function _extractProductCodeForType($codes)
    {
        $shipmentType = $this->getShipmentType(false);

        /**
         * If this is a domestic shipment and the shipment has been marked as 'buspakje', update the shipment type. If
         * no buspakje field was entered or the field has a value of -1, automatically determine whether this shipment
         * is a buspakje shipment.
         */
        if ($shipmentType == self::SHIPMENT_TYPE_DOMESTIC
            && array_key_exists('is_buspakje', $codes)
            && $codes['is_buspakje'] == '1'
        ) {
            $isBuspakje = true;
        } elseif ($shipmentType == self::SHIPMENT_TYPE_DOMESTIC
            && (!array_key_exists('is_buspakje', $codes)
                || $codes['is_buspakje'] == '-1'
            )
        ) {
            $isBuspakje = $this->_getIsBuspakje();
        } else {
            $isBuspakje = false;
        }

        /**
         * If this is a buspakje shipment, change the shipment type accordingly.
         */
        $this->setIsBuspakje($isBuspakje);
        if ($isBuspakje) {
            $shipmentType = self::SHIPMENT_TYPE_BUSPAKJE;
            $this->setShipmentType($shipmentType);
        }

        /**
         * The merchant may choose to use the default product code for this shipment.
         */
        if (array_key_exists('use_default', $codes) && $codes['use_default'] == '1') {
            return $this->getDefaultProductCode();
        }

        /**
         * Get the selected product code for the current shipment's shipment type.
         */
        $shipmentType .= '_options';
        if (isset($codes[$shipmentType])) {
            return $codes[$shipmentType];
        } elseif (isset($codes['product_option'])) {
            return $codes['product_option'];
        }

        /**
         * If no code was found, use the default.
         */
        return $this->getDefaultProductCode();
    }

    /**
     * Checks if a given product code is allowed for the current shipments. Throws an exception if not.
     *
     * @param string $productCode
     *
     * @return boolean
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _checkProductCodeAllowed($productCode)
    {
        /**
         * @var TIG_PostNL_Helper_Cif $cifHelper
         */
        $cifHelper = $this->getHelper('cif');
        $allowedProductCodes = $this->getAllowedProductCodes();

        /**
         * Check if the product code is allowed.
         */
        if (!in_array($productCode, $allowedProductCodes)) {
            $options = Mage::getSingleton('postnl_core/system_config_source_allProductOptions')->getOptions();
            $productName = $cifHelper->__($options[$productCode]['label']);

            throw new TIG_PostNL_Exception(
                $cifHelper->__(
                    "Product option '%s' (%s) is not allowed for this shipment.", $productName, $productCode
                ),
                'POSTNL-0078'
            );
        }

        /**
         * Check if the product code is restricted to certain countries.
         */
        $allowedCountries = $this->_isCodeRestricted($productCode);
        if ($allowedCountries === false) {
            return true;
        }

        /**
         * Check if the destination country of this shipment is allowed.
         */
        $shippingAddress = $this->getShippingAddress();
        if (!$shippingAddress
            || !in_array($shippingAddress->getCountryId(), $allowedCountries)
        ) {
            $options = Mage::getSingleton('postnl_core/system_config_source_allProductOptions')->getOptions();
            $productName = $cifHelper->__($options[$productCode]['label']);

            throw new TIG_PostNL_Exception(
                $cifHelper->__(
                    "Product option '%s' (%s) is not allowed for this shipment.", $productName, $productCode
                ),
                'POSTNL-0078'
            );
        }

        return true;
    }

    /**
     * Checks if a given product code is only allowed for a specific country.
     *
     * @param $code
     *
     * @return boolean|array Either false if the code is not restricted, or otherwise an array of allowed country IDs
     */
    protected function _isCodeRestricted($code)
    {
        /**
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $helper = $this->getHelper('cif');
        $countryRestrictedCodes = $helper->getCountryRestrictedProductCodes();

        /**
         * Check if the supplied code is restricted
         */
        if (!array_key_exists($code, $countryRestrictedCodes)) {
            return false;
        }

        /**
         * Get the countries that are allowed
         */
        $allowedCountries = $countryRestrictedCodes[$code];
        return $allowedCountries;
    }

    /*******************************************************************************************************************
     * ADDITIONAL SHIPMENT OPTIONS
     ******************************************************************************************************************/

    /**
     * Public alias for _saveAdditionalShippingOptions()
     *
     * @return $this
     *
     * @see TIG_PostNL_Model_Core_Shipment::_saveAdditionalShippingOptions()
     */
    public function saveAdditionalShippingOptions()
    {
        return $this->_saveAdditionalShippingOptions();
    }

    /**
     * Stores additionally selected shipping options
     *
     * @return $this
     */
    protected function _saveAdditionalShippingOptions()
    {
        $additionalOptions = Mage::registry('postnl_additional_options');
        if (!$additionalOptions || !is_array($additionalOptions)) {
            return $this;
        }

        foreach($additionalOptions as $option => $value) {
            $this->setDataUsingMethod($option, $value);
        }

        Mage::dispatchEvent(
            'postnl_shipment_saveadditionaloptions_after',
            array(
                'shipment' => $this,
                'options'  => $additionalOptions
            )
        );

        Mage::unRegister('postnl_additional_options');

        return $this;
    }

    /**
     * Get the number of parcels in this shipment
     *
     * @return int
     */
    protected function _calculateParcelCount()
    {
        /**
         * Only Dutch shipments that are not COD support multi-colli shipments
         */
        if (!$this->isDutchShipment() || $this->isCod()) {
            return 1;
        }

        /**
         * get this shipment's total weight
         */
        $weight = $this->getTotalWeight(true);

        /**
         * get the weight per parcel.
         *
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $helper = $this->getHelper();
        $weightPerParcel = Mage::getStoreConfig(self::XPATH_WEIGHT_PER_PARCEL, $this->getStoreId());
        $weightPerParcel = $helper->standardizeWeight($weightPerParcel, $this->getStoreId());

        /**
         * calculate the number of parcels needed to ship the total weight of this shipment
         */
        $parcelCount = ceil($weight / $weightPerParcel);

        return $parcelCount;
    }

    /*******************************************************************************************************************
     * CONVERSION METHODS
     ******************************************************************************************************************/

    /**
     * Converts this shipment to a buspakje shipment.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function convertToBuspakje()
    {
        if (!$this->canConvertShipmentToBuspakje()) {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('The convertToBuspakje action is currently unavailable.'),
                'POSTNL-0191'
            );
        }

        $this->_prepareForConversion()
             ->setShipmentType(self::SHIPMENT_TYPE_BUSPAKJE)
             ->setProductCode($this->_getProductCode())
             ->setIsBuspakje(true);

        return $this;
    }

    /**
     * Converts this shipment to a package shipment.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function convertToPackage()
    {
        if (!$this->canConvertShipmentToPackage()) {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('The convertToPackage action is currently unavailable.'),
                'POSTNL-0192'
            );
        }

        $this->_prepareForConversion()
             ->setShipmentType(self::SHIPMENT_TYPE_DOMESTIC)
             ->setProductCode($this->_getProductCode())
             ->setIsBuspakje(false);

        return $this;
    }

    /**
     * Prepares this shipment for conversion to a different product code or shipment type.
     *
     * @return $this
     */
    protected function _prepareForConversion()
    {
        $this->deleteBarcodes()
             ->deleteLabels()
             ->deleteShipmentTracks()
             ->deleteStatusHistory()
             ->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED);

        return $this;
    }

    /**
     * Changes the current shipment's product code.
     *
     * @param string $productCode
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function changeProductCode($productCode)
    {
        if (!$this->canChangeProductCode()) {
            throw new TIG_PostNL_Exception(
                $this->getHelper()->__('The changeProductCode action is currently unavailable.'),
                'POSTNL-0193'
            );
        }

        $this->_checkProductCodeAllowed($productCode);

        $this->setProductCode($productCode);

        return $this;
    }

    /*******************************************************************************************************************
     * RESET AND DELETE METHODS
     ******************************************************************************************************************/

    /**
     * Resets this shipment to a pre-confirmed state
     *
     * @param boolean $deleteLabels
     * @param boolean $deleteTracks
     *
     * @return $this
     */
    public function resetConfirmation($deleteLabels = true, $deleteTracks = true)
    {
        if (!$this->hasCustomBarcode()) {
            $confirmStatus = self::CONFIRM_STATUS_UNCONFIRMED;
        } else {
            $confirmStatus = self::CONFIRM_STATUS_BUSPAKJE;
        }

        $this->setConfirmStatus($confirmStatus) //set status to unconfirmed
             ->setShippingPhase(false) //delete current shipping phase
             ->setConfirmedAt(false); //delete 'confirmed at' date

        $this->deleteBarcodes() //delete all associated barcodes
             ->deleteStatusHistory(); //delete all associated status history items


        if ($deleteLabels) {
            $this->setlabelsPrinted(false) //labels have not been printed
                 ->deleteLabels(); //delete all associated labels
        }

        if ($deleteTracks) {
            $this->deleteShipmentTracks() //delete ale associated tracks
                 ->setTrackAndTraceEmailSent(false); //make sure that a new T&T e-mail is sent
        }

        return $this;
    }

    /**
     * Delete all tracks for this shipment.
     *
     * @return $this
     */
    public function deleteShipmentTracks()
    {
        $shipment = $this->getShipment();

        if (!$shipment) {
            return $this;
        }

        /**
         * @var Mage_Sales_Model_Order_Shipment_Track $track
         */
        $tracksCollection = $shipment->getTracksCollection();
        foreach($tracksCollection as $track) {
            $track->delete();
        }

        return $this;
    }

    /**
     * Removes all labels associated with this shipment
     *
     * @return $this
     */
    public function deleteLabels()
    {
        $labelCollection = $this->getLabelCollection();

        $labels = $labelCollection->getItems();

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Label $label
         */
        foreach ($labels as $label) {
            $label->delete();
        }

        return $this;
    }

    /**
     * Removes all barcodes associated with this shipment
     *
     * @return $this
     */
    public function deleteBarcodes()
    {
        $barcodes = $this->getBarcodes(true);

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Barcode $barcode
         */
        foreach ($barcodes as $barcode) {
            $barcode->delete();
        }

        $this->setMainBarcode(false);

        return $this;
    }

    /**
     * Deletes all status history items associated with this shipment.
     *
     * @return $this
     */
    public function deleteStatusHistory()
    {
        $statusHistoryCollection = Mage::getResourceModel('postnl_core/shipment_status_history_collection');
        $statusHistoryCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));

        /**
         * @var TIG_PostNL_Model_Core_Shipment_Status_History $status
         */
        foreach ($statusHistoryCollection as $status) {
            $status->delete();
        }

        return $this;
    }

    /*******************************************************************************************************************
     * LOADING METHODS
     ******************************************************************************************************************/

    /**
     * Loads this shipment by a Magento shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return $this
     */
    public function loadByShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $shipmentId = $shipment->getId();

        if (!$shipmentId) {
            return $this;
        }

        $this->load($shipmentId, 'shipment_id');
        return $this;
    }

    /*******************************************************************************************************************
     * SAVING METHODS
     ******************************************************************************************************************/

    /**
     * Updates the shipment's attributes before saving this shipment
     *
     * @return Mage_Core_Model_Abstract::_beforeSave
     */
    protected function _beforeSave()
    {
        /**
         * @var Mage_Core_Model_Date $dateModel
         */
        $dateModel = Mage::getModel('core/date');
        $currentTimestamp = $dateModel->gmtTimestamp();

        /**
         * Store any shipment options that have been saved in the registry.
         */
        if (Mage::registry('postnl_additional_options')) {
            $this->_saveAdditionalShippingOptions();
        }

        /**
         * Set confirm status.
         */
        if ($this->getConfirmStatus() === null && $this->hasCustomBarcode()) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_BUSPAKJE);
        } elseif ($this->getConfirmStatus() === null) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED);
        }

        /**
         * Set confirmed at.
         */
        if ($this->getConfirmedStatus() == self::CONFIRM_STATUS_CONFIRMED
            && $this->getConfirmedAt() === null
        ) {
            $this->setConfirmedAt($currentTimestamp);
        }

        /**
         * Set whether labels have printed or not.
         */
        if ($this->getlabelsPrinted() == 0 && $this->hasLabels()) {
            $this->setLabelsPrinted(1);
        }

        /**
         * Set a product code.
         */
        if (!$this->getProductCode() || Mage::registry('postnl_product_option') !== null) {
            $productCode = $this->_getProductCode();
            $this->setProductCode($productCode);

            /**
             * If this is an extra cover shipment and no extra cover amount has been set, set the default of 500 EUR.
             */
            if ($this->isExtraCover() && !$this->hasExtraCoverAmount()) {
                $this->setExtraCoverAmount();
            }
        }

        /**
         * Set the parcel count.
         */
        if (!$this->getParcelCount()) {
            $parcelCount = $this->_calculateParcelCount();
            $this->setParcelCount($parcelCount);
        }

        /**
         * Set the confirm date.
         */
        if (!$this->getConfirmDate()) {
            $this->setConfirmDate();
        }

        /**
         * If no order ID has been set, use the getOrderId() method. This will automatically set the order ID.
         */
        if (!$this->_getData('order_id')) {
            $this->getOrderId();
        }

        return parent::_beforeSave();
    }

    /**
     * Check if saving this shipment is allowed before actually saving the object.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function save()
    {
        if ($this->getPreventSaving()) {
            return $this;
        }

        return parent::save();
    }
}