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
 */
class TIG_PostNL_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Log filename to log all non-specific PostNL exceptions.
     */
    const POSTNL_EXCEPTION_LOG_FILE = 'TIG_PostNL_Exception.log';

    /**
     * Log filename to log all non-specific PostNL debug messages.
     */
    const POSTNL_DEBUG_LOG_FILE = 'TIG_PostNL_Debug.log';

    /**
     * Directory inside var/log where PostNL log files will be logged.
     */
    const POSTNL_LOG_DIRECTORY = 'TIG_PostNL';

    /**
     * Log filename to log all cron log messages.
     */
    const POSTNL_CRON_DEBUG_LOG_FILE = 'TIG_PostNL_Cron_Debug.log';

    /**
     * XML path to postnl mode setting.
     */
    const XPATH_EXTENSION_MODE = 'postnl/cif/mode';

    /**
     * XML path to debug mode config option.
     */
    const XPATH_DEBUG_MODE = 'postnl/advanced/debug_mode';

    /**
     * XML path to 'is_activated' flag.
     */
    const XPATH_IS_ACTIVATED = 'postnl/general/is_activated';

    /**
     * XML path to 'show_error_details_in_frontend' flag.
     */
    const XPATH_SHOW_ERROR_DETAILS_IN_FRONTEND = 'postnl/advanced/show_error_details_in_frontend';

    /**
     * XML path to use_globalpack setting.
     */
    const XPATH_USE_GLOBALPACK = 'postnl/cif_globalpack_settings/use_globalpack';

    /**
     * Xpath to use_buspakje setting.
     */
    const XPATH_USE_BUSPAKJE = 'postnl/delivery_options/use_buspakje';

    /**
     * XPATH to allow EPS BE only product option setting.
     */
    const XPATH_ALLOW_EPS_BE_ONLY_OPTION = 'postnl/cif_product_options/allow_eps_be_only_options';

    /**
     * XPATH to the allow Pakjegemak not insured setting.
     */
    const XPATH_ALLOW_PAKJEGEMAK_NOT_INSURED = 'postnl/cif_product_options/allow_pakjegemak_not_insured';

    /**
     * XML path to weight unit used
     */
    const XPATH_WEIGHT_UNIT = 'postnl/packing_slip/weight_unit';

    /**
     * Xpath to the buspakje calculation mode setting.
     */
    const XPATH_BUSPAKJE_CALC_MODE = 'postnl/delivery_options/buspakje_calculation_mode';

    /**
     * Minimum PHP version required by this extension.
     */
    const MIN_PHP_VERSION = '5.3.0';

    /**
     * Xpath to the changelog URL.
     */
    const CHANGELOG_URL_XPATH = 'postnl/general/changelog_url';

    /**
     * Logging levels supported by this extension.
     */
    const LOGGING_EXCEPTION_ONLY = 1;
    const LOGGING_FULL           = 2;

    /**
     * Maximum weight for letter box parcels (in kilograms).
     */
    const MAX_LETTER_BOX_PARCEL_WEIGHT = 2;

    /**
     * Maximum weight for letter box parcels (in kilograms).
     */
    const MAX_LETTER_BOX_PARCEL_QTY_RATIO = 1;

    /**
     * Value the 'is_activated' setting must achieve for the extension to be considered 'activated'.
     */
    const EXTENSION_ACTIVE = 2;

    /**
     * Buspakje calculation modes.
     */
    const BUSPAKJE_CALCULATION_MODE_AUTOMATIC = 'automatic';
    const BUSPAKJE_CALCULATION_MODE_MANUAL    = 'manual';

    /**
     * Xpaths to return label settings.
     */
    const XPATH_RETURN_LABELS_ACTIVE                     = 'postnl/returns/return_labels_active';
    const XPATH_FREEPOST_NUMBER                          = 'postnl/returns/return_freepost_number';
    const XPATH_CUSTOMER_PRINT_LABEL                     = 'postnl/returns/customer_print_label';
    const XPATH_GUEST_PRINT_LABEL                        = 'postnl/returns/guest_print_label';
    const XPATH_PRINT_RETURN_LABELS_WITH_SHIPPING_LABELS = 'postnl/returns/print_return_and_shipping_label';

    /**
     * Xpath to the sender country setting.
     */
    const XPATH_SENDER_COUNTRY = 'postnl/cif_address/country';

    /**
     * Xpath to the used checkout extension
     */
    const XPATH_CHECKOUT_EXTENSION = 'postnl/cif_labels_and_confirming/checkout_extension';

    /**
     * Required configuration fields.
     *
     * @var array
     */
    protected $_requiredFields = array(
        'postnl/cif/customer_code',
        'postnl/cif/customer_number',
        'postnl/cif/collection_location',
        'postnl/cif_labels_and_confirming/label_size',
        array(
            'postnl/cif_address/lastname',
            'postnl/cif_address/company',
        ),
        'postnl/cif_address/streetname',
        'postnl/cif_address/housenumber',
        'postnl/cif_address/postcode',
        'postnl/cif_address/city',
    );

    /**
     * Required configuration fields for live mode.
     *
     * @var array
     */
    protected $_liveModeRequiredFields = array(
        'postnl/cif/live_username',
        'postnl/cif/live_password',
    );

    /**
     * Required configuration fields for test mode.
     *
     * @var array
     */
    protected $_testModeRequiredFields = array(
        'postnl/cif/test_username',
        'postnl/cif/test_password',
    );

    /**
     * Required configuration fields when using global shipments.
     *
     * @var array
     */
    protected $_globalShipmentRequiredFields = array(
        'postnl/cif_globalpack_settings/use_globalpack',
        'postnl/cif_globalpack_settings/global_barcode_type',
        'postnl/cif_globalpack_settings/global_barcode_range',
        'postnl/cif_globalpack_settings/customs_value_attribute',
        'postnl/cif_globalpack_settings/country_of_origin_attribute',
        'postnl/cif_globalpack_settings/description_attribute',
    );

    /**
     * Array of possible log files created by the PostNL extension.
     *
     * @var array
     */
    protected $_logFiles = array(
        'TIG_PostNL_Cendris_Debug.log',
        'TIG_PostNL_Cendris_Exception.log',
        'TIG_PostNL_Checkout_Debug.log',
        'TIG_PostNL_CIF_Debug.log',
        'TIG_PostNL_CIF_Exception.log',
        'TIG_PostNL_Cron_Debug.log',
        'TIG_PostNL_Debug.log',
        'TIG_PostNL_Exception.log',
        'TIG_PostNL_MijnPakket_Debug.log',
        'TIG_PostNL_Payment_Debug.log',
        'TIG_PostNL_Webservices_Debug.log',
        'TIG_PostNL_Webservices_Exception.log',
    );

    /**
     * For certain product codes a custom barcode is required.
     *
     * @var array
     */
    protected $_customBarcodes = array(
        '2828' => '3STFGG000000000'
    );

    /**
     * @var null|boolean|TIG_PostNL_Model_Core_Cache
     */
    protected $_cache = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * The current server's memory limit.
     *
     * @var int
     */
    protected $_memoryLimit;

    /**
     * The URL of the PostNL change log.
     *
     * @var string
     */
    protected $_changelogUrl;

    /**
     * @var string[]
     */
    protected $_storeTimeZones;

    /**
     * @var string
     */
    protected $_domesticCountry;

    /**
     * Get required fields array.
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->_requiredFields;
    }

    /**
     * Get required fields for live mode array.
     *
     * @return array
     */
    public function getLiveModeRequiredFields()
    {
        return $this->_liveModeRequiredFields;
    }

    /**
     * Get required fields for test mode array.
     *
     * @return array
     */
    public function getTestModeRequiredFields()
    {
        return $this->_testModeRequiredFields;
    }

    /**
     * Get required fields for global shipments array.
     *
     * @return array
     */
    public function getGlobalShipmentsRequiredFields()
    {
        return $this->_globalShipmentRequiredFields;
    }

    /**
     * @return string
     */
    public function getChangelogUrl()
    {
        if ($this->_changelogUrl) {
            return $this->_changelogUrl;
        }

        $changelogUrl = Mage::getStoreConfig(self::CHANGELOG_URL_XPATH, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->_changelogUrl = $changelogUrl;
        return $changelogUrl;
    }

    /**
     * @param null|boolean|TIG_PostNL_Model_Core_Cache $cache
     *
     * @return TIG_PostNL_Helper_Data
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;

        return $this;
    }

    /**
     * Gets the cache if it's been set. If the cache is null, it means the cache had not been defined yet. In this case
     * we instantiate the cache model. If the cache is active, the _cache variable will be set with the cache instance.
     * Otherwise the _cache variable will be false.
     *
     * @return null|boolean|TIG_PostNL_Model_Core_Cache
     */
    public function getCache()
    {
        if ($this->_cache !== null) {
            return $this->_cache;
        }

        /** @var TIG_PostNL_Model_Core_Cache $cache */
        $cache = Mage::getSingleton('postnl_core/cache');
        if (!$cache->canUseCache()) {
            $cache = false;
        }

        $this->setCache($cache);
        return $cache;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote) {
            return $this->_quote;
        }

        /** @var Mage_Checkout_Model_Session $session */
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $this->_quote = $quote;
        return $quote;
    }

    /**
     * @return array
     */
    public function getLogFiles()
    {
        return $this->_logFiles;
    }

    /**
     * Gets the current memory limit in bytes.
     *
     * @return int
     */
    public function getMemoryLimit()
    {
        if ($this->_memoryLimit) {
            return $this->_memoryLimit;
        }

        $memoryLimit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
            if (!isset($matches[1])) {
                $memoryLimit = (int) $memoryLimit;
            } elseif (!isset($matches[2])) {
                $memoryLimit = $matches[1];
            } elseif ($matches[2] == 'G' || $matches[2] == 'g') {
                $memoryLimit = $matches[1] * 1024 * 1024 * 1024;
            } elseif ($matches[2] == 'M' || $matches[2] == 'm') {
                $memoryLimit = $matches[1] * 1024 * 1024;
            } elseif ($matches[2] == 'K' || $matches[2] == 'k') {
                $memoryLimit = $matches[1] * 1024;
            }
        } else {
            $memoryLimit = (int) $memoryLimit;
        }

        $this->setMemoryLimit($memoryLimit);
        return $memoryLimit;
    }

    /**
     * @param int $memoryLimit
     *
     * @return $this
     */
    public function setMemoryLimit($memoryLimit)
    {
        $this->_memoryLimit = $memoryLimit;

        return $this;
    }

    /**
     * get an array of product codes which use a custom barcode.
     *
     * @return array
     */
    public function getCustomBarcodes()
    {
        return $this->_customBarcodes;
    }

    /**
     * @return string[]
     */
    public function getStoreTimeZones()
    {
        /**
         * Get the stored store time zones.
         */
        $storeTimeZones = $this->_storeTimeZones;

        /**
         * If no store time zones are stored, try to get them from the PostNL cache.
         */
        if (is_null($storeTimeZones) && $this->getCache()) {
            $storeTimeZones = $this->getCache()->getStoreTimeZones();

            if (is_array($storeTimeZones)) {
                $this->_storeTimeZones = $storeTimeZones;
            } else {
                $this->_storeTimeZones = array();
            }
        } elseif (is_null($storeTimeZones)) {
            $this->_storeTimeZones = array();
        }

        return $this->_storeTimeZones;
    }

    /**
     * Get an array of country codes considered to be 'domestic'.
     *
     * @return string
     */
    public function getDomesticCountry()
    {
        $domesticCountry = $this->_domesticCountry;

        if (!empty($domesticCountry)) {
            return $domesticCountry;
        }

        /**
         * Try to get the domestic country array from the cache.
         */
        $cache = $this->getCache();
        if ($cache && $cache->hasDomesticCountry()) {
            $domesticCountry = $cache->getDomesticCountry();

            $this->setDomesticCountry($cache->getDomesticCountry());
            return $domesticCountry;
        }

        /**
         * The domestic country array contains the selected sender address country.
         */
        $domesticCountry = Mage::getStoreConfig(self::XPATH_SENDER_COUNTRY, Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->setDomesticCountry($domesticCountry);

        /**
         * Attempt to save the array to the PostNL cache.
         */
        if ($cache) {
            $cache->setDomesticCountry($domesticCountry)
                  ->saveCache();
        }

        return $domesticCountry;
    }

    /**
     * @param array $domesticCountries
     *
     * @return $this
     */
    public function setDomesticCountry($domesticCountries)
    {
        $this->_domesticCountry = $domesticCountries;

        return $this;
    }

    /**
     * @param string[] $storeTimeZones
     *
     * @return $this
     */
    public function setStoreTimeZones(array $storeTimeZones)
    {
        $this->_storeTimeZones = $storeTimeZones;

        return $this;
    }

    /**
     * Get the time zone of the specified store ID.
     *
     * @param int|string $storeId
     * @param boolean    $asDateTimeZone
     *
     * @return string|DateTimeZone
     */
    public function getStoreTimeZone($storeId, $asDateTimeZone = false)
    {
        $storeId = (int) $storeId;

        $storeTimeZones = $this->getStoreTimeZones();
        if (isset($storeTimeZones[$storeId])) {
            $timeZone = $storeTimeZones[$storeId];
            if ($asDateTimeZone) {
                $timeZone = new DateTimeZone($timeZone);
            }

            return $timeZone;
        }

        $timeZone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
        $storeTimeZones[$storeId] = $timeZone;

        $this->setStoreTimeZones($storeTimeZones);

        if ($asDateTimeZone) {
            $timeZone = new DateTimeZone($timeZone);
        }
        return $timeZone;
    }

    /**
     * Get debug mode config setting.
     *
     * @return int
     */
    public function getDebugMode()
    {
        if (Mage::registry('postnl_debug_mode') !== null) {
            return Mage::registry('postnl_debug_mode');
        }

        $debugMode = (int) Mage::getStoreConfig(self::XPATH_DEBUG_MODE, Mage_Core_Model_App::ADMIN_STORE_ID);

        Mage::register('postnl_debug_mode', $debugMode);
        return $debugMode;
    }

    /**
     * Alias for TIG_PostNL_Helper_Data::getModuleVersion()
     *
     * @return string
     *
     * @see TIG_PostNL_Helper_Data::getModuleVersion
     */
    public function getExtensionVersion()
    {
        return $this->getModuleVersion();
    }

    /**
     * Get the current version of the PostNL extension's code base.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $version = (string) Mage::getConfig()->getModuleConfig('TIG_PostNL')->version;

        return $version;
    }

    /**
     * Get the current stability of the PostNL extension's code base.
     *
     * @return string
     */
    public function getModuleStability()
    {
        $stability = Mage::getConfig()->getXpath('tig/stability/postnl');
        $stability = (string) $stability[0];

        return $stability;
    }

    /**
     * Gets a shipment's PakjeGemak address if available.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return bool|Mage_Sales_Model_Order_Address
     */
    public function getPakjeGemakAddressForShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $order = $shipment->getOrder();

        return $this->getPakjeGemakAddressForOrder($order);
    }

    /**
     * Gets an order's PakjeGemak address if available.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool|Mage_Sales_Model_Order_Address
     */
    public function getPakjeGemakAddressForOrder(Mage_Sales_Model_Order $order)
    {
        /**
         * Check if this order was placed using PostNL.
         */
        $shippingMethod = $order->getShippingMethod();

        /**
         * If this shipment's order was not placed with PostNL, we need to ignore any PakjeGemak addresses that may have
         * been saved.
         */
        /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
        $carrierHelper = Mage::helper('postnl/carrier');
        if (!$carrierHelper->isPostnlShippingMethod($shippingMethod)) {
            return false;
        }

        /**
         * @var Mage_Sales_Model_Order_Address $address
         */
        $addressCollection = $order->getAddressesCollection();
        foreach ($addressCollection as $address) {
            if ($address->getAddressType() == 'pakje_gemak') {
                return $address;
            }
        }

        return false;
    }

    /**
     * Checks to see if the module may ship to the Netherlands or Belgium using PostNL standard shipments.
     *
     * @return boolean
     */
    public function canUseStandard()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseStandard()) {
            return $cache->getPostnlCoreCanUseStandard();
        }

        $canUseStandardNL = $this->canUseStandardForCountry('NL');
        $canUseStandardBE = $this->canUseStandardForCountry('BE');

        $result = $canUseStandardNL || $canUseStandardBE;

        if ($cache) {
            $cache->setPostnlCoreCanUseStandard($result)
                  ->saveCache();
        }

        return $result;
    }

    /**
     * Checks to see if the module may ship to the Netherlands using PostNL standard shipments.
     *
     * @param $country
     *
     * @return bool
     */
    public function canUseStandardForCountry($country)
    {
        $cache = $this->getCache();

        $hasPostnlCoreCanUseStandard = 'hasPostnlCoreCanUseStandard' . $country;
        $getPostnlCoreCanUseStandard = 'getPostnlCoreCanUseStandard' . $country;
        $setPostnlCoreCanUseStandard = 'setPostnlCoreCanUseStandard' . $country;

        if ($cache && $cache->$hasPostnlCoreCanUseStandard()) {
            return $cache->$getPostnlCoreCanUseStandard();
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_StandardProductOptions $standardProductOptionsModel */
        $standardProductOptionsModel = Mage::getModel('postnl_core/system_config_source_standardProductOptions');
        $standardProductOptions = $standardProductOptionsModel->getAvailableOptions(false, $country);
        if (empty($standardProductOptions)) {
            if ($cache) {
                $cache->$setPostnlCoreCanUseStandard(false)
                      ->saveCache();
            }

            return false;
        }

        if ($cache) {
            $cache->$setPostnlCoreCanUseStandard(true)
                  ->saveCache();
        }

        return true;
    }

    /**
     * Checks to see if the module may ship using PakjeGemak.
     *
     * @param mixed $country
     *
     * @return bool
     */
    public function canUsePakjeGemak($country = false)
    {
        $cache = $this->getCache();

        $setPostnlCoreCanUsePakjeGemak = 'setPostnlCoreCanUsePakjeGemak';
        $hasPostnlCoreCanUsePakjeGemak = 'hasPostnlCoreCanUsePakjeGemak';
        $getPostnlCoreCanUsePakjeGemak = 'getPostnlCoreCanUsePakjeGemak';

        if ($country) {
            $setPostnlCoreCanUsePakjeGemak .= $country;
            $hasPostnlCoreCanUsePakjeGemak .= $country;
            $getPostnlCoreCanUsePakjeGemak .= $country;
        }

        if ($cache && $cache->$hasPostnlCoreCanUsePakjeGemak()) {
            return $cache->$getPostnlCoreCanUsePakjeGemak();
        }

        $options = array(
            'isCod' => false,
        );

        if ($country) {
            $options['countryLimitation'] = $country;
        } else {
            $options['isBelgiumOnly'] = false;
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions $pakjeGemakProductoptionsModel */
        $pakjeGemakProductoptionsModel = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions');
        $pakjeGemakProductoptions = $pakjeGemakProductoptionsModel->getOptions($options, false, true);

        if (empty($pakjeGemakProductoptions)) {
            if ($cache) {
                $cache->$setPostnlCoreCanUsePakjeGemak(false)
                      ->saveCache();
            }

            return false;
        }

        if ($cache) {
            $cache->$setPostnlCoreCanUsePakjeGemak(true)
                  ->saveCache();
        }
        return true;
    }

    /**
     * Checks to see if the module may ship to EU countries using EPS
     *
     * @return boolean
     */
    public function canUseEps()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseEps()) {
            return $cache->getPostnlCoreCanUseEps();
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_EuProductOptions $euProductOptionsModel */
        $euProductOptionsModel = Mage::getModel('postnl_core/system_config_source_euProductOptions');
        $euProductOptions = $euProductOptionsModel->getAvailableOptions();

        if (empty($euProductOptions)) {
            if ($cache) {
                $cache->setPostnlCoreCanUseEps(false)
                      ->saveCache();
            }
            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreCanUseEps(true)
                  ->saveCache();
        }
        return true;
    }

    /**
     * Checks to see if the module may ship to countries outside the EU using GlobalPack
     *
     * @return boolean
     */
    public function canUseGlobalPack()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseGlobalPack()) {
            return $cache->getPostnlCoreCanUseGlobalPack();
        }

        if (!$this->isGlobalAllowed()) {
            if ($cache) {
                $cache->setPostnlCoreCanUseGlobalPack(false)
                      ->saveCache();
            }
            return false;
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_GlobalProductOptions $globalProductOptionsModel */
        $globalProductOptionsModel = Mage::getModel('postnl_core/system_config_source_globalProductOptions');
        $globalProductOptions = $globalProductOptionsModel->getAvailableOptions();

        if (empty($globalProductOptions)) {
            if ($cache) {
                $cache->setPostnlCoreCanUseGlobalPack(false)
                      ->saveCache();
            }
            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreCanUseGlobalPack(true)
                  ->saveCache();
        }
        return true;
    }

    /**
     * Checks to see if the module may ship buspakjes.
     *
     * @return boolean
     */
    public function canUseBuspakje()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseBuspakje()) {
            return $cache->getPostnlCoreCanUseBuspakje();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $storeId = Mage::app()->getStore()->getStoreId();

        $isBuspakjeActive = Mage::getStoreConfigFlag(self::XPATH_USE_BUSPAKJE, $storeId);

        if (!$isBuspakjeActive) {
            if ($cache) {
                $cache->setPostnlCoreCanUseBuspakje(false)
                      ->saveCache();
            }

            return false;
        }

        /** @var TIG_PostNL_Model_Core_System_Config_Source_BuspakjeProductOptions $buspakjeProductOptionsModel */
        $buspakjeProductOptionsModel = Mage::getModel('postnl_core/system_config_source_buspakjeProductOptions');
        $buspakjeProductOptions = $buspakjeProductOptionsModel->getAvailableOptions();

        if (empty($buspakjeProductOptions)) {
            if ($cache) {
                $cache->setPostnlCoreCanUseBuspakje(false)
                      ->saveCache();
            }

            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreCanUseBuspakje(true)
                  ->saveCache();
        }

        return true;
    }

    /**
     * Checks whether the EPS BE only product option is allowed.
     *
     * @param bool|int $storeId
     *
     * @return bool
     */
    public function canUseEpsBEOnlyOption($storeId = false)
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseEpsBeOnlyOption()) {
            return $cache->getPostnlCoreCanUseEpsBeOnlyOption();
        }

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $epsBeOnlyOptionAllowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_EPS_BE_ONLY_OPTION, $storeId);

        if ($cache) {
            $cache->setPostnlCoreCanUseEpsBeOnlyOption($epsBeOnlyOptionAllowed)
                  ->saveCache();
        }

        return $epsBeOnlyOptionAllowed;
    }

    /**
     * Checks if the productcode 4936 is allowed.
     *
     * @param bool $storeId
     *
     * @return bool
     */
    public function canUsePakjegemakBeNotInsured($storeId = false)
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUsePakjegemakNotInsured()) {
            return $cache->getPostnlCoreCanUsePakjegemakNotInsured();
        }

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $pakjegemakNotInsuredAllowed = Mage::getStoreConfigFlag(self::XPATH_ALLOW_PAKJEGEMAK_NOT_INSURED, $storeId);

        if ($cache) {
            $cache->setPostnlCoreCanUsePakjegemakNotInsured($pakjegemakNotInsuredAllowed)
                ->saveCache();
        }

        return $pakjegemakNotInsuredAllowed;
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     *
     * @return bool
     *
     * @see Mage_Adminhtml_System_ConfigController::_saveState()
     */
    public function saveConfigState($configState = array())
    {
        /** @var Mage_Admin_Model_Session $adminSession */
        $adminSession = Mage::getSingleton('admin/session');
        /** @var Mage_Admin_Model_User $adminUser */
        /** @noinspection PhpUndefinedMethodInspection */
        $adminUser = $adminSession->getUser();
        if (!$adminUser) {
            return false;
        }

        if (!is_array($configState)) {
            return false;
        }

        $extra = $adminUser->getExtra();
        if (!is_array($extra)) {
            $extra = array();
        }

        if (!isset($extra['configState'])) {
            $extra['configState'] = array();
        }

        foreach ($configState as $fieldset => $state) {
            $extra['configState'][$fieldset] = $state;
        }

        $adminUser->setExtra($extra)
                  ->saveExtra($extra);

        return true;
    }

    /**
     * Alias for TIG_PostNL_Helper_Data::quoteIsBuspakje() provided for backwards compatibility.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     *
     * @see TIG_PostNL_Helper_Data::quoteIsBuspakje()
     */
    public function isBuspakjeConfigApplicableToQuote(Mage_Sales_Model_Quote $quote = null)
    {
        return $this->quoteIsBuspakje($quote);
    }

    /**
     * Checks if the buspakje-specific configuration is applicable to the current quote.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function quoteIsBuspakje(Mage_Sales_Model_Quote $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'is_buspakje_config_applicable_to_quote_' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /**
         * Orders to Belgium are never letter box parcels.
         */
        if ($this->isBe($quote)) {
            return false;
        }

        /**
         * Food orders are never letter box parcels.
         */
        if ($this->quoteIsFood($quote)) {
            return false;
        }

        /**
         * ID Check orders are never letter box parcels.
         */
        if ($this->quoteHasIDCheckProducts($quote)) {
            return false;
        }

        /**
         * If the buspakje calculation mode is set to 'manual', no further checks are required as the regular delivery
         * option rules will apply.
         */
        if (self::BUSPAKJE_CALCULATION_MODE_AUTOMATIC != $this->getBuspakjeCalculationMode()) {
            Mage::register($registryKey, false);
            return false;
        }

        /**
         * Check if the current quote would fit as a letter box parcel.
         */
        $quoteItems = $quote->getAllItems();

        $fits = $this->fitsAsBuspakje($quoteItems);

        Mage::register($registryKey, $fits);
        return $fits;
    }

    /**
     * Checks if the current quote is classified as a food quote, and saves the result in the cache.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return boolean
     */
    public function quoteIsFood(Mage_Sales_Model_Quote $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }

        /**
         * Form a unique registry key for the current quote (if available) so we can cache the result of this method in
         * the registry.
         */
        $registryKey = 'postnl_quote_is_food' . $quote->getId();

        /**
         * Check if the result of this method has been cached in the registry.
         */
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        $quoteFoodType = (bool) $this->getQuoteFoodType($quote);

        Mage::register($registryKey, $quoteFoodType);
        return $quoteFoodType;
    }

    /**
     * Returns the food Type of the provided quote.
     *
     * Cool products are leading. So if we find a cooled products, the entire quote is marked as Cool Products.
     * If there is a Dry & Groceries product, but no Cooled Product, the quote is marked as Dry & Groceries.
     * If neither of above is found, the quote is marked as Non-Food.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return int
     *  0 = Non-Food
     *  1 = Dry & Groceries
     *  2 = Cool Products
     */
    public function getQuoteFoodType(Mage_Sales_Model_Quote $quote = null)
    {
        if (!$quote) {
            $quote = $this->getQuote();
        }

        $quoteItems = $quote->getAllItems();

        $foodType = 0;
        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            /** @noinspection PhpUndefinedMethodInspection */
            $postnlProductType = $quoteItem->getProduct()->getPostnlProductType();

            if ($postnlProductType == $deliveryOptionsHelper::FOOD_TYPE_COOL_PRODUCTS) {
                $foodType = $postnlProductType;
                break;
            }

            if ($postnlProductType == $deliveryOptionsHelper::FOOD_TYPE_DRY_GROCERIES) {
                $foodType = $postnlProductType;
            }
        }

        return $foodType;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool|mixed
     */
    public function quoteHasIDCheckProducts(Mage_Sales_Model_Quote $quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        $registryKey = 'postnl_quote_has_id_check_products_' . $quote->getId();
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        if ($this->quoteIsAgeCheck($quote)) {
            Mage::registry($registryKey, true);
            return true;
        }

        if ($this->quoteIsBirthdayCheck($quote)) {
            Mage::registry($registryKey, true);
            return true;
        }

        if ($this->quoteIsIDCheck($quote)) {
            Mage::registry($registryKey, true);
            return true;
        }

        return false;
    }

    /**
     * Check if this quote has a ID Check product.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool|mixed
     */
    public function quoteIsIDCheck(Mage_Sales_Model_Quote $quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        $registryKey = 'postnl_quote_is_id_check_' . $quote->getId();
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        $result = $this->_hasQuotePostnlProductType($deliveryOptionsHelper::IDCHECK_TYPE_ID, $quote);

        Mage::register($registryKey, $result);
        return $result;
    }

    /**
     * Check if this quote has a Age Check product.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool|mixed
     */
    public function quoteIsAgeCheck(Mage_Sales_Model_Quote $quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        $registryKey = 'postnl_quote_is_age_check_' . $quote->getId();
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        $result = $this->_hasQuotePostnlProductType($deliveryOptionsHelper::IDCHECK_TYPE_AGE, $quote);

        Mage::register($registryKey, $result);
        return $result;
    }

    /**
     * Check if this quote has a Age Check product.
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool|mixed
     */
    public function quoteIsBirthdayCheck(Mage_Sales_Model_Quote $quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        $registryKey = 'postnl_quote_is_birthday_check_' . $quote->getId();
        if (Mage::registry($registryKey) !== null) {
            return Mage::registry($registryKey);
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');
        $result = $this->_hasQuotePostnlProductType($deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY, $quote);

        Mage::register($registryKey, $result);
        return $result;
    }

    /**
     * Gets the currently configured buspakje calculation mode.
     *
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getBuspakjeCalculationMode($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        /**
         * If buspakje is turned off, return setting 'manual' to prevent extra checks while getting the same
         * functionality.
         */
        $buspakjeActive = $this->canUseBuspakje();
        if(!$buspakjeActive){
            return self::BUSPAKJE_CALCULATION_MODE_MANUAL;
        }

        $calculationMode = Mage::getStoreConfig(self::XPATH_BUSPAKJE_CALC_MODE, $storeId);

        return $calculationMode;
    }

    /**
     * Determines whether an array of items would fit as a buspakje shipment.
     *
     * @param array|Mage_Sales_Model_Resource_Collection_Abstract $items
     * @param boolean                                             $registerReason
     *
     * @return boolean
     */
    public function fitsAsBuspakje($items, $registerReason = false)
    {
        $totalQtyRatio = 0;
        $totalWeight = 0;

        if ($registerReason) {
            Mage::unregister('postnl_reason_not_buspakje');
        }

        /**
         * @var Mage_Sales_Model_Order_Item|Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($items as $item) {
            /**
             * Get either the qty ordered or the qty shipped, depending on whether this is an order or a shipment item.
             */
            if ($item instanceof Mage_Sales_Model_Order_Item) {
                if ($item->getParentItemId()) {
                    $qty = $item->getParentItem()->getQtyOrdered();
                } else {
                    $qty = $item->getQtyOrdered();
                }
            } elseif ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
                $qty = $item->getQty();
            } elseif($item instanceof Mage_Sales_Model_Quote_Item) {
                if ($item->getParentItemId()) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $qty = $item->getParentItem()->getQty();
                } else {
                    $qty = $item->getQty();
                }
            } else {
                if ($registerReason) {
                    Mage::register('postnl_reason_not_buspakje', 'missing_qty');
                }
                return false;
            }

            $product = $item->getProduct();
            /**
             * @todo optimize this code.
             */
            if (!$product) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
            }

            /**
             * The max qty attribute is only available on simple products.
             */
            if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                continue;
            }

            /**
             * Calculate the weight of the item in kilograms.
             */
            $weight = $item->getWeight() * $qty;
            $convertedWeight = $this->standardizeWeight($weight, $item->getStoreId());

            $totalWeight += $convertedWeight;

            /**
             * Get how many of this product would fit in a buspakje package.
             */
            $maxQty = Mage::getResourceSingleton('postnl/catalog_product')->getAttributeRawValue(
                $item->getProductId(),
                'postnl_max_qty_for_buspakje',
                $item->getStoreId()
            );

            if (!is_numeric($maxQty) || !$maxQty) {
                if ($registerReason) {
                    Mage::register('postnl_reason_not_buspakje', 'invalid_max_qty');
                }
                return false;
            }

            /**
             * Determine the ratio. If 2 products fit, then the ratio is 1/2 = 0.5. If 3 fit, the ratio is 1/3 = 0.33.
             */
            $qtyRatio = 1 / $maxQty;

            $totalQtyRatio += $qtyRatio * $qty;
        }

        /**
         * If the combined weight of all items is more than 2 kg, this shipment is not a buspakje.
         */
        if ($totalWeight > self::MAX_LETTER_BOX_PARCEL_WEIGHT) {
            if ($registerReason) {
                Mage::register('postnl_reason_not_buspakje', 'weight');
            }
            return false;
        }

        /**
         * If the combined qty ratios of the items is more than 1 this is not a buspakje.
         */
        if ($totalQtyRatio > self::MAX_LETTER_BOX_PARCEL_QTY_RATIO) {
            if ($registerReason) {
                Mage::register('postnl_reason_not_buspakje', 'qty_ratio');
            }
            return false;
        }

        return true;
    }

    /**
     * Convert a given weight to kilogram or gram.
     *
     * @param float $weight The weight to be converted
     * @param int | null $storeId Store Id used to determine the weight unit that was originally used
     * @param boolean $toGram Optional parameter to convert to gram instead of kilogram
     *
     * @return float
     */
    public function standardizeWeight($weight, $storeId = null, $toGram = false)
    {
        if ($weight == 0) {
            return 0;
        }

        if (is_null($storeId)) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $unitUsed = Mage::getStoreConfig(self::XPATH_WEIGHT_UNIT, $storeId);

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
     * Checks if the current admin user is allowed for the specified actions.
     *
     * @param array|string $actions
     * @param boolean      $throwException
     *
     * @throws TIG_PostNL_Exception
     *
     * @return bool
     */
    public function checkIsPostnlActionAllowed($actions = array(), $throwException = false)
    {
        if (!is_array($actions)) {
            $actions = array($actions);
        }

        foreach ($actions as $action) {
            if ($this->_isActionAllowed($action)) {
                continue;
            }

            if ($throwException) {
                throw new TIG_PostNL_Exception(
                    $this->__('The current user is not allowed to perform this action.'),
                    'POSTNL-0155'
                );
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a specified action is allowed for the current admin user.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function _isActionAllowed($action)
    {
        switch ($action) {
            case 'create_shipment':
                $aclPath = 'sales/order/actions/ship';
                break;
            case 'view_complete_status':
                $aclPath = 'postnl/shipment/complete_status';
                break;
            case 'download_logs':
                $aclPath = 'system/config/postnl/download_logs';
                break;
            case 'print_packing_slip': //no break
            case 'print_packing_slips':
                $aclPath = 'postnl/shipment/actions/print_label/print_packing_slips';
                break;
            case 'print_return_label': //no break
            case 'print_return_labels':
                $aclPath = 'postnl/shipment/actions/print_label/print_return_labels';
                break;
            case 'send_return_label_email': //no break
            case 'send_return_labels_email':
                $aclPath = 'postnl/shipment/actions/print_label/print_return_labels/send_return_label_email';
                break;
            case 'convert_to_buspakje':
                $aclPath = 'postnl/shipment/actions/convert/to_buspakje';
                break;
            case 'convert_to_package':
                $aclPath = 'postnl/shipment/actions/convert/to_package';
                break;
            case 'change_product_code': //no break
            case 'change_parcel_count':
                $aclPath = 'postnl/shipment/actions/convert/' . $action;
                break;
            case 'confirm':                  //no break
            case 'print_label':              //no break
            case 'reset_confirmation':       //no break
            case 'delete_labels':            //no break
            case 'create_parcelware_export': //no break
            case 'send_track_and_trace':
                $aclPath = 'postnl/shipment/actions/' . $action;
                break;
            default:
                $aclPath = false;
                break;
        }

        if (!$aclPath) {
            return false;
        }

        /** @var Mage_Admin_Model_Session $adminSession */
        $adminSession = Mage::getSingleton('admin/session');
        $isAllowed = $adminSession->isAllowed($aclPath);
        return $isAllowed;
    }

    /**
     * Checks if GlobalPack may be used.
     *
     * @return boolean
     */
    public function isGlobalAllowed()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $useGlobal = Mage::getStoreConfigFlag(self::XPATH_USE_GLOBALPACK, $storeId);
        return $useGlobal;
    }

    /**
     * Check if the module is set to test mode
     *
     * @param bool|int $storeId
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

        $testMode = false;
        $mode = Mage::getStoreConfig(self::XPATH_EXTENSION_MODE, $storeId);

        if ($mode === '1') {
            $testMode = true;
        }

        Mage::register('postnl_test_mode', $testMode);
        return $testMode;
    }

    /**
     * Checks if test mode is currently allowed
     *
     * @deprecated 1.2.0 Test mode is now always allowed, regardless of configuration. This method should therefore not
     *                   be used anymore and may be removed in the future.
     *
     * @return boolean
     */
    public function isTestModeAllowed()
    {
        trigger_error('This method is deprecated and may be removed in the future.', E_USER_NOTICE);
        return true;
    }

    /**
     * Alias for isEnabled()
     *
     * @param int|boolean  $storeId
     * @param null|boolean $forceTestMode
     *
     * @return boolean
     *
     * @see TIG_PostNL_Helper_Data::isEnabled()
     */
    public function isActive($storeId = false, $forceTestMode = null)
    {
        return $this->isEnabled($storeId, $forceTestMode);
    }

    /**
     * Determines if the extension is active
     *
     * @param int|boolean  $storeId
     * @param null|boolean $forceTestMode
     * @param boolean      $ignoreCache
     *
     * @return boolean
     */
    public function isEnabled($storeId = false, $forceTestMode = null, $ignoreCache = false)
    {
        if ($ignoreCache) {
            $cache = false;
        } else {
            $cache = $this->getCache();
        }

        if ($cache && $cache->hasPostnlCoreIsEnabled()) {
            return $cache->getPostnlCoreIsEnabled();
        }

        $isEnabled = $this->_isEnabled($storeId, $forceTestMode, $ignoreCache);

        if ($cache) {
            $cache->setPostnlCoreIsEnabled($isEnabled)
                  ->saveCache();
        }

        return $isEnabled;
    }

    /**
     * Run various checks to make sure the PostNL extension is enabled and fully configured.
     *
     * @param int|boolean  $storeId
     * @param null|boolean $forceTestMode
     * @param boolean      $ignoreCache
     *
     * @return bool
     */
    protected function _isEnabled($storeId, $forceTestMode, $ignoreCache)
    {
        Mage::unregister('postnl_core_is_enabled_errors');

        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0210',
                    'message' => $this->__(
                        'The installed version of PHP is too low. The installed PHP version is %s, the minimum ' .
                        'required PHP version is %s.',
                        PHP_VERSION,
                        self::MIN_PHP_VERSION
                    ),
                )
            );

            Mage::register('postnl_core_is_enabled_errors', $errors);
            return false;
        }

        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        /**
         * Check if the module has been enabled
         */
        $enabled = Mage::getStoreConfigFlag(self::XPATH_EXTENSION_MODE, $storeId);

        if ($enabled === false) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0030',
                    'message' => $this->__('You have not yet enabled the extension.'),
                )
            );

            Mage::register('postnl_core_is_enabled_errors', $errors);
            return false;
        }

        /**
         * Make sure that the required PHP extensions are loaded.
         */
        $phpExtensionsLoaded = $this->areRequiredPHPExtensionsLoaded();
        if ($phpExtensionsLoaded === false) {
            return false;
        }

        /**
         * Check if the module's required configuration options have been filled
         */
        $isConfigured = $this->isConfigured($storeId, $forceTestMode, $ignoreCache);
        if ($isConfigured === false) {
            return false;
        }

        /**
         * Check if at least one PostNL shipping method is active.
         *
         * First get a list of all PostNl shipping methods from the PostNl config. Then compare this to a list of all
         * active shipping methods in Magento.
         */
        $postnlShippingMethodEnabled = false;
        /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
        $carrierHelper               = Mage::helper('postnl/carrier');
        $postnlShippingMethods       = $carrierHelper->getPostnlShippingMethods();
        $activeMethods               = Mage::getModel('postnl_core/system_config_source_shippingMethods')
                                           ->toArray(true);

        if ($postnlShippingMethods) {
            $activePostnlMethods = array_intersect($postnlShippingMethods, $activeMethods);
            if (!empty($activePostnlMethods)) {
                $postnlShippingMethodEnabled = true;
            }
        }

        if (!$postnlShippingMethodEnabled) {
            $link = '';
            $linkEnd = '';

            if ($this->isSystemConfig() || $this->isLoggingEnabled()) {
                /** @var Mage_Adminhtml_Helper_Data $adminhtmlHelper */
                $adminhtmlHelper = Mage::helper("adminhtml");
                $shippingMethodSectionurl = $adminhtmlHelper->getUrl(
                    'adminhtml/system_config/edit',
                    array(
                        '_secure' => true,
                        'section' => 'carriers',
                    )
                );

                $link = '<a href="'
                      . $shippingMethodSectionurl
                      . '" target="_blank" title="'
                      . $this->__('Shipping Methods')
                      . '">';
                $linkEnd = '</a>';
            }

            $errorMessage = $this->__(
                'No PostNL shipping method has been enabled. You can enable the PostNL shipping method under '
                . '%sSystem > Config > Shipping Methods%s.',
                $link,
                $linkEnd
            );

            $errors = array(
                array(
                    'code'    => 'POSTNL-0031',
                    'message' => $errorMessage,
                )
            );

            Mage::register('postnl_core_is_enabled_errors', $errors);
            return false;
        }

        /**
         * The PostNL module only works with EUR as the shop's base currency
         */
        /** @var Mage_Core_Model_Store $store */
        $store = Mage::getModel('core/store')->load($storeId);
        $baseCurrencyCode = $store->getBaseCurrencyCode();
        if ($baseCurrencyCode != 'EUR') {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0032',
                    'message' => $this->__("The shop's base currency code must be set to EUR for PostNL to function."),
                )
            );

            Mage::register('postnl_core_is_enabled_errors', $errors);
            return false;
        }

        return true;
    }

    /**
     * Check if the required SOAP, OpenSSL and MCrypt PHP extensions are loaded.
     *
     * @return bool
     */
    public function areRequiredPHPExtensionsLoaded()
    {
        $errors = array();
        if (!extension_loaded('soap')) {
            $errors[] = array(
                'code'    => 'POSTNL-0134',
                'message' => $this->__(
                    'The SOAP extension is not installed. PostNL requires the SOAP extension to communicate with '
                    . 'PostNL.'
                ),
            );
        }

        if (!extension_loaded('openssl')) {
            $errors[] = array(
                'code'    => 'POSTNL-0135',
                'message' => $this->__(
                    'The OpenSSL extension is not installed. The PostNL extension requires the OpenSSL extension to '
                    . 'secure the communications with the PostNL servers.'
                ),
            );
        }

        if (!extension_loaded('mcrypt')) {
            $errors[] = array(
                'code'    => 'POSTNL-0137',
                'message' => $this->__(
                    'The MCrypt extension is not installed. The PostNL extension requires the MCrypt extension to '
                    . 'secure the communications with the PostNL servers.'
                ),
            );
        }

        /**
         * Register any errors that may have occurred and return false.
         */
        if (!empty($errors)) {
            Mage::register('postnl_core_is_enabled_errors', $errors);
            return false;
        }

        return true;
    }

    /**
     * Check if the modules has been configured.
     * The required fields will only be checked to see if they're not empty. The values entered will not be validated.
     *
     * @param int|boolean  $storeId
     * @param null|boolean $forceTestMode
     * @param boolean      $ignoreCache
     *
     * @return boolean
     */
    public function isConfigured($storeId = false, $forceTestMode = null, $ignoreCache = false)
    {
        if ($ignoreCache) {
            $cache = false;
        } else {
            $cache = $this->getCache();
        }

        if ($cache && $cache->hasPostnlCoreIsConfigured()) {
            return $cache->getPostnlCoreIsConfigured();
        }

        $isConfigured = $this->_isConfigured($storeId, $forceTestMode);

        if ($cache) {
            $cache->setPostnlCoreIsConfigured($isConfigured)
                  ->saveCache();
        }

        return $isConfigured;
    }

    /**
     * Checks if the PostNL extension is fully configured.
     *
     * @param int|boolean  $storeId
     * @param null|boolean $forceTestMode
     *
     * @return bool
     */
    protected function _isConfigured($storeId, $forceTestMode)
    {
        if ($forceTestMode === null) {
            $testMode = $this->isTestMode();
        } else {
            $testMode = $forceTestMode;
        }

        $errors = array();

        Mage::unregister('postnl_core_is_configured_errors');

        /**
         * Check if the module has been activated.
         *
         * The is_activated config value can have 3 possible values:
         *  0 - The extension has not yet been activated.
         *  1 - The activation procedure has begun and keys have been sent to the merchant.
         *  2 - The activation procedure has been finished. The merchant has entered his keys.
         */
        $isActivated = Mage::getStoreConfig(self::XPATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($isActivated != self::EXTENSION_ACTIVE) {
            $errors[] = array(
                'code'    => 'POSTNL-0033',
                'message' => $this->__('The extension has not been activated.'),
            );
        }

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        /**
         * Get the bse required fields. These are always required.
         */
        $baseFields = $this->getRequiredFields();

        /**
         * Get either the live mode or test mode required fields.
         */
        if ($testMode) {
            $modeFields = $this->getTestModeRequiredFields();
        } else {
            $modeFields = $this->getLiveModeRequiredFields();
        }
        $requiredFields = array_merge($modeFields, $baseFields);

        /**
         * Check if all required fields are entered. This method will return an array of errors containing the fields
         * that are missing. If all fields are entered, the array will be empty.
         */
        $fieldErrors = $this->_getFieldsConfiguredErrors($requiredFields, $storeId);
        $errors = array_merge($errors, $fieldErrors);

        /**
         * If any errors were detected, add them to the registry and return false.
         */
        if (!empty($errors)) {
            Mage::register('postnl_core_is_configured_errors', $errors);
            return false;
        }

        return true;
    }

    /**
     * Checks if configuration fields that are required for GlobalPack shipments are configured.
     *
     * @param boolean $storeId
     * @param boolean $ignoreCache
     *
     * @return boolean
     */
    public function isGlobalConfigured($storeId = false, $ignoreCache = false)
    {
        if ($ignoreCache) {
            $cache = false;
        } else {
            $cache = $this->getCache();
        }

        if ($cache && $cache->hasPostnlCoreIsGlobalConfigured()) {
            return $cache->getPostnlCoreIsGlobalConfigured();
        }

        Mage::unregister('postnl_core_is_global_configured_errors');

        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $fields = $this->getGlobalShipmentsRequiredFields();

        $errors = $this->_getFieldsConfiguredErrors($fields, $storeId);

        if (!empty($errors)) {
            Mage::register('postnl_core_is_global_configured_errors', $errors);

            if ($cache) {
                $cache->setPostnlCoreIsConfigured(false)
                      ->saveCache();
            }
            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreIsGlobalConfigured(true)
                  ->saveCache();
        }
        return true;
    }

    /**
     * Checks if a specified array of fields are configured. If not, returns an array of errors.
     *
     * @param array $requiredFields
     * @param int   $storeId
     *
     * @return array
     */
    protected function _getFieldsConfiguredErrors($requiredFields, $storeId)
    {
        $errors = array();

        /**
         * If full logging is enabled or we are on the system > config page in the backend, we may add additional
         * details about fields that are missing. To do this we need to load the very large Mage_Adminhtml_Model_Config
         * singleton.
         */
        if ($this->isSystemConfig() || $this->isLoggingEnabled()) {
            /**
             * Load the adminhtml config model and get the PostNL section.
             *
             * @var Mage_Adminhtml_Model_Config $configFields
             * @var Varien_Simplexml_Element $section
             */
            $configFields = Mage::getSingleton('adminhtml/config');
            /** @noinspection PhpUndefinedFieldInspection */
            $section = $configFields->getSections('postnl')->postnl;
        }

        /**
         * Loop through all required fields and check if they're configured.
         *
         * $requiredField may be the full xpath to the config setting or it may be an array of xpaths. In the latter
         * case one of the fields in the array must be configured.
         */
        foreach ($requiredFields as $requiredField) {
            /**
             * Get the value of this field.
             */
            if (is_array($requiredField)) {
                $value = null;
                foreach ($requiredField as $requiredSubField) {
                    if (Mage::getStoreConfig($requiredSubField, $storeId)) {
                        $value = true;
                        break;
                    }
                }
            } else {
                $value = Mage::getStoreConfig($requiredField, $storeId);
            }

            /**
             * If the value is null or an empty string, it is not configured. Please note that 0 is a valid value.
             */
            if ($value !== null && $value !== '') {
                continue;
            }

            /**
             * Add the error message. The error message may be different based on whether the missing field is a single
             * field, an array of fields and whether we are currently on the system > config page.
             */
            if (isset($section) && !is_array($requiredField)) {
                $errorMessage = $this->_getFieldMissingErrorMessage($requiredField, $section);

                $errors[] = array(
                    'code'    => 'POSTNL-0034',
                    'message' => $errorMessage,
                );
            } elseif (isset($section)) {
                $message = $this->__('One of the following fields is required:');

                $fieldErrors = array();
                foreach ($requiredField as $requiredSubField) {
                    $fieldErrors[] = $this->_getFieldMissingErrorMessage($requiredSubField, $section, '%s > %s');
                }

                $implodeString = ' ' . $this->__('or') . ' ';
                $message .= ' ' . implode($implodeString, $fieldErrors);

                $errors[] = array(
                    'code'    => 'POSTNL-0034',
                    'message' => $message,
                );
            } else {
                $errors[] = array(
                    'code'    => 'POSTNL-0160',
                    'message' => $this->__('A required configuration value is missing: %s', $requiredField),
                );
            }
        }

        return $errors;
    }

    /**
     * Get a formatted error message for a missing system > config value.
     *
     * @param string                   $requiredField The full xpath to the field.
     * @param Varien_Simplexml_Element $section The system.xml section the field is present in.
     * @param null|string              $format The format of the message. By default: '%s > %s is required.'.
     * @param boolean                  $saveConfigState
     *
     * @return string
     */
    protected function _getFieldMissingErrorMessage($requiredField, $section, $format = null, $saveConfigState = true)
    {
        $fieldParts = explode('/', $requiredField);
        $field      = $fieldParts[2];
        $group      = $fieldParts[1];

        /**
         * @var Varien_Simplexml_Element $sectionGroup
         */
        /** @noinspection PhpUndefinedFieldInspection */
        $sectionGroup = $section->groups->$group;

        /** @noinspection PhpUndefinedFieldInspection */
        $label      = (string) $sectionGroup->fields->$field->label;
        /** @noinspection PhpUndefinedFieldInspection */
        $groupLabel = (string) $sectionGroup->label;
        $groupName  = $sectionGroup->getName();

        if (!$format) {
            $format = '%s > %s is required.';
        }
        $message = $this->__($format, $this->__($groupLabel), $this->__($label));

        if ($saveConfigState && $this->isSystemConfig()) {
            $this->saveConfigState(array('postnl_' . $groupName => 1));
        }

        return $message;
    }

    /**
     * Check if return labels may be printed.
     *
     * @param boolean|int $storeId
     *
     * @return boolean
     */
    public function isReturnsEnabled($storeId = false)
    {
        if (false === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->isEnabled($storeId)) {
            return false;
        }

        $canPrintLabels = Mage::getStoreConfigFlag(self::XPATH_RETURN_LABELS_ACTIVE, $storeId);

        if (!$canPrintLabels) {
            return false;
        }

        $freePostNumber = Mage::getStoreConfig(self::XPATH_FREEPOST_NUMBER, $storeId);
        $freePostNumber = trim($freePostNumber);

        if (empty($freePostNumber)) {
            return false;
        }

        return true;
    }

    /**
     * Check if return labels can be printed along with shipping labels for the specified store view.
     *
     * @param boolean|int $storeId
     *
     * @return boolean
     */
    public function canPrintReturnLabelsWithShippingLabels($storeId = false)
    {
        if (false === $storeId) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        if (!$this->isReturnsEnabled($storeId)) {
            return false;
        }

        $printReturnLabels = Mage::getStoreConfigFlag(self::XPATH_PRINT_RETURN_LABELS_WITH_SHIPPING_LABELS, $storeId);
        return $printReturnLabels;
    }

    /**
     * Check if return label printing is available for logged-in customers.
     *
     * @param boolean|int $storeId
     *
     * @return boolean
     */
    public function canPrintReturnLabelForCustomer($storeId = false)
    {
        if (false === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->isReturnsEnabled($storeId)) {
            return false;
        }

        $canPrintReturnLabelForCustomer = Mage::getStoreConfigFlag(self::XPATH_CUSTOMER_PRINT_LABEL, $storeId);
        return $canPrintReturnLabelForCustomer;
    }

    /**
     * Check if return label printing is available for guests.
     *
     * @param boolean|int $storeId
     *
     * @return boolean
     */
    public function canPrintReturnLabelForGuest($storeId = false)
    {
        if (false === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!$this->canPrintReturnLabelForCustomer($storeId)) {
            return false;
        }

        $canPrintReturnLabelForGuest = Mage::getStoreConfigFlag(self::XPATH_GUEST_PRINT_LABEL, $storeId);
        return $canPrintReturnLabelForGuest;
    }

    /**
     * Check if printing return labels is allowed for this order.
     *
     * @param Mage_Sales_Model_Order|null $order
     *
     * @return bool
     */
    public function canPrintReturnLabelForOrder($order)
    {
        if (!$order || !$order->getId()) {
            return false;
        }

        /**
         * Check if printing return labels is allowed for the order's store ID and if it's allowed for logged-in
         * customers or guests depending on who placed the order.
         */
        if ($order->getCustomerIsGuest()
            && !$this->canPrintReturnLabelForGuest($order->getStoreId())
        ) {
            return false;
        } elseif (!$this->canPrintReturnLabelForCustomer($order->getStoreId())) {
            return false;
        }

        /**
         * Return labels are only available for orders that are shipped with PostNL.
         */
        $shippingMethod = $order->getShippingMethod();
        /** @var TIG_PostNL_Helper_Carrier $carrierHelper */
        $carrierHelper = Mage::helper('postnl/carrier');
        if (!$carrierHelper->isPostnlShippingMethod($shippingMethod)) {
            return false;
        }

        /**
         * Check if there are any confirmed PostNl shipments for this order.
         */
        $postnlShipmentsCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentsCollection->addFieldToFilter('order_id', array('eq' => $order->getId()))
                                  ->addFieldToFilter(
                                      'confirm_status',
                                      array(
                                          'eq' => TIG_PostNL_Model_Core_Shipment::CONFIRM_STATUS_CONFIRMED
                                      )
                                  );

        /**
         * We can only print return labels for confirmed shipments, so we need to make sure that at least one such
         * shipment is available.
         */
        if ($postnlShipmentsCollection->getSize() < 1) {
            return false;
        }

        if ($this->isFoodOrder($order)) {
            return false;
        }

        /**
         * Loop through all confirmed shipments. If at least one of them is able to print return labels, return true.
         */
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentsCollection as $postnlShipment) {
            if ($postnlShipment->canPrintReturnLabels()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the order contains food products.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function isFoodOrder($order)
    {
        $orderItems = $order->getAllItems();

        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            /** @noinspection PhpUndefinedMethodInspection */
            $postnlProductType = $orderItem->getProduct()->getPostnlProductType();

            if ($postnlProductType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the provided quote or order is being shipped to Belgium.
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $object
     *
     * @return bool
     */
    public function isBe($object)
    {
        if (!($object instanceof Mage_Sales_Model_Order) && !($object instanceof Mage_Sales_Model_Quote)) {
            throw new InvalidArgumentException("The object parameter must be an instance of an order or a quote.");
        }

        $shippingAddress = $object->getShippingAddress();
        if (!$shippingAddress) {
            return false;
        }

        if ($shippingAddress->getCountryId() == 'BE') {
            return true;
        }

        return false;
    }

    /**
     * Check if debug logging is enabled
     *
     * @return boolean
     */
    public function isLoggingEnabled()
    {
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            return false;
        }

        $debugMode = $this->getDebugMode();
        if ($debugMode >= self::LOGGING_FULL) {
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
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            return false;
        }

        $debugMode = $this->getDebugMode();
        if ($debugMode >= self::LOGGING_EXCEPTION_ONLY) {
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
     * @param string $moduleName
     *
     * @return string
     *
     * @see      Mage_Core_Model_Config::getModuleDir()
     */
    public function getModuleDir($dir, $moduleName = 'TIG_PostNL')
    {
        $config = Mage::app()->getConfig();

        /**
         * @var Varien_Simplexml_Element $moduleConfig
         */
        $moduleConfig = $config->getModuleConfig($moduleName);
        /** @noinspection PhpUndefinedFieldInspection */
        $codePool = (string) $moduleConfig->codePool;
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
     * Logs a debug message. Based on Mage::log.
     *
     * @param string      $message
     * @param int|null    $level
     * @param string|null $file
     * @param boolean     $forced
     * @param boolean     $isError
     *
     * @return $this
     *
     * @see Mage::log
     */
    public function log($message, $level = null, $file = null, $forced = false, $isError = false)
    {
        if ($isError === true
            && !$this->isExceptionLoggingEnabled()
            && !$forced
        ) {
            return $this;
        } elseif ($isError !== true
            && !$this->isLoggingEnabled()
            && !$forced
        ) {
            return $this;
        }

        if (is_null($level)) {
            $level = Zend_Log::DEBUG;
        }

        if (is_null($file)) {
            $file = static::POSTNL_LOG_DIRECTORY . DS . static::POSTNL_DEBUG_LOG_FILE;
        }

        $this->createLogDir();

        Mage::log($message, $level, $file, $forced);

        return $this;
    }

    /**
     * Logs a cron debug message to a separate file in order to differentiate it from other debug messages.
     *
     * @param string $message
     * @param int    $level
     *
     * @return $this
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
     * @param string|Exception $exception
     *
     * @return $this
     *
     * @see Mage::logException
     */
    public function logException($exception)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }

        if (is_object($exception)) {
            $message = "\n" . $exception->__toString();
        } else {
            $message = $exception;
        }

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::POSTNL_EXCEPTION_LOG_FILE;

        $this->log($message, Zend_Log::ERR, $file, false, true);

        return $this;
    }

    /**
     * Checks if the current edition of Magento is enterprise. Uses Mage::getEdition if available. If not, look for the
     * Enterprise_Enterprise extension. Finally, check the version number.
     *
     * @return boolean
     *
     * @throws TIG_PostNL_Exception
     */
    public function isEnterprise()
    {
        /**
         * Use Mage::getEdition, which is available since CE 1.7 and EE 1.12.
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
             * If the edition is not community or enterprise, it is not supported.
             */
            throw new TIG_PostNL_Exception(
                $this->__('Invalid Magento edition detected: %s', $edition),
                'POSTNL-0035'
            );
        }

        /**
         * Check if the Enterprise_Enterprise extension is installed.
         */
        /** @noinspection PhpUndefinedFieldInspection */
        if (Mage::getConfig()->getNode('modules')->Enterprise_Enterprise) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current environment is in the shop's admin area.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        /**
         * Fallback check in case the previous check returns a false negative.
         */
        if (Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current page is the system/config page in the backend.
     *
     * @return bool
     */
    public function isSystemConfig()
    {
        if (!$this->isAdmin()) {
            return false;
        }

        $request = Mage::app()->getRequest();
        if ($request->getControllerName() == 'system_config' && $request->getActionName() == 'edit') {
            return true;
        }

        return false;
    }

    /**
     * Creates a separate dir to log PostNL log files. Does nothing if the dir already exists.
     *
     * @return $this
     */
    public function createLogDir()
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS . self::POSTNL_LOG_DIRECTORY;

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
            chmod($logDir, 0777);
        }

        return $this;
    }

    /**
     * Gets the knowledge base URL for a specified error code. First we check to see if we have an entry in config.xml
     * for this error code and if so, if it has an associated URL.
     *
     * @param string $errorCode The error code (for example: POSTNL-0001)
     *
     * @return string The URL or an empty string if no URL could be found
     */
    public function getErrorUrl($errorCode)
    {
        $error = Mage::getConfig()->getNode('tig/errors/' . $errorCode);
        /** @noinspection PhpUndefinedFieldInspection */
        if ($error !== false && $error->url) {
            /** @noinspection PhpUndefinedFieldInspection */
            return (string) $error->url;
        }

        return '';
    }

    /**
     * Adds an error message to the specified session based on an exception. The exception should contain a valid error
     * code in order to properly process the error. Exceptions without a (valid) error code will behave like a regular
     * $session->addError() call.
     *
     * @param string|Mage_Core_Model_Session_Abstract $session The session to which the messages will be added.
     * @param Exception                               $exception
     *
     * @return $this
     *
     * @see TIG_PostNL_Helper_Data::addSessionMessage()
     */
    public function addExceptionSessionMessage($session, Exception $exception)
    {
        /**
         * Get the error code, message type (hardcoded as 'error') and the message of the exception
         */
        $messageType      = 'error';
        $exceptionMessage = trim($exception->getMessage());
        $message          = $this->__('An error occurred while processing your request: ') . $exceptionMessage;
        $code             = $exception->getCode();
        if (empty($code)) {
            $code = $this->getErrorCodeByMessage($exceptionMessage);
        }

        return $this->addSessionMessage($session, $code, $messageType, $message);
    }

    /**
     * Gets an error code by looping through all known errors and if the specified message can be matched, returning the
     * associated code.
     *
     * @param string $message
     *
     * @return string|null
     */
    public function getErrorCodeByMessage($message)
    {
        /**
         * Get an array of all known errors
         */
        $errors = Mage::getConfig()->getNode('tig/errors')->asArray();

        /**
         * Loop through each error and compare it's message
         */
        foreach ($errors as $code => $error) {
            $errorMessage = (string) $error['message'];

            /**
             * If a the error's message and the specified message match, return the error code
             */
            if (strcasecmp($message, $errorMessage) === 0) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Add a message to the specified session. Message can be an error, a success message, an info message or a warning.
     * If a valid error code is supplied, the message will be prepended with the error code and a link to a
     * knowledgebase article will be appended.
     *
     * If no $code is specified, $messageType and $message will be required
     *
     * @param string|Mage_Core_Model_Session_Abstract $session The session to which the messages will be added.
     * @param string|null $code
     * @param string|null $messageType
     * @param string|null $message
     *
     * @return $this
     *
     * @see Mage_Core_Model_Session_Abstract::addMessage()
     *
     * @throws InvalidArgumentException
     * @throws TIG_PostNL_Exception
     */
    public function addSessionMessage($session, $code = null, $messageType = null, $message = null)
    {
        /***************************************************************************************************************
         * Check that the required arguments are available and valid.
         **************************************************************************************************************/

        /**
         * If $code is null or 0, $messageType and $message are required.
         */
        if (
            (is_null($code) || $code === 0)
            && (is_null($messageType) || is_null($message))
        ) {
            throw new InvalidArgumentException(
                "Warning: Missing argument for addSessionMessage method: 'messageType' and 'message' are required."
            );
        }

        /**
         * If the session is a string, treat it as a class name and instantiate it.
         */
        if (is_string($session) && strpos($session, '/') !== false) {
            $session = Mage::getSingleton($session);
        } elseif (is_string($session)) {
            $session = Mage::getSingleton($session . '/session');
        }

        /**
         * If the session could not be loaded or is not of the correct type, throw an exception.
         */
        if (!$session
            || !is_object($session)
            || !($session instanceof Mage_Core_Model_Session_Abstract)
        ) {
            throw new TIG_PostNL_Exception(
                $this->__('Invalid session requested.'),
                'POSTNL-0088'
            );
        }

        $errorMessage = $this->getSessionMessage($code, $messageType, $message);

        /***************************************************************************************************************
         * Add the error to the session.
         **************************************************************************************************************/

        /**
         * The method we'll use to add the message to the session has to be built first.
         */
        $addMethod = 'add' . ucfirst($messageType);

        /**
         * If the method doesn't exist, throw an exception.
         */
        if (!method_exists($session, $addMethod)) {
            throw new TIG_PostNL_Exception(
                $this->__('Invalid message type requested: %s.', $messageType),
                'POSTNL-0094'
            );
        }

        /**
         * Add the message to the session.
         */
        $session->$addMethod($errorMessage);

        return $this;
    }

    /**
     * Formats a message string so it can be added as a session message.
     *
     * @param null|string $code
     * @param null|string $messageType
     * @param null|string $message
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     * @throws InvalidArgumentException
     */
    public function getSessionMessage($code = null, $messageType = null, $message = null)
    {
        /**
         * If $code is null or 0, $messageType and $message are required.
         */
        if (
            (is_null($code) || $code === 0)
            && (is_null($messageType) || is_null($message))
        ) {
            throw new InvalidArgumentException(
                "Warning: Missing argument for addSessionMessage method: 'messageType' and 'message' are required."
            );
        }

        /***************************************************************************************************************
         * Get the actual error from config.xml if it's available.
         **************************************************************************************************************/

        $error = false;
        $link = false;

        if (!is_null($code) && $code !== 0) {
            /**
             * get the requested code and if possible, the knowledgebase link
             */
            $error = Mage::getConfig()->getNode('tig/errors/' . $code);
            if ($error !== false) {
                /** @noinspection PhpUndefinedFieldInspection */
                $link = (string) $error->url;
            }
        }

        /***************************************************************************************************************
         * Check that the required 'message' and 'messageType' components are available. If they are not yet available,
         * we'll try to read them from the error itself.
         **************************************************************************************************************/

        /**
         * If the specified error was found and no message was supplied, get the error's default message.
         */
        if ($error && !$message) {
            /** @noinspection PhpUndefinedFieldInspection */
            $message = (string) $error->message;
        }

        /**
         * If we still don't have a valid message, throw an exception.
         */
        if (!$message) {
            throw new TIG_PostNL_Exception(
                $this->__('No message supplied.'),
                'POSTNL-0089'
            );
        }

        /**
         * If the specified error was found and no message type was supplied, get the error's default type.
         */
        if ($error && !$messageType) {
            /** @noinspection PhpUndefinedFieldInspection */
            $messageType = (string) $error->type;
        }


        /**
         * If we still don't have a valid message type, throw an exception.
         */
        if (!$messageType) {
            throw new TIG_PostNL_Exception(
                $this->__('No message type supplied.'),
                'POSTNL-0090'
            );
        }

        /***************************************************************************************************************
         * Build the actual message we're going to add. The message will consist of the error code, followed by the
         * actual message and finally a link to the knowledge base. Only the message part is required.
         **************************************************************************************************************/

        /**
         * Flag that determines whether the error code and knowledgebase link will be included in the error message
         * (if available).
         */
        $canShowErrorDetails = $this->_canShowErrorDetails();

        /**
         * Lets start with the error code if it's present. It will be formatted as "[POSTNL-0001]".
         */
        $errorMessage = '';
        if ($canShowErrorDetails
            && !is_null($code)
            && $code !== 0
        ) {
            $errorMessage .= "[{$code}] ";
        }

        /**
         * Add the actual message. This is the only required part. The code and link are optional.
         */
        $errorMessage .= $this->__($message);

        /**
         * Add the link to the knowledgebase if we have one.
         */
        if ($canShowErrorDetails && $link) {
            $errorMessage .= ' <a href="'
                . $link
                . '" target="_blank" class="postnl-message">'
                . $this->__('Click here for more information from the TIG knowledgebase.')
                . '</a>';
        }

        return $errorMessage;
    }

    /**
     * @param null $quote
     *
     * @return bool
     */
    public function getQuoteIdCheckType($quote = null)
    {
        if ($quote === null) {
            $quote = $this->getQuote();
        }

        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');

        $shipmentType = false;
        if ($this->quoteIsAgeCheck($quote)) {
            $shipmentType = $deliveryOptionsHelper::IDCHECK_TYPE_AGE;
        }

        if ($this->quoteIsBirthdayCheck($quote)) {
            $shipmentType = $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY;
        }

        if ($this->quoteIsIdCheck($quote)) {
            $shipmentType = $deliveryOptionsHelper::IDCHECK_TYPE_ID;
        }

        return $shipmentType;
    }

    /**
     * @return bool
     */
    public function isIdevOsc()
    {
        return Mage::getStoreConfig(self::XPATH_CHECKOUT_EXTENSION) == 'idev_onestepcheckout';
    }

    /**
     * Checks to see if we can show error details (error code and knowledgebase link) in the frontend when an error
     * occurs.
     *
     * @return boolean
     */
    protected function _canShowErrorDetails()
    {
        /**
         * We can always show error details in the admin area
         */
        if ($this->isAdmin()) {
            return true;
        }

        /**
         * Check if the show_error_details_in_frontend setting is set to true
         */
        $storeId = Mage::app()->getStore()->getId();
        if (Mage::getStoreConfigFlag(self::XPATH_SHOW_ERROR_DETAILS_IN_FRONTEND, $storeId)) {
            return true;
        }

        return false;
    }

    /**
     * @param array|string           $types
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    protected function _hasQuotePostnlProductType($types, Mage_Sales_Model_Quote $quote = null)
    {
        if (!is_array($types)) {
            $types = array($types);
        }

        if ($quote === null) {
            $quote = $this->getQuote();
        }

        $quoteHasIDCheckType = false;
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            /** @noinspection PhpUndefinedMethodInspection */
            $postnlProductType = $quoteItem->getProduct()->getPostnlProductType();

            foreach ($types as $type) {
                if ($postnlProductType == $type) {
                    $quoteHasIDCheckType = true;
                    break;
                }
            }
        }

        return $quoteHasIDCheckType;
    }

    /**
     * Save the stored time zones to the PostNl cache.
     */
    public function __destruct()
    {
        if ($this->getStoreTimeZones() && $this->getCache()) {
            $this->getCache()
                 ->setStoreTimeZones($this->getStoreTimeZones())
                 ->saveCache();
        }
    }
}
