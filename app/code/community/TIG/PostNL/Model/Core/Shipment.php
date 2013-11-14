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
    const SHIPPING_PHASE_COLLECTION     = '01';
    const SHIPPING_PHASE_SORTING        = '02';
    const SHIPPING_PHASE_DISTRIBUTION   = '03';
    const SHIPPING_PHASE_DELIVERED      = '04';
    const SHIPPING_PHASE_NOT_APPLICABLE = '99';
    
    /**
     * XML paths to default product options settings
     */
    const XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTION = 'postnl/cif_product_options/default_product_option';
    const XML_PATH_DEFAULT_EU_PRODUCT_OPTION       = 'postnl/cif_product_options/default_eu_product_option';
    const XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTION   = 'postnl/cif_product_options/default_global_product_option';
    const XML_PATH_USE_ALTERNATIVE_DEFAULT         = 'postnl/cif_product_options/use_alternative_default';
    const XML_PATH_ALTERNATIVE_DEFAULT_MAX_AMOUNT  = 'postnl/cif_product_options/alternative_default_max_amount';
    const XML_PATH_ALTERNATIVE_DEFAULT_OPTION      = 'postnl/cif_product_options/alternative_default_option';
    
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
     * CIF warning code returned when an EPS combi label is not available
     */
    const EPS_COMBI_LABEL_WARNING_CODE = 'LIRS_0';
    
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
    
    public function _construct()
    {
        $this->_init('postnl_core/shipment');
    }
    
    /****************************************************************************************************************************
     * GETTER AND SETTER METHODS
     ***************************************************************************************************************************/
    
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
     * Get all product codes that have extra cover
     * 
     * @return array
     */
    public function getExtraCoverProductCodes()
    {
        return $this->_extraCoverProductCodes;
    }
    
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
     * get PostNL Carrier helper
     * 
     * @return TIG_PostNL_Helper_Carrier
     */
    public function getHelper()
    {
        if ($this->getData('helper')) {
            return $this->getData('helper');
        }
        
        $helper = Mage::helper('postnl/carrier');
        
        $this->setHelper($helper);
        return $helper;
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
        if ($this->getData('extra_cover_amount')) {
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
        $weight = Mage::helper('postnl/cif')->standardizeWeight(
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
     * Sets the shipment's current shipping phase. Forces the phase to a 2-digit string if a single digit string is provided
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function setShippingPhase($phase)
    {
        if (strlen($phase < 2)) {
            $phase = '0' . $phase;
        }
        
        $this->setData('shipping_phase', $phase);
        return $this;
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
     * IS / CAN / HAS METHODS
     ***************************************************************************************************************************/
        
    /**
     * Check if the shipping destination of this shipment is NL
     * 
     * @return boolean
     */
    public function isDutchShipment()
    {
        $shippingDestination = $this->getShippingAddress()->getCountry();
        
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
        $shippingDestination = $this->getShippingAddress()->getCountry();
        
        $euCountries = Mage::helper('postnl/cif')->getEuCountries();
        
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
     * PakjeGemak functionality is not yet implemented.
     * 
     * @return boolean
     * 
     * @todo implement this method
     */
    public function isPakjeGemakShipment()
    {
        return false; //not yet implemented
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
     * Unconfirmed shipments or shipments that are already delivered are inelligible.
     * 
     * @return boolean
     */
    public function canUpdateShippingStatus()
    {
        if (self::CONFIRM_STATUS_CONFIRMED != $this->getConfirmStatus()) {
            return false;
        }
        
        if (self::SHIPPING_PHASE_DELIVERED == $this->getShippingPhase()) {
            return false;
        }
        
        if (!$this->getMainBarcode()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Checks if the current shipment is eligible for a complete shipping status update.
     * Unconfirmed shipments are inelligible.
     * 
     * @return boolean
     */
    public function canUpdateCompleteShippingStatus()
    {
        if (self::CONFIRM_STATUS_CONFIRMED != $this->getConfirmStatus()) {
            return false;
        }
        
        if (!$this->getMainBarcode()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Checks if the current shipment can send a track & trace email to the customer.
     * 
     * @return boolean
     */
    public function canSendTrackAndTraceEmail()
    {
        if ($this->getTrackAndTraceEmailSent()) {
            return false;
        }
        
        $storeId = $this->getStoreId();
        $canSendTrackAndTrace = Mage::getStoreConfig(self::XML_PATH_SEND_TRACK_AND_TRACE_EMAIL, $storeId);
        if (!$canSendTrackAndTrace) {
            return false;
        }
        
        return true;
    }
    
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
            throw Mage::exception('TIG_PostNL', 'The generateBarcodes action is currently unavailable.');
        }
        
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
            return $this;
        }
        
        /**
         * Generate a barcode for each parcel and save it
         */
        for ($i = 1; $i < $parcelCount; $i++) {
            $barcode = $this->_generateBarcode();
            $this->_addBarcode($barcode, $i);
        }
        
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
        if (!$this->canGenerateBarcode()) {
            throw Mage::exception('TIG_PostNL', 'The generateBarcode action is currently unavailable.');
        }
        
        $shipment = $this->getShipment();
        
        $cif = Mage::getModel('postnl_core/cif');
        $barcodeType = Mage::helper('postnl/cif')->getBarcodeTypeForShipment($this);
        
        $barcode = $cif->generateBarcode($shipment, $barcodeType);
        
        if (!$barcode) {
            throw Mage::exception('TIG_PostNL', 'Unable to generate barcode for this shipment: '. $shipment->getId());
        }
        
        /**
         * If the generated barcode already exists a new one needs to be generated.
         */
        if (Mage::helper('postnl/cif')->barcodeExists($barcode)) {
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
        
        /**
         * If this is an EU shipment and a non-combi label was returned, the product code needs to be updated
         */
        if ($this->isEuShipment() && !$this->_isCombiLabel()) {
            $this->setProductCode($result->ProductCodeDelivery);
        }
             
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
            throw Mage::exception('TIG_PostNL', "The confirmAndPrintLabel action returned an invalid response: \n" . var_export($response, true));
        }
        $labels = $result->Labels->Label;
        
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
            throw Mage::exception('TIG_PostNL', 'The confirm action is currently unavailable.');
        }
        
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
                 ->setConfirmedAt(Mage::getModel('core/date')->timestamp());
            
            return $this;
        }
        
        /**
         * onfirm each parcel in the shipment seperately
         */
        for ($i = 0; $i < $parcelCount; $i++) {
            $this->_confirm($i);
        }

        $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED)
             ->setConfirmedAt(Mage::getModel('core/date')->timestamp());
        
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
        throw Mage::exception('TIG_PostNL', 'Invalid confirm response recieved: ' . var_export($result, true));
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
            throw Mage::exception('TIG_PostNL', 'The confirmAndGenerateLabel action is currently unavailable.');
        }
        
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
            
            $this->_saveLabels();
            
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
             ->setConfirmedAt(Mage::getModel('core/date')->timestamp());
                 
        $this->_saveLabels();
        
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
            throw Mage::exception('TIG_PostNL', 'The updateShippingStatus action is currently unavailable.');
        }
        
        $cif = Mage::getModel('postnl_core/cif');
        $result = $cif->getShipmentStatus($this);
        
        $currentPhase = $result->Status->CurrentPhaseCode;
        $this->setShippingPhase($currentPhase);
        
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
            throw Mage::exception('TIG_PostNL', 'The updateShippingStatus action is currently unavailable.');
        }
        
        $cif = Mage::getModel('postnl_core/cif');
        $result = $cif->getCompleteShipmentStatus($this);
        
        /**
         * Update the shipment's shipping phase
         */
        $currentPhase = $result->Status->CurrentPhaseCode;
        $this->setShippingPhase($currentPhase);
        
        if (!isset($result->Events->CompleteStatusResponseEvent)) {
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
            
            $statusHistory->setParentId($this->getId())
                          ->setCode($status->Code)
                          ->setDescription($status->Description)
                          ->setLocationCode($status->LocationCode)
                          ->setDestinationLocationCode($status->DestinationLocationCode)
                          ->setRouteCode($status->RouteCode)
                          ->setRouteName($status->RouteName)
                          ->setTimestamp(strtotime($status->TimeStamp))
                          ->save();
        }
        
        $this->setStatusHistoryUpdatedAt(Mage::getModel('core/date')->timestamp());
        
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
            throw Mage::exception('TIG_PostNL', 'Unable to add tracking info: no barcode or shipment available.');
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
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function sendTrackAndTraceEmail()
    {
        if (!$this->canSendTrackAndTraceEmail()) {
            throw Mage::exception('TIG_PostNL', 'The sendTrackAndTraceEmail action is currently unavailable.');
        }
        
        $storeId = $this->getStoreId();
        $template = Mage::getStoreConfig(self::XML_PATH_TRACK_AND_TRACE_EMAIL_TEMPLATE, $storeId);
        $mailTemplate = Mage::getModel('core/email_template');
        
        $shippingAddress = $this->getShippingAddress();
        $recipient = array(
            'email' => $shippingAddress->getEmail(),
            'name'  => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
        );
        
        $mailTemplate->setDesignConfig(
            array(
                'area'  => 'frontend', 
                'store' => $storeId
            )
        );
        
        $templateVariables = array(
            'customer'       => $customer,
            'quote'          => $quote,
            'shipment'       => $this->getShipment(),
            'order'          => $this->getShipment()->getOrder(),
            'postnlshipment' => $this,
            'barcode'        => $this->getMainBarcode(),
            'barcode_url'    => Mage::helper('postnl/carrier')->getBarcodeUrl(
                                    $this->getMainBarcode(), 
                                    $this->getShippingAddress()
                                ),
        );
        
        $orderModel = Mage::getConfig()->getModelClassName('sales/order');
        $mailTemplate->sendTransactional(
            $template,
            Mage::getStoreConfig($orderModel::XML_PATH_EMAIL_IDENTITY, $storeId),
            $recipient['email'],
            $recipient['name'],
            $templateVariables
       );
       
       return $this;
    }
    
    /****************************************************************************************************************************
     * BARCOCDE PROCESSING METHODS
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
        
        foreach ($labelsToSave as $label) {
            $transactionSave->addObject($label);
        }
        
        /**
         * Save the transaction
         */
        $transactionSave->save();
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
     * 
     * @todo implement PakjeGemak product codes
     */
    protected function _checkProductCodeAllowed($productCode)
    {
        $cifHelper = Mage::helper('postnl/cif');
        $allowedProductCodes = array();
        
        if ($this->isDutchShipment() && !$this->isPakjeGemakShipment()) {
            $allowedProductCodes = $cifHelper->getStandardProductCodes();
        }
        if ($this->isDutchShipment() && $this->isPakjeGemakShipment()) {
            $allowedProductCodes = $cifHelper->getPakjeGemakProductCodes();
        }
        
        if ($this->isEuShipment()) {
            $allowedProductCodes = $cifHelper->getEuProductCodes();
        }
        
        if ($this->isGlobalShipment()) {
            $allowedProductCodes = $cifHelper->getGlobalProductCodes();
        }
        
        if (!in_array($productCode, $allowedProductCodes)) {
            throw Mage::exception('TIG_PostNL', 'Product code ' . $productCode . ' is not allowed for this shipment.');
        }
        
        return true;
    }
    
    /****************************************************************************************************************************
     * ADDITIONAL SHIPMENT OPTIONS
     ***************************************************************************************************************************/
    
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
             ->deleteBarcodes(); //delete all associated barcodes
             
        return $this;
    }
    
    /**
     * Removes all labels associated with this shipment
     * 
     * @return TIG_PostNL_Model_Core_Shipment
     */
    public function deleteLabels()
    {
        $labels = $this->getLabels();
        
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
     * Updates the shipment's attributes before saving this shipment
     * 
     * @return Mage_Core_Model_Abstract::_beforeSave
     */
    protected function _beforeSave()
    {
        $currentTimestamp = Mage::getModel('core/date')->timestamp();
        
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
        if (!$this->getProductCode()) {
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
         * If this shiopment is new, set it's created at date to the current timestamp
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