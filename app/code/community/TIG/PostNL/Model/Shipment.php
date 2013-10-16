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
     * 
     * @var string
     */
    const POSTNL_CARRIER_CODE = 'postnl';
    
    /**
     * Possible confirm statusses
     * 
     * @var string
     */
    const CONFIRM_STATUS_CONFIRMED   = 'confirmed';
    const CONFIRM_STATUS_UNCONFIRMED = 'unconfirmed';
    
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
        if (!$shipmentId) {
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
     * Retrieves the calculated product code for this shipment. If no code is set, it calculates it based on default settings
     * 
     * @return string
     */
    public function getProductCode()
    {
        //TODO finish method
        
        return '3085';
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
        $barcodeType = Mage::helper('postnl/cif')->getBarcodeTypeForShipment($shipment);
        
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
        $label = $result->Labels->Label;
        
        $this->setLabel(base64_encode($label->Content))
             ->setConfirmStatus(self::CONFIRM_STATUS_CONFIRMED);
        
        return $this;
    }
    
    /**
     * Updates the shipment's confirm status if it is still null
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
        
        return parent::_beforeSave();
    }
}