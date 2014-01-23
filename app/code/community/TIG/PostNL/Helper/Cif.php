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
 * Helper class for CIF operations
 */
class TIG_PostNL_Helper_Cif extends TIG_PostNL_Helper_Data
{
    /**
     * Log filename to log all CIF exceptions
     */
    const CIF_EXCEPTION_LOG_FILE = 'TIG_PostNL_CIF_Exception.log';
    
    /**
     * Log filename to log CIF calls
     */
    const CIF_DEBUG_LOG_FILE = 'TIG_PostNL_CIF_Debug.log';
    
    /**
     * available barcode types
     */
    const DUTCH_BARCODE_TYPE  = 'NL';
    const EU_BARCODE_TYPE     = 'EU';
    const GLOBAL_BARCODE_TYPE = 'GLOBAL';
    
    /**
     * XML path to infinite label printiong setting
     */
    const XML_PATH_INFINITE_LABEL_PRINTING = 'postnl/advanced/infinite_label_printing';
    
    /**
     * XML path to weight unit used
     */
    const XML_PATH_WEIGHT_UNIT = 'postnl/cif_labels_and_confirming/weight_unit';
    
    /**
     * XML path to weight per parcel config setting
     */
    const XML_PATH_WEIGHT_PER_PARCEL = 'postnl/cif_labels_and_confirming/weight_per_parcel'; 
    
    /**
     * XML paths to default product options settings
     */
    const XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTION = 'postnl/cif_product_options/default_product_option';
    const XML_PATH_DEFAULT_EU_PRODUCT_OPTION       = 'postnl/cif_product_options/default_eu_product_option';
    const XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTION   = 'postnl/cif_product_options/default_global_product_option';
    
    /**
     * Array of countries to which PostNL ships using EPS. Other EU countries are shipped to using GlobalPack
     * 
     * @var array
     */
    protected $_euCountries = array(
        'BE',
        'BG',
        'DK',
        'DE',
        'EE',
        'FI',
        'FR',
        'GB',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'AT',
        'PL',
        'PT',
        'RO',
        'SI',
        'SK',
        'ES',
        'CZ',
        'SE',
    );
    
    /**
     * Array of country-restricted product codes
     * 
     * Array is constructed as follows:
     * <restricted code> => array(<allowed country>, <allowed country>, <allowed country>,...)
     * 
     * @var array
     */
    protected $_countryRestrictedProductCodes = array(
        '4955' => array(
                      'BE',
                  ),
    );
    
    /**
     * Array of supported shipment types
     * 
     * @var array
     */
    protected $_shipmentTypes = array(
        'gift'              => 'Gift',
        'documents'         => 'Documents',
        'commercial_goods'  => 'Commercial Goods',
        'commercial_sample' => 'Commercial Sample',
        'returned_goods'    => 'Returned Goods',
    );
    
    /**
     * Array of possible shipping phases
     * 
     * @var array
     */
    protected $_shippingPhases = array(
        '1'  => 'Reported at PostNL',
        '2'  => 'Sorted',
        '3'  => 'In Distribution',
        '4'  => 'Delivered',
        '99' => 'Shipment not found',
    );
    
    /**
     * Get an array of EU countries
     * 
     * @return array
     */
    public function getEuCountries()
    {
        return $this->_euCountries;
    }
    
    /**
     * Gets an array of country-restricted product codes
     * 
     * @return array
     */
    public function getCountryRestrictedProductCodes()
    {
        return $this->_countryRestrictedProductCodes;
    }
    
    /**
     * Get an array of standard product codes
     * 
     * @return array
     */
    public function getStandardProductCodes($storeId = false)
    {
        $standardProductCodes = Mage::getSingleton('postnl_core/system_config_source_standardProductOptions');
        return $standardProductCodes->getAvailableOptions($storeId, true);
    }
    
