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
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
 
/**
 * PostNL Shipment base class. Contains majority of PostNL shipping functionality
 */
class TIG_PostNL_Model_Core_Shipment extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'postnl_shipment';
    
    /**
     * Carrier code used by postnl
     */
    const POSTNL_CARRIER_CODE = 'postnl';
    
    /**
     * Possible confirm statusses
     */
    const CONFIRM_STATUS_CONFIRMED       = 'confirmed';
    const CONFIRM_STATUS_UNCONFIRMED     = 'unconfirmed';
    const CONFIRM_STATUS_CONFIRM_EXPIRED = 'confirm_expired';
    
    /**
     * Possible shipping phases
     */
    const SHIPPING_PHASE_COLLECTION     = '1';
    const SHIPPING_PHASE_SORTING        = '2';
    const SHIPPING_PHASE_DISTRIBUTION   = '3';
    const SHIPPING_PHASE_DELIVERED      = '4';
    const SHIPPING_PHASE_NOT_APPLICABLE = '99';
    
    /**
     * XML paths to default product options settings
     */
    const XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTION   = 'postnl/cif_product_options/default_product_option';
    const XML_PATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION = 'postnl/cif_product_options/default_pakjegemak_product_option';
    const XML_PATH_DEFAULT_EU_PRODUCT_OPTION         = 'postnl/cif_product_options/default_eu_product_option';
    const XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTION     = 'postnl/cif_product_options/default_global_product_option';
    const XML_PATH_USE_ALTERNATIVE_DEFAULT           = 'postnl/cif_product_options/use_alternative_default';
    const XML_PATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT    = 'postnl/cif_product_options/alternative_default_max_amount';
    const XML_PATH_ALTERNATIVE_DEFAULT_OPTION        = 'postnl/cif_product_options/alternative_default_option';
    
    /**
     * XML path to weight per parcel config setting
     */
    const XML_PATH_WEIGHT_PER_PARCEL = 'postnl/cif_labels_and_confirming/weight_per_parcel'; 
    
    /**
     * XML path to setting that determines whether or not to send track and trace emails
     */
    const XML_PATH_SEND_TRACK_AND_TRACE_EMAIL = 'postnl/cif_labels_and_confirming/send_track_and_trace_email';
    
    /**
     * XML path to track and trace email template setting
     */
    const XML_PATH_TRACK_AND_TRACE_EMAIL_TEMPLATE = 'postnl/cif_labels_and_confirming/track_and_trace_email_template';
    
    /**
     * XML path to maximum allowed parcel count settings
     */
    const XML_PATH_MAX_PARCEL_COUNT = 'postnl/advanced/max_parcel_count';
    
    /**
     * CIF warning code returned when an EPS combi label is not available
     */
    const EPS_COMBI_LABEL_WARNING_CODE = 'LIRS_0';
    
    /**
     * Newly added 'pakje_gemak' address type
     */
    const ADDRESS_TYPE_PAKJEGEMAK = 'pakje_gemak';
    
    /**
     * Array of product codes that have extra cover
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
     * Contains an instance of TIG_PostNL_Model_Core_Shipment_Process which locks a shipment and prevents it from being modified
     * 
     * @var void | TIG_PostNL_Model_Core_Shipment_Process
     */
    protected $_process;
    
    /**
     * Initialize the shipment
     */
    public function _construct()
    {
        $this->_init('postnl_core/shipment');
    }
    
    /****************************************************************************************************************************
     * GETTER AND SETTER METHODS
     ***************************************************************************************************************************/
    
    /**
     * Retrieves a Mage_Sales_Model_Order_Shipment entity linked to the postnl shipment.
     * 
     * @return Mage_Sales_Model_Order_Shipment | null
     */
    public function getShipment()
    {
        if ($this->getData('shipment')) {
            return $this->getData('shipment');
        }
        
        $shipmentId = $this->getShipmentId();
        if (!$shipmentId) {
            return null;
        }
        
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        
        $this->setShipment($shipment);
        return $shipment;
    }
    
    /**
     * Retrieves the linked Shipment's shipping address
     * 
     * @return Mage_Sales_Model_Order_Address | null
     */
    public function getShippingAddress()
    {
        if ($this->getData('shipping_address')) {
            return $this->getData('shipping_address');
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
     * @return boolean | Mage_Sales_Model_Order_Address
     */
    public function getPakjeGemakAddress()
    {
        if ($this->getData('pakje_gemak_address')) {
            return $this->getData('pakje_gemak_address');
        }
        
        $shipmentId = $this->getShipmentId();
        if (!$shipmentId && !$this->getShipment()) {
            return null;
        }
        
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
        if ($this->getData('store_id')) {
            return $this->getData('store_id');
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
        if ($this->getData('order_id')) {
            return $this->getData('order_id');
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
     * @return TIG_PostNL_Helper_Data
     */
    public function getHelper($type = 'data')
    {
        if ($this->getData('helper_' . $type)) {
            return $this->getData('helper_' . $type);
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
            $process = Mage::getModel('postnl_core/shipment_process')
                           ->setId($this->getId());
            $this->setProcess($process);
        }

        return $process;
    }
    
    /**
     * Sets the process used for locking and unlocking this shipment
     * 
     * @param TIG_PostNL_Model_Core_Shipment_Process
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function setProcess(TIG_PostNL_Model_Core_Shipment_Process $process)
    {
        $this->_process = $process;
        
        return $this;
    }
    
    /**
     * Get an array of labels that have to be saved together
     * 
     * @return array
     */
    public function getlabelsToSave()
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
        if ($this->getData('product_code')) {
            return $this->getData('product_code');
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
         * Delete the labels so the module will generate new ones.
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
     * @return int | float
     */
    public function getExtraCoverAmount()
    {
        if ($this->hasData('extra_cover_amount')) {
            return $this->getData('extra_cover_amount');
        }
        
        return 0;
    }
    
    /**
     * Calculates the total weight of this shipment
     * 
     * @param boolean $standardize Whether or not to convert the weight to kg
     * @param boolean $toGrams whether or not to convert the standardized weight to g
     * 
     * @return float | int
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
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty());
        }
        
        if ($standardize !== true) {
            return $weight;
        }
        
        /**
         * standardize the weight to kg or g
         */
        $weight = $this->getHelper('cif')->standardizeWeight(
            $weight, 
            $this->getStoreId(),
            $toGrams
        );
        
        return $weight;
    }
    
    /**
     * Calculates a shipment's base grand total based on it's shipment items
     * 
     * @return float | null
     */
    public function getShipmentBaseGrandTotal()
    {
        if ($this->getData('shipment_base_grand_total')) {
            return $this->getData('shipment_base_grand_total');
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
            return $this->getData('barcode_url');
        }
        
        $barcode = $this->getMainBarcode();
        if (!$barcode) {
            return false;
        }
        
        $helper = $this->getHelper('carrier');
        
        $locale = Mage::getStoreConfig('general/locale/code', $this->getStoreId());
        $lang = substr($locale, 0, 2);
        
        $url = $helper->getBarcodeUrl($barcode, $this->getShippingAddress(), $lang, $forceNl);
        
        $this->setBarcodeUrl($url);
        return $url;
    }
        
    /**
     * Gets the shipment's shipment type for intrnational shipments.
     * If no shipment type is defined, use the default 'commercial goods'.
     * 
     * @return string | null
     */
    public function getShipmentType()
    {
        if ($this->getData('shipment_type')) {
            return $this->getData('shipment_type');
        }
        
        if (!$this->isGlobalShipment()) {
            return null;
        }
        
        $shipmentType = 'Commercial Goods';
        return $shipmentType;
    }
    
    /**
     * Gets the default product code for this shipment from the module's configuration
     * 
     * @return string
     * 
     * @todo implement pakjegemak
     */
    public function getDefaultProductCode()
    {
        $storeId = $this->getStoreId();
        
        if ($this->isPakjeGemakShipment()) {
            /**
             * PakjeGemak default option
             */
            $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION, $storeId);
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        if ($this->isEuShipment()) {
            /**
             * EU default option
             */
            $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EU_PRODUCT_OPTION, $storeId);
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        if ($this->isGlobalShipment()) {
            /**
             * Global default option
             */
            $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTION, $storeId);
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        /**
         * If the shipment is not EU or global, it's dutch (AKA a 'standard' shipment)
         */
        
        /**
         * Dutch shipments may use an alternative default option when the shipment's base grandtotal exceeds a specified amount
         */
        $useAlternativeDefault = Mage::getStoreConfig(self::XML_PATH_USE_ALTERNATIVE_DEFAULT, $storeId);
        if ($useAlternativeDefault) {
            /**
             * Alternative default option usage is enabled
             */
            $maxShipmentAmount = Mage::getStoreConfig(self::XML_PATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT, $storeId);
            if ($this->getShipmentBaseGrandTotal() > $maxShipmentAmount) {
                /**
                 * The shipment's base GT exceeds the specified amount; use the alternative default
                 */
                $productCode = Mage::getStoreConfig(self::XML_PATH_ALTERNATIVE_DEFAULT_OPTION, $storeId);
                $this->_checkProductCodeAllowed($productCode);
                
                return $productCode;
            }
        }
        
        /**
         * standard default option
         */
        $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTION, $storeId);
        $this->_checkProductCodeAllowed($productCode);
        
        return $productCode;
    }
    
    /**
     * Get a specific barcode for this shipment
     * 
     * @param int | null $barcodeNumber Which barcode to get
     * 
     * @return string | null
     */
    public function getBarcode($barcodeNumber = null)
    {
        if (is_null($barcodeNumber) || $barcodeNumber == 0) {
            $barcode = $this->getMainBarcode();
            return $barcode;
        }
        
        $barcode = Mage::getModel('postnl_core/shipment_barcode')
                       ->loadByParentAndBarcodeNumber($this->getId(), $barcodeNumber);
        
        return $barcode->getBarcode();
    }
    
    /**
     * Get all barcodes associated with this shipment
     * 
     * @param $asObject boolean Optional value to get the barcodes as entities, rather than an array of values
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
    
    /****************************************************************************************************************************
     * SETTER METHODS
     ***************************************************************************************************************************/
    
    /**
     * Set an array of labels that are to be saved together
     * 
     * @param array $labels
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function setExtraCoverAmount($amount)
    {
        /**
         * Check if extra cover is allowed for this shipment
         */
        $productCode = $this->getProductCode();
        $extraCoverProductCodes = $this->getExtraCoverProductCodes();
        if (!in_array($productCode, $extraCoverProductCodes)) {
            return $this;
        }
        
        $this->setData('extra_cover_amount', $amount);
        
        return $this;
    }
    
    /**
     * Sets a shipment's shipment type. This is required for GlobalPack shipments
     * 
     * @param string $type
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function setShipmentType($type)
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
        
        $this->setData('shipment_type', $shipmentType);
        return $this;
    }
    
    /**
     * Set this shipment's parcel count. Verifies that the requested amount does not exceed the maximum allowed.
     * 
     * @param int $count
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function setParcelCount($count)
    {
        $maxParcelCount = Mage::getStoreConfig(self::XML_PATH_MAX_PARCEL_COUNT, Mage_Core_Model_App::ADMIN_STORE_ID);
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
    
    /****************************************************************************************************************************
     * SHIPMENT LOCKING AND UNLOCKING FUNCTIONS
     ***************************************************************************************************************************/
    
    /**
     * Lock this shipment to prevent simultaneous execution
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function lock()
    {
        $process = $this->getProcess();
        $process->lockAndBlock();
        
        return $this;
    }
    
    /**
     * Unlock this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
    
    /****************************************************************************************************************************
     * HAS- METHODS
     ***************************************************************************************************************************/
    
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
    
    /****************************************************************************************************************************
     * IS- AND CAN- METHODS
     ***************************************************************************************************************************/
    
    /**
     * Alias for magic getIsPakjeGemak()
     * 
     * Please note the difference between this method and TIG_PostNL_Model_Core_Shipment::isPakjeGemakShipment
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
        $shippingDestination = $this->getShippingAddress()->getCountryId();
        
        $euCountries = $this->getHelper('cif')->getEuCountries();
        
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
        if (!$this->isDutchShipment() && !$this->isEuShipment()) {
            return true;
        }
        
        return false;
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
        
        $postnlOrder = Mage::getModel('postnl_checkout/order')->load($this->getOrderId(), 'order_id');
        if ($postnlOrder->getId() && $postnlOrder->getIsPakjeGemak()) {
            return true;
        }
        
        $pakjeGemakProductCodes = $this->getHelper('cif')->getPakjeGemakProductCodes();
        $productCode = $this->getData('product_code');
        
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
     * Checks if this shipment is a COD shipment
     * 
     * @return boolean
     * 
     * @todo implement this method
     */
    public function isCod()
    {
        return false; //TODO implement this method
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
     * Unconfirmed shipments, shipments whose labels are not yet printed or shipments that are already delivered are inelligible.
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
     * For now the same conditions apply as a regular status update. This may change in a future update of the extension.
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
     * 
     * @return boolean
     */
    public function canSendTrackAndTraceEmail($ignoreAlreadySent = false)
    {
        if ($this->isLocked()) {
            return false;
        }
        
        if ($ignoreAlreadySent !== true && $this->getTrackAndTraceEmailSent()) {
            return false;
        }
        
        $storeId = $this->getStoreId();
        $canSendTrackAndTrace = Mage::getStoreConfig(self::XML_PATH_SEND_TRACK_AND_TRACE_EMAIL, $storeId);
        if (!$canSendTrackAndTrace) {
            return false;
        }
        
        return true;
    }
    
    /****************************************************************************************************************************
     * CIF FUNCTIONALITY METHODS
     ***************************************************************************************************************************/
        
    /**
     * Generates barcodes for this postnl shipment.
     * Barcodes are the basis for all CIF functionality and must therefore be generated before any further action is possible.
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
         * If this shipment consists of a single parcel or if it's an international shipment we only need the main barcode
         */
        if ($parcelCount < 2 || $this->isGlobalShipment()) {
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
     */
    protected function _generateBarcode()
    {
        $shipment = $this->getShipment();
        
        $cif = Mage::getModel('postnl_core/cif');
        $barcodeType = $this->getHelper('cif')->getBarcodeTypeForShipment($this);
        
        $barcode = $cif->generateBarcode($shipment, $barcodeType);
        
        if (!$barcode) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to generate barcode for this shipment: %s', $shipment->getId()),
                'POSTNL-0070'
            );
        }
        
        /**
         * If the generated barcode already exists a new one needs to be generated.
         */
        if ($this->getHelper('cif')->barcodeExists($barcode)) {
            return $this->_generateBarcode();
        }
        
        return $barcode;
    }
    
    /**
     * Generates a shipping labels for a shipment without confirming it with PostNL.
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
     * Get a shipping label from PostNL for a single parcel or a whole shipment
     * 
     * @param boolean $confirm Whether or not to also confirm the shipment
     * @param int | null $barcodeNumber An optional barcode number. If this parameter is null, the main barcode will be used
     * 
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
            $mainbarcode = false;
        } else {
            $barcode = $this->getBarcode($barcodeNumber);
            $barcodeNumber++; //while barcode numbers start at 0, shipment numbers start at 1
        }
        
        $cif = Mage::getModel('postnl_core/cif');
        
        if ($confirm === false) {
            $result = $cif->generateLabelsWithoutConfirm($this, $barcode, $mainBarcode, $barcodeNumber);
        } else {
            $result = $cif->generateLabels($this, $barcode, $mainBarcode, $barcodeNumber);
        }
        
        if (!isset($result->Labels) || !isset($result->Labels->Label)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__(
                    'The confirmAndPrintLabel action returned an invalid response: %s', 
                    var_export($response, true)
                ),
                'POSTNL-0071'
            );
        }
        $labels = $result->Labels->Label;
        
        /**
         * If this is an EU shipment and a non-combi label was returned, the product code needs to be updated
         */
        if ($this->isEuShipment() && !$this->_isCombiLabel()) {
            $this->setProductCode($result->ProductCodeDelivery);
        }
        
        return $labels;
    }
    
    /**
     * Confirm the shipment with PostNL without generating new labels
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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

            $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
                 ->setConfirmedAt(Mage::getModel('core/date')->gmtTimestamp());
            
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

        $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
             ->setConfirmedAt(Mage::getModel('core/date')->gmtTimestamp());
        
        Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));
        
        $this->unlock();
        return $this;
    }
    
    /**
     * Confirms the shipment using CIF
     * 
     * @param int | null $barcodeNumber
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     * 
     * @throws TIG_PostNL_Exception
     */
    protected function _confirm($barcodeNumber = false)
    {
        $mainBarcode = $this->getMainBarcode();
        
        /**
         * if $barcodeNumber is false, this is a single parcel shipment
         */
        if ($barcodeNumber === false) {
            $barcode = $mainBarcode;
            $mainbarcode = false;
        } else {
            $barcode = $this->getBarcode($barcodeNumber);
            $barcodeNumber++; //while barcode numbers start at 0, shipment numbers start at 1
        }
        
        $cif = Mage::getModel('postnl_core/cif');
        $result = $cif->confirmShipment($this, $barcode, $mainBarcode, $barcodeNumber);
        
        $responseShipment = $result->ConfirmingResponseShipment;
        
        /**
         * If the ConfirmingResponseShipment is an object, it means only one shipment was confirmed and the returned barcode
         * has to be the shipment's main barcode.
         */
        if (is_object($responseShipment) 
            && isset($responseShipment->Barcode)
            && $responseShipment->Barcode == $barcode
        ) {
            return $this;
        }
        
        /**
         * If the ConfirmingResponseShipment is an array, it may indicate multiple shipments were confirmed. We need to check the
         * first shipment's barcode to see if it matches the main bartcode.
         */
        if (is_array($responseShipment)) {
            $mainResponseShipment = $responseShipment[0];
            
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
     * @return TIG_PostNL_Model_Core_Shipment
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
         * Confirm and generate labels purely for the main shipment
         */
        if ($parcelCount < 2) {
            $labels = $this->_generateLabel(true);
            $this->addLabels($labels);
        
            $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
                 ->setConfirmedAt(Mage::getModel('core/date')->gmtTimestamp());
            
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
             ->setConfirmedAt(Mage::getModel('core/date')->gmtTimestamp());
                 
        $this->_saveLabels();
        
        Mage::dispatchEvent('postnl_shipment_confirm_after', array('shipment' => $this));
        Mage::dispatchEvent('postnl_shipment_confirmandgeneratelabel_after', array('shipment' => $this));
        
        $this->unlock();
        return $this;
    }
    
    /**
     * Requests a shipping status update for this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
        
        $cif = Mage::getModel('postnl_core/cif');
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
     * @param StdClass $oldStatuses
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
        
        $cif = Mage::getModel('postnl_core/cif');
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
         * Update the shipments status history
         */
        foreach ($completeStatusHistory as $status) {
            $statusHistory = Mage::getModel('postnl_core/shipment_status_history');
            
            /**
             * Check if a status history item exists for the given code and shipment id.
             * If not, create a new one
             */
            if (!$statusHistory->statusHistoryIsNew($this->getId(), $status)) {
                continue;
            }
            
            $timestamp = Mage::getModel('core/date')->gmtTimestamp($status->TimeStamp);
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
        
        $this->setStatusHistoryUpdatedAt(Mage::getModel('core/date')->gmtTimestamp());
        
        Mage::dispatchEvent('postnl_shipment_updatecompleteshippingstatus_after', array('shipment' => $this));
        
        $this->unlock();
        
        return $this;
    }
    
    /****************************************************************************************************************************
     * TRACKING METHODS
     ***************************************************************************************************************************/
    
    /**
     * Adds Magento tracking information to the order containing the previously retrieved barcode
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
        
        $track = Mage::getModel('sales/order_shipment_track')->addData($data);
        $shipment->addTrack($track);
                 
        /**
         * Save the Mage_Sales_Order_Shipment object and the TIG_PostNL_Model_Core_Shipment objects simultaneously
         */
        $transactionSave = Mage::getModel('core/resource_transaction')
                               ->addObject($this)
                               ->addObject($shipment)
                               ->save();
        
        return $this;
    }

    /**
     * Send a track & trace email to the customer containing a link to the 'mijnpakket' environment where they
     * can track their shipment.
     * 
     * @param boolean $ignoreAlreadySent Flag to ignore the 'already sent' check
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function sendTrackAndTraceEmail($ignoreAlreadySent = false)
    {
        if (!$this->canSendTrackAndTraceEmail($ignoreAlreadySent)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('The sendTrackAndTraceEmail action is currently unavailable.'),
                'POSTNL-0076'
            );
        }
        
        $oldStoreId = Mage::app()->getStore()->getId();
        $storeId = $this->getStoreId();
        
        $template = Mage::getStoreConfig(self::XML_PATH_TRACK_AND_TRACE_EMAIL_TEMPLATE, $storeId);
        $mailTemplate = Mage::getModel('core/email_template');
        
        $shippingAddress = $this->getShippingAddress();
        $recipient = array(
            'email' => $this->getShipment()->getOrder()->getCustomerEmail(),
            'name'  => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
        );
        
        $mailTemplate->setDesignConfig(
            array(
                'area'  => 'frontend', 
                'store' => $storeId
            )
        );
        
        $shipment = $this->getShipment();
        $order = $shipment->getOrder();
        $templateVariables = array(
            'postnlshipment' => $this,
            'barcode'        => $this->getMainBarcode(),
            'barcode_url'    => $this->getBarcodeUrl(false),
            'shipment'       => $shipment,
            'order'          => $order,
            'customer'       => $order->getCustomer(),
            'quote'          => $order->getQuote(),
        );
        
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
                Mage::helper('postnl')->__('Unable to send track and trace email for shipment #', $this->getShipmentId()),
                'POSTNL-0077'
            );
        }
        
        return $this;
    }
    
    /****************************************************************************************************************************
     * BARCODE PROCESSING METHODS
     ***************************************************************************************************************************/
    
    /**
     * Add a barcode to this shipment's barcode collection
     * 
     * @param string $barcode The barcode to add
     * @param int $barcodeNumber The number of this barcode
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _addBarcode($barcode, $barcodeNumber)
    {
        $barcodeModel = Mage::getModel('postnl_core/shipment_barcode');
        $barcodeModel->setParentId($this->getId())
                     ->setBarcode($barcode)
                     ->setBarcodeNumber($barcodeNumber)
                     ->save();
                     
        return $this;
    }
    
    /****************************************************************************************************************************
     * LABEL PROCESSING METHODS
     ***************************************************************************************************************************/
    
    /**
     * Add labels to this shipment
     * 
     * @param mixed $labels An array of labels or a single label object
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _addLabel($label)
    {
        $labelType = $label->Labeltype;
        
        if ($this->_isCombiLabel()) {
            $labelType = 'Label-combi';
        }
        
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
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _addLabelToSave($label)
    {
        $labelsToSave = $this->getlabelsToSave();
        
        $labelsToSave[] = $label;
        
        $this->setLabelsToSave($labelsToSave);
        
        return $this;
    }
    
    /**
     * Save all newly added labels at once
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    protected function _saveLabels()
    {
        $transactionSave = Mage::getModel('core/resource_transaction');
        
        /**
         * Add all labels to the transaction
         */
        $labelsToSave = $this->getLabelsToSave();
        
        Mage::dispatchEvent('postnl_shipment_savelabels_before', array('shipment' => $this, 'labels' => $labelsToSave));
        
        foreach ($labelsToSave as $label) {
            $transactionSave->addObject($label);
        }
        
        /**
         * Save the transaction
         */
        $transactionSave->save();
        
        Mage::dispatchEvent('postnl_shipment_savelabels_after', array('shipment' => $this, 'labels' => $labelsToSave));
        
        return $this;
    }
    
    /**
     * Check if the returned label is a combi-label
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
    
    /****************************************************************************************************************************
     * STATUS PROCESSING METHODS
     ***************************************************************************************************************************/
    
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
    
    /****************************************************************************************************************************
     * PRODUCT CODE METHODS
     ***************************************************************************************************************************/

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
        $cifHelper = $this->getHelper('cif');
        $allowedProductCodes = array();
        
        /**
         * PakjeGemak shipments are also dutch shipments
         */
        if ($this->isDutchShipment() && $this->isPakjeGemakShipment()) {
            $allowedProductCodes = $cifHelper->getPakjeGemakProductCodes();
        }
        
        /**
         * Here we specifically want shipments that are dutch, but not PakjeGemak
         */
        if ($this->isDutchShipment() && !$this->isPakjeGemakShipment()) {
            $allowedProductCodes = $cifHelper->getStandardProductCodes();
        }
        
        if ($this->isEuShipment()) {
            $allowedProductCodes = $cifHelper->getEuProductCodes();
            
        }
        
        if ($this->isGlobalShipment()) {
            if (!$cifHelper->isGlobalAllowed()) {
                throw new TIG_PostNL_Exception(
                    $cifHelper->__('Product code %s is not allowed for this shipment.', $productCode),
                    'POSTNL-0078'
                );
            }
            
            $allowedProductCodes = $cifHelper->getGlobalProductCodes();
        }
        
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
     * @return boolean|array Either false if the code is not restricted, or otherwise an array of allowed country IDs
     */
    protected function _isCodeRestricted($code)
    {
        $countryRestrictedCodes = $this->getHelper('cif')->getCountryRestrictedProductCodes();
        
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
    
    /****************************************************************************************************************************
     * ADDITIONAL SHIPMENT OPTIONS
     ***************************************************************************************************************************/
    
    /**
     * Public alias for _saveAdditionalShippingOptions()
     * 
     * @return TIG_PostNL_Model_Core_Shipment
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
     * @return TIG_PostNL_Model_Core_Shipment
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
                'options' => $additionalOptions
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
         * get the weight per parcel
         */
        $weightPerParcel = Mage::getStoreConfig(self::XML_PATH_WEIGHT_PER_PARCEL, $this->getStoreId());
        $weightPerParcel = $this->getHelper('cif')->standardizeWeight($weightPerParcel, $this->getStoreId());
        
        /**
         * calculate the number of parcels needed to ship the total weight of this shipment
         */
        $parcelCount = ceil($weight / $weightPerParcel);
        
        return $parcelCount;
    }
    
    /****************************************************************************************************************************
     * RESET AND DELETE METHODS
     ***************************************************************************************************************************/
    
    /**
     * Resets this shipment to a pre-confirmed state
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function resetConfirmation()
    {
        $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED) //set status to unconfirmed
             ->setShippingPhase(false) //delete current shipping phase
             ->setConfirmedAt(false) //delete 'confirmed at' date
             ->setlabelsPrinted(0) //labels have not been printed
             ->deleteLabels() //delete all associated labels
             ->deleteBarcodes() //delete all associated barcodes
             ->deleteStatusHistory(); //delete all associated status history items
             
        return $this;
    }
    
    /**
     * Removes all labels associated with this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function deleteLabels()
    {
        $labelCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');
        $labelCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));
        
        $labels = $labelCollection->getItems();
        
        foreach ($labels as $label) {
            $label->delete()
                  ->save();
        }
        
        return $this;
    }
    
    /**
     * Removes all barcodes associated with this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function deleteBarcodes()
    {
        $barcodes = $this->getBarcodes(true);
        
        foreach ($barcodes as $barcode) {
            $barcode->delete()
                    ->save();
        }
        
        $this->setMainBarcode(false);
        
        return $this;
    }
    
    /**
     * Deletes all status history items associated with this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function deleteStatusHistory()
    {
        $statusHistoryCollection = Mage::getResourceModel('postnl_core/shipment_status_history_collection');
        $statusHistoryCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));
        
        foreach ($statusHistoryCollection as $status) {
            $status->delete()
                   ->save();
        }
        
        return $this;
    }
    
    /****************************************************************************************************************************
     * BEFORE- AND AFTERSAVE METHODS
     ***************************************************************************************************************************/
    
    /**
     * Updates the shipment's attributes before saving this shipment
     * 
     * @return Mage_Core_Model_Abstract::_beforeSave
     */
    protected function _beforeSave()
    {
        $currentTimestamp = Mage::getModel('core/date')->gmtTimestamp();
        
        /**
         * Store any shipment options that have been saved in the registry
         */
        if (Mage::registry('postnl_additional_options')) {
            $this->_saveAdditionalShippingOptions();
        }
        
        /**
         * Set confirm status
         */
        if ($this->getConfirmStatus() === null) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED);
        }
        
        /**
         * Set confrirmed at
         */
        if ($this->getConfirmedStatus() == self::CONFIRM_STATUS_CONFIRMED
            && $this->getConfirmedAt() === null
        ) {
            $this->setConfirmedAt($currentTimestamp);
        }
        
        /**
         * Set whether labels have printed or not
         */
        if ($this->getlabelsPrinted() == 0 && $this->hasLabels()) {
            $this->setLabelsPrinted(1);
        }
        
        /**
         * Set a product code
         */
        if (!$this->getProductCode() || Mage::registry('postnl_product_option') !== null) {
            $productCode = $this->_getProductCode();
            $this->setProductCode($productCode);
        }
        
        /**
         * Set the parcel count
         */
        if (!$this->getParcelCount()) {
            $parcelCount = $this->_calculateParcelCount();
            $this->setParcelCount($parcelCount);
        }
        
        /**
         * Set the confirm date
         */
        if (!$this->getConfirmDate()) {
            $this->setConfirmDate($currentTimestamp);
        }
        
        /**
         * If this shipment is new, set it's created at date to the current timestamp
         */
        if (!$this->getId()) {
            $this->setCreatedAt($currentTimestamp);
        }
        
        /**
         * Always update the updated at timestamp to the current timestamp
         */
        $this->setUpdatedAt($currentTimestamp);
        
        return parent::_beforeSave();
    }
}