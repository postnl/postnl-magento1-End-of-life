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
 * @copyright Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
 *  - postnl_shipment_savelabels_before
 *  - postnl_shipment_savelabels_after
 *  - postnl_shipment_saveadditionaloptions_after
 *
 * @method bool                           getIsDutchShipment()
 * @method bool                           getIsEuShipment()
 * @method bool                           getIsGlobalShipment()
 * @method int                            getParcelCount()
 * @method string|null                    getConfirmedStatus
 * @method string                         getStatusHistoryUpdatedAt()
 * @method string                         getConfirmStatus()
 * @method string                         getMainBarcode()
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
 *
 * @method TIG_PostNL_Model_Core_Shipment setLabelsPrinted(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setTreatAsAbandoned(int $value)
 * @method TIG_PostNL_Model_Core_Shipment setShippingPhase(int $value)
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
 */
class TIG_PostNL_Model_Core_Shipment extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_shipment';

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

    /**
     * Possible shipping phases.
     */
    const SHIPPING_PHASE_COLLECTION     = '1';
    const SHIPPING_PHASE_SORTING        = '2';
    const SHIPPING_PHASE_DISTRIBUTION   = '3';
    const SHIPPING_PHASE_DELIVERED      = '4';
    const SHIPPING_PHASE_NOT_APPLICABLE = '99';

    /**
     * Xpaths to default product options settings.
     */
    const XPATH_DEFAULT_STANDARD_PRODUCT_OPTION       = 'postnl/cif_product_options/default_product_option';
    const XPATH_DEFAULT_EVENING_PRODUCT_OPTION        = 'postnl/cif_product_options/default_evening_product_option';
    const XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION     = 'postnl/cif_product_options/default_pakjegemak_product_option';
    const XPATH_DEFAULT_PGE_PRODUCT_OPTION            = 'postnl/cif_product_options/default_pge_product_option';
    const XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION = 'postnl/cif_product_options/default_pakketautomaat_product_option';
    const XPATH_DEFAULT_EU_PRODUCT_OPTION             = 'postnl/cif_product_options/default_eu_product_option';
    const XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION         = 'postnl/cif_product_options/default_global_product_option';
    const XPATH_USE_ALTERNATIVE_DEFAULT               = 'postnl/cif_product_options/use_alternative_default';
    const XPATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT        = 'postnl/cif_product_options/alternative_default_max_amount';
    const XPATH_ALTERNATIVE_DEFAULT_OPTION            = 'postnl/cif_product_options/alternative_default_option';

    /**
     * Xpath to weight per parcel config setting.
     */
    const XPATH_WEIGHT_PER_PARCEL = 'postnl/cif_labels_and_confirming/weight_per_parcel';

    /**
     * Xpath to setting that determines whether or not to send track and trace emails.
     */
    const XPATH_SEND_TRACK_AND_TRACE_EMAIL = 'postnl/cif_labels_and_confirming/send_track_and_trace_email';

    /**
     * Xpath to track and trace email template setting.
     */
    const XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE = 'postnl/cif_labels_and_confirming/track_and_trace_email_template';

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
     * Retrieves a Mage_Sales_Model_Order_Shipment entity linked to the postnl shipment.
     *
     * @return Mage_Sales_Model_Order_Shipment | null
     */
    public function getShipment()
    {
        if ($this->hasShipment()) {
            return $this->_getData('shipment');
        }

        $shipmentId = $this->getShipmentId();
        if (!$shipmentId) {
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
     * Retrieves the linked Shipment's shipping address
     *
     * @return Mage_Sales_Model_Order_Address|null
     */
    public function getShippingAddress()
    {
        if ($this->hasShippingAddress()) {
            return $this->_getData('shipping_address');
        }

        $shipmentId = $this->getShipmentId();
        if (!$shipmentId && !$this->getShipment()) {
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

        $shipmentId = $this->getShipmentId();
        if (!$shipmentId && !$this->getShipment()) {
            return null;
        }

        /**
         * @var Mage_Sales_Model_Order_Address $address
         */
        $addresses = $this->getShipment()->getOrder()->getAddressesCollection();
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

        if ($this->getShipment()) {
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
     * @return int
     */
    public function getOrderId()
    {
        if ($this->hasOrderId()) {
            return $this->_getData('order_id');
        }

        $shipment = $this->getShipment();
        if (!$shipment || !$shipment->getOrderId()) {
            return null;
        }

        $orderId = $shipment->getOrderId();

        $this->setOrderId($orderId);
        return $orderId;
    }

    /**
     * Gets a PostNL helper object
     *
     * @param string $type
     *
     * @return TIG_PostNL_Helper_Data|TIG_PostNL_Helper_Cif|TIG_PostNL_Helper_Carrier|TIG_PostNL_Helper_Checkout
     *         |TIG_PostNL_Helper_AddressValidation|TIG_PostNL_Helper_DeliveryOptions|TIG_PostNL_Helper_Parcelware
     *         |TIG_PostNL_Helper_Webservices|TIG_PostNL_Helper_Mijnpakket
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
     * gets all shipping labels associated with this shipment
     *
     * @return array Array of TIG_PostNL_Model_Core_Shipment_Label objects
     */
    public function getLabels()
    {
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
        $helper = $this->getHelper('cif');
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
        $shipment = $this->getShipment();
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
     * Gets the url for this shipment's main barcode
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

        $locale = Mage::getStoreConfig('general/locale/code', $this->getStoreId());
        $lang = substr($locale, 0, 2);

        $url = $helper->getBarcodeUrl($barcode, $this->getShippingAddress(), $lang, $forceNl);

        $this->setBarcodeUrl($url);
        return $url;
    }

    /**
     * Gets the shipment's shipment type for intrnational shipments. If no shipment type is defined, use the default
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
     * Gets the default product code for this shipment from the module's configuration
     *
     * @return string
     */
    public function getDefaultProductCode()
    {
        $storeId = $this->getStoreId();

        $xpath = false;
        if ($this->isPgeShipment()) {
            /**
             * PakjeGemak Express default option
             */
            $xpath = self::XPATH_DEFAULT_PGE_PRODUCT_OPTION;
        } elseif ($this->isAvondshipment()) {
            /**
             * Evening delivery default option
             */
            $xpath = self::XPATH_DEFAULT_EVENING_PRODUCT_OPTION;
        } elseif ($this->isPakjeGemakShipment()) {
            /**
             * PakjeGemak default option
             */
            $xpath = self::XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION;
        } elseif ($this->isPakketautomaatShipment()) {
            /**
             * PakjeGemak default option
             */
            $xpath = self::XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION;
        } elseif ($this->isEuShipment()) {
            /**
             * EU default option
             */
            $xpath = self::XPATH_DEFAULT_EU_PRODUCT_OPTION;
        } elseif ($this->isGlobalShipment()) {
            /**
             * Global default option
             */
            $xpath = self::XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION;
        }

        /**
         * If the shipment is not EU or global, it's dutch (AKA a 'standard' shipment)
         */

        /**
         * Dutch shipments may use an alternative default option when the shipment's base grandtotal exceeds a specified
         * amount.
         */
        $useAlternativeDefault = Mage::getStoreConfig(self::XPATH_USE_ALTERNATIVE_DEFAULT, $storeId);
        if (!$xpath && $useAlternativeDefault) {
            /**
             * Alternative default option usage is enabled
             */
            $maxShipmentAmount = Mage::getStoreConfig(self::XPATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT, $storeId);
            if ($this->getShipmentBaseGrandTotal() > $maxShipmentAmount) {
                /**
                 * The shipment's base grand total exceeds the specified amount: use the alternative default
                 */
                $xpath = self::XPATH_ALTERNATIVE_DEFAULT_OPTION;
            }
        }

        if (!$xpath) {
            $xpath = self::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION;
        }

        /**
         * standard default option
         */
        $productCode = Mage::getStoreConfig($xpath, $storeId);
        $this->_checkProductCodeAllowed($productCode);

        return $productCode;
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
     * @return boolean|TIG_PostNL_Model_Core_Order
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
     *
     * @throws TIG_PostNL_Exception
     */
    public function getAllowedProductCodes()
    {
        $cifHelper = $this->getHelper('cif');

        /**
         * Please note the order of these checks as PakjeGemak, PakjeGemak Express, avond and pakketautomaat shipment
         * would all also be considered Dutch shipments.
         */

        if ($this->isPgeShipment()) {
            $allowedProductCodes = $cifHelper->getPgeProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isAvondShipment()) {
            $allowedProductCodes = $cifHelper->getAvondProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isPakjeGemakShipment()) {
            $allowedProductCodes = $cifHelper->getPakjeGemakProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isPakketautomaatShipment()) {
            $allowedProductCodes = $cifHelper->getPakketautomaatProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isDutchShipment()) {
            $allowedProductCodes = $cifHelper->getStandardProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isEuShipment()) {
            $allowedProductCodes = $cifHelper->getEuProductCodes();
            return $allowedProductCodes;
        }

        if ($this->isGlobalShipment() && $cifHelper->isGlobalAllowed()) {
            $allowedProductCodes = $cifHelper->getGlobalProductCodes();
            return $allowedProductCodes;
        }

        /**
         * If no matches were found, return an empty array.
         */
        return array();
    }

    /**
     * Gets the delivery date for this shipment.
     *
     * @return null|string
     */
    public function getDeliveryDate()
    {
        if ($this->hasDeliveryDate()) {
            return $this->_getData('delivery_date');
        }

        /**
         * Try to get the delivery date fr a PostNL order.
         */
        $postnlOrder = $this->getPostnlOrder();
        if ($postnlOrder && $postnlOrder->getDeliveryDate()) {
            $deliveryDate = $postnlOrder->getDeliveryDate();

            $this->setDeliveryDate($deliveryDate);
            return $deliveryDate;
        }

        /**
         * If no delivery date is available, return null.
         */
        return null;
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
     * @param int $amount
     *
     * @return boolean|TIG_PostNL_Model_Core_Shipment
     */
    public function setExtraCoverAmount($amount)
    {
        /**
         * Check if extra cover is allowed for this shipment
         */
        $productCode = $this->getProductCode();
        $extraCoverProductCodes = $this->getExtraCoverProductCodes();
        if (!in_array($productCode, $extraCoverProductCodes)) {
            return false;
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
        if ($postnlOrder && $postnlOrder->getConfirmDate()) {
            $confirmDate = strtotime($postnlOrder->getConfirmDate());

            $this->setData('confirm_date', $confirmDate);
            return $this;
        }

        /**
         * Get the requested delivery date for this shipment.
         */
        $deliveryDate = $this->getDeliveryDate();

        /**
         * If no delivery date is available, set the confirm date to today.
         */
        if (!$deliveryDate) {
            $confirmDate = Mage::getModel('core/date')->gmtTimestamp();

            $this->setData('confirm_date', $confirmDate);
            return $this;
        }

        /**
         * Calculate the confirm based on the delivery date.
         */
        $deliveryTimeStamp = strtotime($deliveryDate);
        $confirmDate = strtotime('-1 day', $deliveryTimeStamp);

        $this->setData('confirm_date', $confirmDate);
        return $this;
    }

    /*******************************************************************************************************************
     * HAS- METHODS
     ******************************************************************************************************************/

    /**
     * Check if the shipment has any associated labels
     *
     * @return boolean
     */
    public function hasLabels()
    {
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

        $shippingDestination = $this->getShippingAddress()->getCountryId();

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

        $shippingDestination = $this->getShippingAddress()->getCountryId();

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
     * Check if the currrent shipment is a PakjeGemak shipment.
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
     * Checks if this shipment is a COD shipment
     *
     * @return boolean
     *
     * @todo implement this method
     */
    public function isCod()
    {
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
     * Checks if the current entity may generate a barcode.
     *
     * @return boolean
     */
    public function canGenerateBarcode()
    {
        if (!$this->getShipmentId() && !$this->getShipment()) {
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

        if (!$this->getShipmentId() && !$this->getShipment()) {
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

        return true;
    }

    /**
     * Checks if the current shipment is eligible for a shipping status update.
     * Unconfirmed shipments, shipments whose labels are not yet printed or shipments that are already delivered are
     * inelligible.
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

        return true;
    }

    /**
     * Checks if the current shipment is eligible for a complete shipping status update.
     * For now the same conditions apply as a regular status update. This may change in a future update of the
     * extension.
     *
     * @return boolean
     *
     * @see TIG_PostNL_Model_Core_Shipment::canUpdateShippingStatus()
     */
    public function canUpdateCompleteShippingStatus()
    {
        return $this->canUpdateShippingStatus();
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

        if ($ignoreAlreadySent !== true && $this->getTrackAndTraceEmailSent()) {
            return false;
        }

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
        if ($shippingPhase == self::SHIPPING_PHASE_COLLECTION
            || $shippingPhase == self::SHIPPING_PHASE_DELIVERED
            || $shippingPhase == self::SHIPPING_PHASE_DISTRIBUTION
            || $shippingPhase == self::SHIPPING_PHASE_SORTING
        ) {
            return false;
        }

        return true;
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
         * Generate and save the main barcode
         */
        $mainBarcode = $this->_generateBarcode();
        $this->setMainBarcode($mainBarcode);

        $parcelCount = $this->getParcelCount();
        if (!$parcelCount) {
            $parcelCount = $this->_calculateParcelCount();
        }

        /**
         * If this shipment consists of a single parcel we only need the main barcode
         */
        if ($parcelCount < 2) {
            Mage::dispatchEvent('postnl_shipment_generatebarcode_after', array('shipment' => $this));
            $this->unlock();

            return $this;
        }

        /**
         * Generate a barcode for each parcel and save it
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

        $parcelCount = $this->getparcelCount();
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

        $parcelCount = $this->getparcelCount();
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
         * onfirm each parcel in the shipment seperately
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
         * check the first shipment's barcode to see if it matches the main bartcode.
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

        $parcelCount = $this->getparcelCount();
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

        $this->setShippingPhase($currentPhase);

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
        $this->setShippingPhase($currentPhase);

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
        if (!$this->canSendTrackAndTraceEmail($ignoreAlreadySent, $ignoreConfig)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The sendTrackAndTraceEmail action is currently unavailable.'),
                'POSTNL-0076'
            );
        }

        $storeId = $this->getStoreId();

        $template = Mage::getStoreConfig(self::XPATH_TRACK_AND_TRACE_EMAIL_TEMPLATE, $storeId);

        /**
         * @var Mage_Core_Model_Email_Template $mailTemplate
         */
        $mailTemplate = Mage::getModel('core/email_template');

        $shippingAddress = $this->getShippingAddress();
        $recipient = array(
            'email' => $this->getShipment()->getOrder()->getCustomerEmail(),
            'name'  => $shippingAddress->getName(),
        );

        $mailTemplate->setDesignConfig(
            array(
                'area'  => 'frontend',
                'store' => $storeId
            )
        );

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $shipment = $this->getShipment();
        $order = $shipment->getOrder();
        /** @noinspection PhpUndefinedMethodInspection */
        $templateVariables = array(
            'postnlshipment' => $this,
            'barcode'        => $this->getMainBarcode(),
            'barcode_url'    => $this->getBarcodeUrl(false),
            'shipment'       => $shipment,
            'order'          => $order,
            'customer'       => $order->getCustomer(),
            'quote'          => $order->getQuote(),
        );

        /**
         * @var $orderModel Mage_Sales_Model_Order
         */
        /** @noinspection PhpParamsInspection */
        $orderModel = Mage::getConfig()->getModelClassName('sales/order');
        $success = $mailTemplate->sendTransactional(
            $template,
            Mage::getStoreConfig($orderModel::XML_PATH_EMAIL_IDENTITY, $storeId),
            $recipient['email'],
            $recipient['name'],
            $templateVariables
        );

        if ($success === false) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'Unable to send track and trace email for shipment #',
                    $this->getShipmentId()
                ),
                'POSTNL-0077'
            );
        }

        /**
         * Set the 'email sent' flag to true for this shipment.
         */
        $this->getShipment()
             ->setEmailSent(true)
             ->save();

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
         * Return only the values (the statusses) of the array
         */
        return array_values($sortedHistory);
    }

    /*******************************************************************************************************************
     * PRODUCT CODE METHODS
     ******************************************************************************************************************/

    /**
     * Gets the product code for this shipment. If specific options have been selected
     * those will be used. Otherwise the default options will be used from system/config
     *
     * @return int
     */
    protected function _getProductCode()
    {
        /**
         * Product options were set manually by the user
         */
        if (Mage::registry('postnl_product_option')) {
            $productCode = Mage::registry('postnl_product_option');
            $this->_checkProductCodeAllowed($productCode);

            return $productCode;
        }

        /**
         * Use default options
         */
        $productCode = $this->getDefaultProductCode();

        return $productCode;
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
         * Check if the product code is allowed
         */
        if (!in_array($productCode, $allowedProductCodes)) {
            throw new TIG_PostNL_Exception(
                $cifHelper->__('Product code %s is not allowed for this shipment.', $productCode),
                'POSTNL-0078'
            );
        }

        /**
         * Check if the product code is restricted to certain countries
         */
        $allowedCountries = $this->_isCodeRestricted($productCode);
        if ($allowedCountries === false) {
            return true;
        }

        /**
         * Check if the destination country of this shipment is allowed
         */
        $destination = $this->getShippingAddress()->getCountryId();
        if (!in_array($destination, $allowedCountries)) {
            throw new TIG_PostNL_Exception(
                $cifHelper->__('Product code %s is not allowed for this shipment.', $productCode),
                'POSTNL-0078'
            );
        }

        return true;
    }

    /**
     * Checks if a given product code is only allowed for a specific country
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
         * Only NL shipments support multi-colli shipments
         */
        if (!$this->isDutchShipment()) {
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
        $helper = $this->getHelper('cif');
        $weightPerParcel = Mage::getStoreConfig(self::XPATH_WEIGHT_PER_PARCEL, $this->getStoreId());
        $weightPerParcel = $helper->standardizeWeight($weightPerParcel, $this->getStoreId());

        /**
         * calculate the number of parcels needed to ship the total weight of this shipment
         */
        $parcelCount = ceil($weight / $weightPerParcel);

        return $parcelCount;
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
        $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED) //set status to unconfirmed
             ->setShippingPhase(false) //delete current shipping phase
             ->setConfirmedAt(false) //delete 'confirmed at' date
             ->deleteBarcodes() //delete all associated barcodes
             ->deleteStatusHistory(); //delete all associated status history items


        if ($deleteLabels) {
            $this->setlabelsPrinted(false) //labels have not been printed
                 ->deleteLabels(); //delete all associated labels
        }

        if ($deleteTracks) {
            $this->deleteShipmentTracks() //delete ale addociated tracks
                 ->setTrackAndTraceEmailSent(false); //make sure that a new T&T e-mail is sent
        }

        return $this;
    }

    /**
     * Delete alle tracks for this shipment.
     *
     * @return $this
     */
    public function deleteShipmentTracks()
    {
        $shipment = $this->getShipment();

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
     * Deletes all status history items associated with this shipment
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
     * BEFORE- AND AFTERSAVE METHODS
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
        if ($this->getConfirmStatus() === null) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED);
        }

        /**
         * Set confrirmed at.
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
         * If this shipment is new, set it's created at date to the current timestamp.
         */
        if (!$this->getId()) {
            $this->setCreatedAt($currentTimestamp);
        }

        /**
         * Always update the updated at timestamp to the current timestamp.
         */
        $this->setUpdatedAt($currentTimestamp);

        return parent::_beforeSave();
    }
}