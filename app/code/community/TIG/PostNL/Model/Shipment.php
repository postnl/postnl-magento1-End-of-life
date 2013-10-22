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
class TIG_PostNL_Model_Shipment extends Mage_Core_Model_Abstract
{
    /**
     * Carrier code used by postnl
     */
    const POSTNL_CARRIER_CODE = 'postnl';
    
    /**
     * Possible confirm statusses
     */
    const CONFIRM_STATUS_CONFIRMED   = 'confirmed';
    const CONFIRM_STATUS_UNCONFIRMED = 'unconfirmed';
    
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
    const XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTIONS = 'postnl/cif_product_options/default_product_options';
    const XML_PATH_DEFAULT_EU_PRODUCT_OPTIONS       = 'postnl/cif_product_options/default_eu_product_options';
    const XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTIONS   = 'postnl/cif_product_options/default_global_product_options';
    
    /**
     * xml path to eu countries setting
     */
    const XML_PATH_EU_COUNTRIES = 'general/country/eu_countries'; 
    
    public function _construct()
    {
        $this->_init('postnl/shipment');
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
     * @return array Array of TIG_PostNL_Model_Shipment_Label objects
     */
    public function getLabels()
    {
        $labelCollection = Mage::getResourceModel('postnl/shipment_label_collection');
        $labelCollection->addFieldToFilter('parent_id', array('eq' => $this->getid()));
        
        $labels = $labelCollection->getItems();
        return $labels;
    }
    
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
     * Checks if the current entity may generate a barcode.
     * 
     * @return boolean
     */
    public function canGenerateBarcode()
    {
        if (!$this->getShipmentId() && !$this->getShipment()) {
            return false;
        }
        
        if ($this->getBarcode()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Checks if the current entity can be confirmed.
     * 
     * @return boolean
     */
    public function canConfirm()
    {
        if ($this->getConfirmStatus() == self::CONFIRM_STATUS_CONFIRMED) {
            return false;
        }
        
        if (!$this->getShipmentId() && !$this->getShipment()) {
            return false;
        }
        
        if (!$this->getBarcode()) {
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
        
        if (!$this->getBarcode()) {
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
        $select->from($coreResource->getTableName('postnl/shipment_label', array('label_id')))
               ->where('`label_type` = ?', $labelType)
               ->where('`parent_id` = ?', $this->getId());
        
        $label = $readConn->fetchOne($select);
        
        if ($label === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Gets the product code for this shipment. If specific options have been selected
     * those will be used. Otherwise the default options will be used from system/config
     * 
     * @return int
     * 
     * @todo implement EU combilabel product codes
     */
    protected function _getProductCode()
    {
        /**
         * Product options were set manually by the user
         */
        if (Mage::registry('postnl_product_options')) {
            $productCode = Mage::registry('postnl_product_options');
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        /**
         * Use default options
         */
        if ($this->isEuShipment()) {
            /**
             * EU default option
             */
            $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EU_PRODUCT_OPTIONS, $this->getStoreId());
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        if ($this->isGlobalShipment()) {
            /**
             * Global default option
             */
            $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EU_GLOBAL_PRODUCT_OPTIONS, $this->getStoreId());
            $this->_checkProductCodeAllowed($productCode);
            
            return $productCode;
        }
        
        /**
         * standard default option
         */
        $productCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTIONS, $this->getStoreId());
        $this->_checkProductCodeAllowed($productCode);
        
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
     * @todo implement EU combilabel shipments
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
    
    /**
     * Generates a barcode for this postnl shipment.
     * Barcodes are the basis for all CIF functionality and must therefore be generated before any further action is possible.
     * 
     * @return TIG_PostNL_Model_Shipment
     * 
     * @throws TIG_PostNL_Exception
     */
    public function generateBarcode()
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
            return $this->generateBarcode();
        }
        
        $this->setBarcode($barcode);
        return $this;
    }

    /**
     * Adds Magento tracking information to the order containing the previously retrieved barcode
     * 
     * @return TIG_PostNL_Model_Shipment
     * 
     * @throws TIG_PostNL_Exception
     */
    public function addTrackingCodeToShipment()
    {
        $shipment = $this->getShipment();
        $barcode = $this->getBarcode();
        
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
         * Save the Mage_Sales_Order_Shipment object and the TIG_PostNL_Model_Shipment objects simultaneously
         */
        $transactionSave = Mage::getModel('core/resource_transaction')
                               ->addObject($this)
                               ->addObject($shipment)
                               ->save();
        
        return $this;
    }
    
    /**
     * Generates a shipping label and confirms the shipment with postNL.
     * 
     * @return TIG_PostNL_Model_Shipment
     * 
     * @throws TIG_PostNL_Exception
     */
    public function confirmAndPrintLabel()
    {
        if (!$this->canConfirm()) {
            throw Mage::exception('TIG_PostNL', 'The confirmAndPrintLabel action is currently unavailable.');
        }
        
        $cif = Mage::getModel('postnl_core/cif');
        $result = $cif->generateLabels($this);
        
        if (!isset($result->Labels) || !isset($result->Labels->Label)) {
            throw Mage::exception('TIG_PostNL', "The confirmAndPrintLabel action returned an invalid response: \n" . var_export($response, true));
        }
        $labels = $result->Labels->Label;
        
        $this->addLabels($labels);
        
        $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED);
        
        return $this;
    }
    
    /**
     * Requests a shipping status update for this shipment
     * 
     * @return TIG_PostNL_Model_Shipment
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
    }
    
    /**
     * Add labels to this shipment
     * 
     * @param mixed $labels An array of labels or a single label object
     * 
     * @return TIG_PostNL_Model_Shipment
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
     * @return TIG_PostNL_Model_Shipment
     */
    protected function _addLabel($label)
    {
        $labelType = $label->Labeltype;
        if ($this->hasLabelType($labelType)){
            return $this;
        }
        
        $postnlLabel = Mage::getModel('postnl/shipment_label');
        $postnlLabel->setParentId($this->getId())
                    ->setLabel(base64_encode($label->Content))
                    ->setLabelType($labelType)
                    ->save();
              
        return $this;
    }
    
    /**
     * Updates the shipment's attributes if they have not yet been set
     * 
     * @return Mage_Core_Model_Abstract::_beforeSave
     */
    protected function _beforeSave()
    {
        if ($this->getConfirmStatus() === null && $this->getLabel()) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED);
        } elseif ($this->getConfirmStatus() === null) {
            $this->setConfirmStatus(self::CONFIRM_STATUS_UNCONFIRMED);
        }
        
        if (!$this->getProductCode()) {
            $productCode = $this->_getProductCode();
            $this->setProductCode($productCode);
        }
        
        if (!$this->getConfirmDate()) {
            $this->setConfirmDate(Mage::getModel('core/date')->timestamp());
        }
        
        return parent::_beforeSave();
    }
}