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
class TIG_PostNL_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Log filename to log all non-specific PostNL exceptions
     */
    const POSTNL_EXCEPTION_LOG_FILE = 'TIG_PostNL_Exception.log';
    
    /**
     * Log filename to log all non-specific PostNL debug messages
     */
    const POSTNL_DEBUG_LOG_FILE = 'TIG_PostNL_Debug.log';
    
    /**
     * Directory inside var/log where PostNL log files will be logged
     */
    const POSTNL_LOG_DIRECTORY = 'TIG_PostNL';
    
    /**
     * Log filename to log all cron log messages
     */
    const POSTNL_CRON_DEBUG_LOG_FILE = 'TIG_PostNL_Cron_Debug.log';
    
    /**
     * XML path to postnl general active/inactive setting
     */
    const XML_PATH_EXTENSION_ACTIVE = 'postnl/general/active';
    
    /**
     * XML path to postnl carier active/inactive setting
     */
    const XML_PATH_CARRIER_ACTIVE = 'carriers/postnl/active';
    
    /**
     * XML path to test/live mode config option
     */
    const XML_PATH_TEST_MODE = 'postnl/cif_labels_and_confirming/mode';
    
    /**
     * XML path to debug mode config option
     */
    const XML_PATH_DEBUG_MODE = 'postnl/advanced/debug_mode';
    
    /**
     * XML path to 'is_activated' flag
     */
    const XML_PATH_IS_ACTIVATED = 'postnl/general/is_activated';
    
    /**
     * Required configuration fields
     * 
     * @var array
     */
    protected $_requiredFields = array(
        'postnl/cif/customer_code',
        'postnl/cif/customer_number',
        'postnl/cif/collection_location',
        'postnl/cif_labels_and_confirming/label_size',
        'postnl/cif_sender_address/firstname',
        'postnl/cif_sender_address/lastname',
        'postnl/cif_sender_address/streetname',
        'postnl/cif_sender_address/housenumber',
        'postnl/cif_sender_address/postcode',
        'postnl/cif_sender_address/city',
    );
    
    /**
     * Required configuration fields for live mode
     * 
     * @var array
     */
    protected $_liveModeRequiredFields = array(
        'postnl/cif/live_username',
        'postnl/cif/live_password',
    );
    
    /**
     * Required configuration fields for test mode
     * 
     * @var array
     */
    protected $_testModeRequiredFields = array(
        'postnl/cif/test_username',
        'postnl/cif/test_password',
    );
    
    /**
     * Required configuration fields when using global shipments
     * 
     * @var array
     */
    protected $_globalShipmentRequiredFields = array(
        'postnl/cif/global_barcode_type',
        'postnl/cif/global_barcode_range',
        'postnl/cif_globalpack_settings/customs_value_attribute',
        'postnl/cif_globalpack_settings/country_of_origin_attribute',
        'postnl/cif_globalpack_settings/description_attribute',
    );
    
    /**
     * Get required fields array
     * 
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->_requiredFields;
    }
    
    /**
     * Get required fields for live mode array
     * 
     * @return array
     */
    public function getLiveModeRequiredFields()
    {
        return $this->_liveModeRequiredFields;
    }
    
    /**
     * Get required fields for test mode array
     * 
     * @return array
     */
    public function getTestModeRequiredFields()
    {
        return $this->_testModeRequiredFields;
    }
    
    /**
     * Get required fields for global shipments array
     * 
     * @return array
     */
    public function getGlobalShipmentsRequiredFields()
    {
        return $this->_globalShipmentRequiredFields;
    }
    
    /**
     * Get debug mode config setting
     * 
     * @return int
     */
    public function getDebugMode()
    {
        if (Mage::registry('postnl_debug_mode') !== null) {
            return Mage::registry('postnl_debug_mode');
        }
        
        $debugMode = (int) Mage::getStoreConfig(self::XML_PATH_DEBUG_MODE, Mage_Core_Model_App::ADMIN_STORE_ID);
        
        Mage::register('postnl_debug_mode', $debugMode);
        return $debugMode;
    }

    /**
     * Checks to see if the module may ship to the Netherlands using PostNL standard shipments
     * 
     * @param boolean|int $storeId
     * 
     * @return boolean
     */
    public function canUseStandard($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $standardProductOptions = Mage::getModel('postnl_core/system_config_source_standardProductOptions')
                                      ->getAvailableOptions($storeId);
        if (empty($standardProductOptions)) {
            return false;
        }
        
        return true;
    }

    /**
     * Checks to see if the module may ship using PakjeGemak
     * 
     * @param boolean|int $storeId
     * 
     * @return boolean
     */
    public function canUsePakjeGemak($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $pakjeGemakProductoptions = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions')
                                        ->getAvailableOptions($storeId);
                                        
        if (empty($pakjeGemakProductoptions)) {
            return false;
        }
        
        return true;
    }

    /**
     * Checks to see if the module may ship to EU countries using EPS
     * 
     * @param boolean|int $storeId
     * 
     * @return boolean
     */
    public function canUseEps($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $euProductOptions = Mage::getModel('postnl_core/system_config_source_euProductOptions')
                                ->getAvailableOptions($storeId); 
        
        if (empty($euProductOptions)) {
            return false;
        }
        
        return true;
    }

    /**
     * Checks to see if the module may ship to countries outside the EU using GlobalPack
     * 
     * @param boolean|int $storeId
     * 
     * @return boolean
     */
    public function canUseGlobalPack($storeId = false)
    {
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $globalProductOptions = Mage::getModel('postnl_core/system_config_source_globalProductOptions')
                                    ->getAvailableOptions($storeId);
                                    
        if (empty($globalProductOptions)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if the module is set to test mode
     * 
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        if (Mage::registry('postnl_test_mode') !== null) {
            return Mage::registry('postnl_test_mode');
        }
        
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $testMode = Mage::getStoreConfigFlag(self::XML_PATH_TEST_MODE, $storeId);
        
        Mage::register('postnl_test_mode', $testMode);
        return $testMode;
    }
    
    /**
     * Alias for isEnabled()
     * 
     * @param int | bool $storeId
     * @param boolean $checkGlobal
     * @param null|boolean $forceTestMode
     * 
     * @return boolean
     * 
     * @see TIG_PostNL_Helper_Data::isEnabled()
     */
    public function isActive($storeId = false, $checkGlobal = false, $forceTestMode = null)
    {
        return $this->isEnabled($storeId = false, $checkGlobal = false, $forceTestMode = null);
    }
    
    /**
     * Determines if the extension is active
     * 
     * @param int | bool $storeId
     * @param boolean $checkGlobal
     * @param null|boolean $forceTestMode
     * 
     * @return boolean
     */
    public function isEnabled($storeId = false, $checkGlobal = false, $forceTestMode = null)
    {
        if ($forceTestMode === null) {
            $testMode = $this->isTestMode();
        } else {
            $testMode = $forceTestMode;
        }
        
        $registryKey = 'postnl_enabled';
        if ($checkGlobal) {
            $registryKey .= '_global';
        }
        if ($testMode) {
            $registryKey .= '_test';
        }
        
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }
        
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
        
        /**
         * Check if the module has been enabled
         */
        $enabled = Mage::getStoreConfigFlag(self::XML_PATH_EXTENSION_ACTIVE, $storeId);
        if ($enabled === false) {
            Mage::register($registryKey, false);
            
            $errors = array(
                $this->__('You have not yet enabled the extension.')
            );
            
            Mage::register($registryKey . '_errors', $errors);
            return false;
        }
        
        /**
         * Check if the PostNL shipping method is active
         */
        $postnlShippingMethodEnabled = Mage::getStoreConfigFlag(self::XML_PATH_CARRIER_ACTIVE, $storeId);
        if ($postnlShippingMethodEnabled === false) {
            Mage::register($registryKey, false);
            
            $errors = array(
                $this->__('The PostNL shipping method has not been enabled.')
            );
            Mage::register($registryKey . '_errors', $errors);
            return false;
        }
        
        /**
         * The PostNL module only works with EUR as the shop's base currency
         */
        $baseCurrencyCode = Mage::getModel('core/store')->load($storeId)->getBaseCurrencyCode();
        if ($baseCurrencyCode != 'EUR') {
            Mage::register($registryKey, false);
            
            $errors = array(
                $this->__("The shop's base currency code must be set to EUR for PostNL to function.")
            );
            Mage::register($registryKey . '_errors', $errors);
            return false;
        }
        
        /**
         * Check if the module's required configuration options have been filled
         */
        $isConfigured = $this->isConfigured($storeId, $checkGlobal, $forceTestMode);
        if ($isConfigured === false) {
            Mage::register($registryKey, false);
            return false;
        }
        
        Mage::register($registryKey, true);
        return true;
    }
    
    /**
     * Check if the modules has been confgured.
     * The required fields will only be checked to see if they're not empty. The values entered will not be validated
     * 
     * @param int | boolean $storeId
     * @param boolean $checkGlobal
     * @param null|boolean $forceTestMode
     * 
     * @return boolean
     * 
     * @todo properly implement global check
     */
    public function isConfigured($storeId = false, $checkGlobal = false, $forceTestMode = null)
    {
        if ($forceTestMode === null) {
            $testMode = $this->isTestMode();
        } else {
            $testMode = $forceTestMode;
        }
        
        $registryKey = 'postnl_is_configured';
        if ($checkGlobal) {
            $registryKey .= '_global';
        }
        if ($testMode) {
            $registryKey .= '_test';
        }
        
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }
        
        $errors = array();
        
        /**
         * Check if the module has been activated
         */
        $isActivated = Mage::getStoreConfig(self::XML_PATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($isActivated != 2) {
            $errors[] = $this->__('The extension has not been activated.');
        }
        
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        /**
         * Get the bse required fields. These are always required.
         */
        $baseFields = $this->getRequiredFields();
        
        /**
         * Get either the live mode or test mode required fields
         */
        if ($testMode) {
            $modeFields = $this->getTestModeRequiredFields();
        } else {
            $modeFields = $this->getLiveModeRequiredFields();
        }
        $requiredFields = array_merge($baseFields, $modeFields);
        
        /**
         * If this check pertains to a global shipment, get the global shipments required fields as well
         */
        if ($checkGlobal !== false) {
            $globalFields = $this->getGlobalShipmentsRequiredFields();
            $requiredFields = array_merge($requiredFields, $globalFields);
        }
        
        /**
         * Check if each required field is filled. If not add the field's label to an array of missing fields so we can later
         * inform the merchant which fields exactly are missing.
         */
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections     = $configFields->getSections('postnl');
        $section      = $sections->postnl;
        foreach ($requiredFields as $requiredField) {
            $value = Mage::getStoreConfig($requiredField, $storeId);
            
            if ($value === null || $value === '') {
                $fieldParts = explode('/', $requiredField);
                $field = $fieldParts[2];
                $group = $fieldParts[1];
                
                $label = $section->groups->$group->fields->$field->label;
                $errors[] = $this->__('%s is required.', $label);
            }
        }
        
        /**
         * If any errors were detected, add them to the registry and return false
         */
        if (!empty($errors)) {
            Mage::register($registryKey, false);
            Mage::register($registryKey . '_errors', $errors);
            return false;
        }
        
        Mage::register($registryKey, true);
        return true;
    }
    
    /**
     * Check if debug logging is enabled
     * 
     * @return boolean
     */
    public function isLoggingEnabled()
    {
        $debugMode = $this->getDebugMode();
        if ($debugMode > 1) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if exception logging is enabled
     * 
     * @return boolean
     */
    public function isExceptionLoggingEnabled()
    {
        $debugMode = $this->getDebugMode();
        if ($debugMode > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns path to specified directory for specified module.
     * 
     * Based on Mage_Core_Model_Config::getModuleDir()
     * 
     * @param string $dir The directory in question
     * @param string $module The module for which the directory is needed
     * 
     * @return string
     * 
     * @see Mage_Core_Model_Config::getModuleDir()
     */
    public function getModuleDir($dir, $moduleName = 'TIG_PostNL')
    {
        $config = Mage::app()->getConfig();
        
        $codePool = (string)$config->getModuleConfig($moduleName)->codePool;
        $path = $config->getOptions()->getCodeDir()
              . DS
              . $codePool
              . DS
              . uc_words($moduleName, DS);

        $path .= DS . $dir;

        $path = str_replace('/', DS, $path);
        
        return $path;
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
        if (empty($xml)) {
            return '';
        }
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
    
    /**
     * Logs a debug message. Based on Mage::log
     * 
     * @param string $message
     * @param int | null $level
     * @param string | null $file
     * 
     * @return TIG_PostNL_Helper_Data
     * 
     * @see Mage::log
     */
    public function log($message, $level = null, $file = null)
    {
        if (!$this->isLoggingEnabled()) {
            return $this;
        }
        
        if (is_null($level)) {
            $level = Zend_Log::DEBUG;
        }
        
        if (is_null($file)) {
            $file = self::POSTNL_LOG_DIRECTORY . DS . self::POSTNL_DEBUG_LOG_FILE;
        }
        
        $this->createLogDir();
        
        Mage::log($message, $level, $file);
        
        return $this;
    }
    
    /**
     * Logs a cron debug messageto a seperate file in order to differentiate it from other debug messages
     * 
     * @param string $message
     * @param int | int $level
     * 
     * @return TIG_PostNL_Helper_Data
     * 
     * @see Mage::log
     */
    public function cronLog($message, $level = null)
    {
        $file = self::POSTNL_LOG_DIRECTORY . DS . self::POSTNL_CRON_DEBUG_LOG_FILE;
        
        return $this->log($message, $level, $file);
    }
    
    /**
     * Logs a PostNL Exception. Based on Mage::logException
     * 
     * N.B. this uses forced logging
     * 
     * @param Exception $exception
     * 
     * @return TIG_PostNL_Helper_Data
     * 
     * @see Mage::logException
     */
    public function logException($exception)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }
        
        $this->createLogDir();
        
        Mage::log(
            "\n" . $exception->__toString(), 
            Zend_Log::ERR, 
            self::POSTNL_LOG_DIRECTORY . DS . self::POSTNL_EXCEPTION_LOG_FILE, 
            true
        );
        
        return $this;
    }
    
    /**
     * Checks if the current edition of Magento is enterprise. Uses Mage::getEdition if available or version_compare if it is not
     * 
     * @return boolean
     * 
     * @throws TIG_PostNL_Exception
     */
    public function isEnterprise()
    {
        /**
         * Use Mage::getEdition, which is available since CE 1.7 and EE 1.12
         */
        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
            if ($edition == Mage::EDITION_ENTERPRISE) {
                return true;
            }
            
            if ($edition == Mage::EDITION_COMMUNITY) {
                return false;
            }
            
            /**
             * If the edition is not community or enterprise, it is not supported
             */
            throw Mage::exception('TIG_PostNL', 'Invalid Magento edition detected: ' . $edition);
        }
        
        /**
         * Do a version check instead
         */
        $version = Mage::getVersion();
        if (version_compare($version, '1.9.0.0', '>=')) { //1.9.0.0 was the first Magento Enterprise version
            return true;
        }
        
        return false;
    }
    
    /**
     * Creates a seperate dir to log PostNL log files. Does nothing if the dir already exists
     * 
     * @return TIG_PostNL_Exception
     */
    public function createLogDir()
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS . self::POSTNL_LOG_DIRECTORY;

        if (!is_dir($logDir)) {
            mkdir($logDir);
            chmod($logDir, 0777);
        }
        
        return $this;
    }
}
