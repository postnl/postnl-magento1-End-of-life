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
class TIG_PostNL_Helper_Cif extends Mage_Core_Helper_Abstract
{
    /**
     * Log filename to log all CIF exceptions
     */
    const CIF_EXCEPTION_LOG_FILE = 'TIG_PostNL_CIF_Exception.log';
    
    /**
     * dutch country code
     */
    const DUTCH_COUNTRY_CODE = 'NL';
    
    /**
     * available barcode types
     */
    const DUTCH_BARCODE_TYPE  = 'NL';
    const EU_BARCODE_TYPE     = 'EU';
    const GLOBAL_BARCODE_TYPE = 'CD';
    
    /**
     * xml path to eu countries setting
     */
    const XML_PATH_EU_COUNTRIES = 'general/country/eu_countries';
    
    /**
     * Checks which barcode type is applicable for this shipment
     * 
     * Possible return values:
     * - NL
     * - EU
     * - CD (global)
     * 
     * @var Mage_Sales_Model_Order_Shipment
     * 
     * @return string
     */
    public function getBarcodeTypeForShipment($shipment)
    {
        $shippingDestination = $shipment->getShippingAddress()->getCountry();
        
        if ($shippingDestination == self::DUTCH_COUNTRY_CODE) {
            $barcodeType = self::DUTCH_BARCODE_TYPE;
            return $barcodeType;
        }
        
        $euCountries = Mage::getStoreConfig(XML_PATH_EU_COUNTRIES, $shipment->getStoreId());
        $euCountriesArray = explode(',', $euCountries);
        
        if (in_array($shippingDestination, $euCountriesArray)) {
            $barcodeType = self::EU_BARCODE_TYPE;
            return $barcodeType;
        }
        
        $barcodeType = self::GLOBAL_BARCODE_TYPE;
        return $barcodeType;
    }
    
    /**
     * formats input XML string to improve readability
     * 
     * @param string $xml
     * 
     * @return string
     */
    public function formatXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
    
    /**
     * Logs a CIF exception in the database and/or a log file
     * 
     * N.B.: if file logging is enabled, the log will be forced
     * 
     * @param Mage_Core_Exception | TIG_PostNL_Model_Core_Cif_Exception $exception
     * 
     * @return TIG_PostNL_Helper_Cif
     */
    public function logCifException($exception)
    {
        if (true) { //@TODO: replace by configuration value check
            if ($exception instanceof TIG_PostNL_Model_Core_Cif_Exception) {
                Mage::log("\nRequest:\n" . $this->formatXml($exception->getRequestXml()), Zend_Log::DEBUG, self::CIF_EXCEPTION_LOG_FILE, true);
                Mage::log("\nResponse:\n" . $this->formatXml($exception->getResponseXml()), Zend_Log::DEBUG, self::CIF_EXCEPTION_LOG_FILE, true);
            }
            
            Mage::log("\n" . $exception->__toString(), Zend_Log::ERR, self::CIF_EXCEPTION_LOG_FILE, true);
        }
        
        return $this;
    }
}