    /**
     * Get an array of pakjegemak product codes
     * 
     * @return array
     */
    public function getPakjeGemakProductCodes($storeId = false)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');
        return $pakjeGemakProductCodes->getAvailableOptions($storeId, true);
    }
    
    /**
     * Get an array of eu product codes
     * 
     * @return array
     */
    public function getEuProductCodes($storeId = false)
    {
        $euProductCodes = Mage::getSingleton('postnl_core/system_config_source_euProductOptions');
        return $euProductCodes->getAvailableOptions($storeId, true);
    }
    
    /**
     * Get an array of global product codes
     * 
     * @return array
     */
    public function getGlobalProductCodes($storeId = false)
    {
        $globalProductCodes = Mage::getSingleton('postnl_core/system_config_source_globalProductOptions');
        return $globalProductCodes->getAvailableOptions($storeId, true);
    }
    
    /**
     * Get an array of possible shipment types
     * 
     * @return array
     */
    public function getShipmentTypes()
    {
        return $this->_shipmentTypes;
    }
    
    /**
     * Get an array of possible shipping phases
     * 
     * @return array
     */
    public function getShippingPhases()
    {
        $shippingPhases = $this->_shippingPhases;
        foreach ($shippingPhases as $key => &$value) {
            $value = $this->__($value);
        }
        
        return $shippingPhases;
    }
    
    /**
     * Checks if infinite label printing is enabled in the module configuration.
     * 
     * @return boolean
     */
    public function allowInfinitePrinting()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $enabled = Mage::getStoreConfigFlag(self::XML_PATH_INFINITE_LABEL_PRINTING, $storeId);
        
        return $enabled;
    }
    /**
     * Checks which barcode type is applicable for this shipment
     * 
     * Possible return values:
     * - NL
     * - EU
     * - GLOBAL
     * 
     * @var Mage_Sales_Model_Order_Shipment
     * 
     * @return string | TIG_PostNL_Helper_Cif
     * 
     * @throws TIG_PostNL_Exception
     */
    public function getBarcodeTypeForShipment($shipment)
    {
        if ($shipment->isDutchShipment() || $shipment->isPakjeGemakShipment()) {
            $barcodeType = self::DUTCH_BARCODE_TYPE;
            return $barcodeType;
        }
        
        if ($shipment->isEuShipment()) {
            $barcodeType = self::EU_BARCODE_TYPE;
            return $barcodeType;
        }
        
        if ($shipment->isGlobalShipment()) {
            $barcodeType = self::GLOBAL_BARCODE_TYPE;
            return $barcodeType;
        }
        
        throw new TIG_PostNL_Exception(
            $this->__('Unable to get valid barcodetype for postnl shipment id #%s', $shipment->getId()),
            'POSTNL-0029'
        );
        
        return $this;
    }
    
    /**
     * Get a list of available product options for a specified shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return array | null
     */
    public function getProductOptionsForShipment($shipment)
    {
        /**
         * PakjeGemak product options
         */
        if ($this->isPakjeGemakShipment($shipment)) {
            $options = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions')
                           ->getAvailableOptions();
                           
            return $options;
        }
        
        /**
         * Dutch product options
         */
        if ($this->isDutchShipment($shipment)) {
            $options = Mage::getModel('postnl_core/system_config_source_standardProductOptions')
                           ->getAvailableOptions();
                           
            return $options;
        }
        
        /**
         * EU product options
         */
        if ($this->isEuShipment($shipment)) {
            $options = Mage::getModel('postnl_core/system_config_source_euProductOptions')
                           ->getAvailableOptions();
                           
            return $options;
        }
        
        /**
         * Global product options
         */
        if ($this->isGlobalShipment($shipment)) {
            $options = Mage::getModel('postnl_core/system_config_source_globalProductOptions')
                           ->getAvailableOptions();
                           
            return $options;
        }
        
        return null;
    }
    
    /**
     * Check if a given shipment is PakjeGemak
     * 
     * @param TIG_PostNL_Model_Core_Shipment | Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return boolean
     * 
     * @see TIG_PostNL_Model_Core_Shipment->isDutchSHipment();
     */
    public function isPakjeGemakShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            return $shipment->isPakjeGemakShipment();
        }
        
        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);
        
        return $tempPostnlShipment->isPakjeGemakShipment();
    }
    
    /**
     * Check if a given shipment is dutch
     * 
     * @param TIG_PostNL_Model_Core_Shipment | Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return boolean
     * 
     * @see TIG_PostNL_Model_Core_Shipment->isDutchSHipment();
     */
    public function isDutchShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            return $shipment->isDutchShipment();
        }
        
        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);
        
        return $tempPostnlShipment->isDutchShipment();
    }
    
    /**
     * Check if a given shipment has an EU destination
     * 
     * @param TIG_PostNL_Model_Core_Shipment | Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return boolean
     * 
     * @see TIG_PostNL_Model_Core_Shipment->isDutchSHipment();
     */
    public function isEuShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            return $shipment->isEuShipment();
        }
        
        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);
        
        return $tempPostnlShipment->isEuShipment();
    }
    
    /**
     * Check if a given shipment has a global destination
     * 
     * @param TIG_PostNL_Model_Core_Shipment | Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return boolean
     * 
     * @see TIG_PostNL_Model_Core_Shipment->isDutchSHipment();
     */
    public function isGlobalShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            return $shipment->isGlobalShipment();
        }
        
        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);
        
        return $tempPostnlShipment->isGlobalShipment();
    }
    
    /**
     * Gets the default product option for a shipment
     * 
     * @param Mage_Sales_Model_Order_Shipment
     * 
     * @return string
     */
    public function getDefaultProductOptionForShipment($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->setShipment($shipment);
        
        $productOption = $postnlShipment->getDefaultProductCode();
        
        return $productOption;
    }
    
    /**
     * Get an array of all default product options. This is a simple method to quickly get a list of default options based on
     * the current storeview.
     * 
     * This does not take into account the possible use of an alternative default for dutch shipments. For that you need to use
     * TIG_PostNL_Model_Core_Shipment::getDefaultProductCode() which is more precise.
     * 
     * @return array
     */
    public function getDefaultProductOptions()
    {
        $storeId = Mage::app()->getStore()->getId();
        
        $defaultDutchOption  = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STANDARD_PRODUCT_OPTION, $storeId);
        $defaultEuOption     = Mage::getStoreConfig(self::XML_PATH_DEFAULT_EU_PRODUCT_OPTION, $storeId);
        $defaultGlobalOption = Mage::getStoreConfig(self::XML_PATH_DEFAULT_GLOBAL_PRODUCT_OPTION, $storeId);
        
        $defaultOptions = array(
            'dutch'  => $defaultDutchOption,
            'eu'     => $defaultEuOption,
            'global' => $defaultGlobalOption,
        );
        
        return $defaultOptions;
    }
    
    /**
     * Gets the number of parcels in this shipment based on it's weight
     * 
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * 
     * @return int
     */
    public function getParcelCount($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment');
        $postnlShipment->setShipment($shipment);
        
        /**
         * Only NL shipments support multi-colli shipments
         */
        if (!$postnlShipment->isDutchShipment()) {
            return 1;
        }
        
        /**
         * get this shipment's total weight
         */
        $weight = $postnlShipment->getTotalWeight(true);
        
        /**
         * get the weight per parcel
         */
        $weightPerParcel = Mage::getStoreConfig(self::XML_PATH_WEIGHT_PER_PARCEL, $shipment->getStoreId());
        $weightPerParcel = $this->standardizeWeight($weightPerParcel, $shipment->getStoreId());
        
        /**
         * calculate the number of parcels needed to ship the total weight of this shipment
         */
        $parcelCount = ceil($weight / $weightPerParcel);
        
        return $parcelCount;
    }
    
    /**
     * Checks if a given postnl shipment exists using Zend_Validate_Db_RecordExists.
     * 
     * @param string $shipmentId
     * 
     * @return boolean
     * 
     * @see Zend_Validate_Db_RecordExists
     * 
     * @link http://framework.zend.com/manual/1.12/en/zend.validate.set.html#zend.validate.Db
     */
    public function postnlShipmentExists($shipmentId)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readAdapter = $coreResource->getConnection('core_read');
        
        $validator = Mage::getModel('Zend_Validate_Db_RecordExists', 
            array(
                'table'   => $coreResource->getTableName('postnl_core/shipment'),
                'field'   => 'shipment_id',
                'adapter' => $readAdapter,
            )
        );
        
        $postnlShipmentExists = $validator->isValid($shipmentId);
        
        if ($postnlShipmentExists) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks if a given barcode exists using Zend_Validate_Db_RecordExists.
     * 
     * @param string $barcode
     * 
     * @return boolean
     * 
     * @see Zend_Validate_Db_RecordExists
     * 
     * @link http://framework.zend.com/manual/1.12/en/zend.validate.set.html#zend.validate.Db
     */
    public function barcodeExists($barcode)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $readAdapter = $coreResource->getConnection('core_read');
        
        /**
         * Check if the barcode exists as a main barcode
         */
        $validator = Mage::getModel('Zend_Validate_Db_RecordExists', 
            array(
                'table'   => $coreResource->getTableName('postnl_core/shipment'),
                'field'   => 'main_barcode',
                'adapter' => $readAdapter,
            )
        );
        
        $barcodeExists = $validator->isValid($barcode);
        
        if ($barcodeExists) {
            return true;
        }
        
        /**
         * Check if the barcode exists as a secondary barcode
         */
        $validator = Mage::getModel('Zend_Validate_Db_RecordExists', 
            array(
                'table'   => $coreResource->getTableName('postnl_core/shipment_barcode'),
                'field'   => 'barcode',
                'adapter' => $readAdapter,
            )
        );
        
        $barcodeExists = $validator->isValid($barcode);
        
        if ($barcodeExists) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Convert a given weight to kilogram or gram
     * 
     * @param float $weight The weight to be converted
     * @param int | null $storeId Store Id used to determine the weight unit that was originally used
     * @param boolean $toGram Optional parameter to convert to gram instead of kilogram
     * 
     * @return float
     */
    public function standardizeWeight($weight, $storeId = null, $toGram = false)
    {
        if (is_null($storeId)) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
        
        $unitUsed = Mage::getStoreConfig(self::XML_PATH_WEIGHT_UNIT, $storeId);
        
        $returnWeight = $weight;
        switch ($unitUsed) {
            case 'tonne':
                $returnWeight = $weight * 1000;
                break;
            case 'kilogram':
                $returnWeight = $weight * 1;
                break;
            case 'hectogram':
                $returnWeight = $weight * 10;
                break;
            case 'gram':
                $returnWeight = $weight * 0.001;
                break;
            case 'carat':
                $returnWeight = $weight * 0.0002;
                break;
            case 'centigram':
                $returnWeight = $weight * 0.00001;
                break;
            case 'milligram':
                $returnWeight = $weight * 0.000001;
                break;
            case 'longton':
                $returnWeight = $weight * 1016.0469088;
                break;
            case 'shortton':
                $returnWeight = $weight * 907.18474;
                break;
            case 'longhundredweight':
                $returnWeight = $weight * 50.80234544;
                break;
            case 'shorthundredweight':
                $returnWeight = $weight * 45.359237;
                break;
            case 'stone':
                $returnWeight = $weight * 6.35029318;
                break;
            case 'pound':
                $returnWeight = $weight * 0.45359237;
                break;
            case 'shortton':
                $returnWeight = $weight * 907;
                break;
            case 'ounce':
                $returnWeight = $weight * 0.028349523125;
                break;
            case 'grain': //no break
            case 'troy_grain':
                $returnWeight = $weight * 0.00006479891;
                break;
            case 'troy_pound':
                $returnWeight = $weight * 0.3732417216;
                break;
            case 'troy_ounce':
                $returnWeight = $weight * 0.0311034768;
                break;
            case 'troy_pennyweight':
                $returnWeight = $weight * 0.00155517384;
                break;
            case 'troy_carat':
                $returnWeight = $weight * 0.00020519654;
                break;
            case 'troy_mite':
                $returnWeight = $weight * 0.00000323994;
                break;
            default:
                $returnWeight = $weight;
                break;
        }

        if ($toGram === true) {
            $returnWeight *= 1000;
        }
        
        return $returnWeight;
    }
    
    /**
     * Logs a CIF request and response for debug purposes.
     * 
     * N.B.: if file logging is enabled, the log will be forced
     * 
     * @param SoapClient $client
     * 
     * @return TIG_PostNL_Helper_Cif
     * 
     * @see Mage::log()
     * 
     * @todo add additional debug options
     * 
     */
    public function logCifCall($client)
    {
        if (!$this->isLoggingEnabled()) { 
            return $this;
        }
        
        $requestXml = $this->formatXml($client->getLastRequest());
        $responseXML = $this->formatXml($client->getLastResponse());
        
        $logMessage = "Request sent:\n"
                    . $requestXml
                    . "\nResponse received:\n"
                    . $responseXML;
        
        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CIF_DEBUG_LOG_FILE;
        $this->log($logMessage, Zend_Log::DEBUG, $file);
        
        return $this;
    }
    
    /**
     * Logs a CIF exception in the database and/or a log file
     * 
     * N.B.: if file logging is enabled, the log will be forced
     * 
     * @param Mage_Core_Exception | TIG_PostNL_Model_Core_Cif_Exception $exception
     * 
     * @return TIG_PostNL_Helper_Cif
     * 
     * @see Mage::logException()
     * 
     * @todo add additional debug options
     */
    public function logCifException($exception)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }
        
        if ($exception instanceof TIG_PostNL_Model_Core_Cif_Exception) {
            $requestXml = $this->formatXml($exception->getRequestXml());
            $responseXML = $this->formatXml($exception->getResponseXml());
            
            $logMessage = '';
            
            $errorNumbers = $exception->getErrorNumbers();
            if (!empty($errorNumbers)) {
                $errorNumbers = implode(', ', $errorNumbers);
                $logMessage .= "Error numbers received: {$errorNumbers}\n";
            }
            
            $logMessage .= "<<< REQUEST SENT >>>\n"
                        . $requestXml
                        . "\n<<< RESPONSE RECEIVED >>>\n"
                        . $responseXML;
        } else {
            $logMessage = "\n" . $exception->__toString();
        }
        
        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CIF_EXCEPTION_LOG_FILE;
        $this->log($logMessage, Zend_Log::ERR, $file, false, true);
        
        return $this;
    }
}
