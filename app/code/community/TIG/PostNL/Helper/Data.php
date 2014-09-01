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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
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
     * XML path to postnl general active/inactive setting.
     */
    const XPATH_EXTENSION_ACTIVE = 'postnl/general/active';

    /**
     * XML path to test/live mode config option.
     */
    const XPATH_TEST_MODE = 'postnl/cif/mode';

    /**
     * XML path to the test mode allowed config option.
     */
    const XPATH_TEST_MODE_ALLOWED = 'postnl/advanced/allow_test_mode';

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
    const XPATH_USE_GLOBALPACK = 'postnl/cif/use_globalpack';

    /**
     * Xpath to use_buspakje setting.
     */
    const XPATH_USE_BUSPAKJE = 'postnl/cif_labels_and_confirming/use_buspakje';

    /**
     * XPATH to allow EPS BE only product option setting.
     */
    const XPATH_ALLOW_EPS_BE_ONLY_OPTION = 'postnl/cif_product_options/allow_eps_be_only_options';

    /**
     * XML path to weight unit used
     */
    const XPATH_WEIGHT_UNIT = 'postnl/cif_labels_and_confirming/weight_unit';

    /**
     * Xpath to the buspakje calculation mode setting.
     */
    const XPATH_BUSPAKJE_CALC_MODE = 'postnl/cif_labels_and_confirming/buspakje_calculation_mode';

    /**
     * Minimum PHP version required by this extension.
     */
    const MIN_PHP_VERSION = '5.3.0';

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
            'postnl/cif_sender_address/lastname',
            'postnl/cif_sender_address/company',
        ),
        'postnl/cif_sender_address/streetname',
        'postnl/cif_sender_address/housenumber',
        'postnl/cif_sender_address/postcode',
        'postnl/cif_sender_address/city',
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
        'postnl/cif/use_globalpack',
        'postnl/cif/global_barcode_type',
        'postnl/cif/global_barcode_range',
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
     * THe current server's memory limit.
     *
     * @var int
     */
    protected $_memoryLimit;

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

        $cache = Mage::getSingleton('postnl_core/cache');
        if (!$cache->canUseCache()) {
            $cache = false;
        }

        $this->setCache($cache);
        return $cache;
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
        if (!Mage::helper('postnl/carrier')->isPostnlShippingMethod($shippingMethod)) {
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
     * Checks to see if the module may ship to the Netherlands using PostNL standard shipments.
     *
     * @return boolean
     */
    public function canUseStandard()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUseStandard()) {
            return $cache->getPostnlCoreCanUseStandard();
        }

        $standardProductOptions = Mage::getModel('postnl_core/system_config_source_standardProductOptions')
                                      ->getAvailableOptions();
        if (empty($standardProductOptions)) {
            if ($cache) {
                $cache->setPostnlCoreCanUseStandard(false)
                      ->saveCache();
            }

            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreCanUseStandard(true)
                  ->saveCache();
        }

        return true;
    }

    /**
     * Checks to see if the module may ship using PakjeGemak.
     *
     * @return boolean
     */
    public function canUsePakjeGemak()
    {
        $cache = $this->getCache();

        if ($cache && $cache->hasPostnlCoreCanUsePakjeGemak()) {
            return $cache->getPostnlCoreCanUsePakjeGemak();
        }

        $pakjeGemakProductoptions = Mage::getModel('postnl_core/system_config_source_pakjeGemakProductOptions')
                                        ->getAvailableOptions();

        if (empty($pakjeGemakProductoptions)) {
            if ($cache) {
                $cache->setPostnlCoreCanUsePakjeGemak(false)
                      ->saveCache();
            }

            return false;
        }

        if ($cache) {
            $cache->setPostnlCoreCanUsePakjeGemak(true)
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

        $euProductOptions = Mage::getModel('postnl_core/system_config_source_euProductOptions')
                                ->getAvailableOptions();

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

        $globalProductOptions = Mage::getModel('postnl_core/system_config_source_globalProductOptions')
                                    ->getAvailableOptions();

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

        $isBuspakjeActive = Mage::getStoreConfigFlag(self::XPATH_USE_BUSPAKJE);

        if (!$isBuspakjeActive) {
            if ($cache) {
                $cache->setPostnlCoreCanUseBuspakje(false)
                      ->saveCache();
            }

            return false;
        }

        $buspakjeProductOptions = Mage::getModel('postnl_core/system_config_source_buspakjeProductOptions')
                                      ->getAvailableOptions();

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
        /**
         * @var Mage_Admin_Model_User $adminUser
         */
        $adminUser = Mage::getSingleton('admin/session')->getUser();
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

            /**
             * The max qty attribute is only available on simple products.
             */
            if ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
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
        if ($totalWeight > 2) {
            if ($registerReason) {
                Mage::register('postnl_reason_not_buspakje', 'weight');
            }
            return false;
        }

        /**
         * If the combined qty ratios of the items is more than 1 this is not a buspakje.
         */
        if ($totalQtyRatio > 1) {
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
            case 'print_packing_slips':
                $aclPath = 'postnl/shipment/actions/print_label/print_packing_slips';
                break;
            case 'convert_to_buspakje':
                $aclPath = 'postnl/shipment/actions/convert/to_buspakje';
                break;
            case 'convert_to_package':
                $aclPath = 'postnl/shipment/actions/convert/to_package';
                break;
            case 'change_product_code':
                $aclPath = 'postnl/shipment/actions/convert/change_product_code';
                break;
            case 'confirm': //no break
            case 'print_label': //no break
            case 'reset_confirmation': //no break
            case 'delete_labels': //no break
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

        $isAllowed = Mage::getSingleton('admin/session')->isAllowed($aclPath);
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

        $testMode = Mage::getStoreConfigFlag(self::XPATH_TEST_MODE, $storeId);

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
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            return false;
        }

        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        Mage::unregister('postnl_core_is_enabled_errors');

        /**
         * Check if the module has been enabled
         */
        $enabled = Mage::getStoreConfigFlag(self::XPATH_EXTENSION_ACTIVE, $storeId);
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
        $postnlShippingMethods       = Mage::helper('postnl/carrier')->getPostnlShippingMethods();
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
                $shippingMethodSectionurl = Mage::helper("adminhtml")->getUrl(
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
        $baseCurrencyCode = Mage::getModel('core/store')->load($storeId)->getBaseCurrencyCode();
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
        if ($isActivated != 2) {
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
             * @var Varien_Simplexml_Element $section
             */
            $configFields = Mage::getSingleton('adminhtml/config');
            $section      = $configFields->getSections('postnl')->postnl;
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
        $sectionGroup = $section->groups->$group;

        $label      = (string) $sectionGroup->fields->$field->label;
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
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            return false;
        }

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
        if ($error !== false && $error->url) {
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
                . $this->__('Click here for more information from the TiG knowledgebase.')
                . '</a>';
        }

        return $errorMessage;
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
}
