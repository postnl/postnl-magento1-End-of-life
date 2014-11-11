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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
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
     * XML path to infinite label printing setting
     */
    const XPATH_INFINITE_LABEL_PRINTING = 'postnl/advanced/infinite_label_printing';

    /**
     * XML path to weight per parcel config setting
     */
    const XPATH_WEIGHT_PER_PARCEL = 'postnl/packing_slip/weight_per_parcel';

    /**
     * XML paths to default product options settings
     */
    const XPATH_DEFAULT_STANDARD_PRODUCT_OPTION       = 'postnl/grid/default_product_option';
    const XPATH_DEFAULT_EU_PRODUCT_OPTION             = 'postnl/cif_product_options/default_eu_product_option';
    const XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION         = 'postnl/cif_product_options/default_global_product_option';
    const XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION = 'postnl/cif_product_options/default_pakketautomaat_product_option';

    /**
     * Regular expression used to split street name from house number. This regex works well for dutch addresses, but
     * may fail for international addresses. We strongly recommend using split address lines instead.
     */
    const SPLIT_STREET_REGEX = '#\A(.*?)\s+(\d+[a-zA-Z]{0,1}\s{0,1}[-]{1}\s{0,1}\d*[a-zA-Z]{0,1}|\d+[a-zA-Z-]{0,1}\d*[a-zA-Z]{0,1})#';

    /**
     * Regular expression used to split house number and house number extension
     */
    const SPLIT_HOUSENUMBER_REGEX = '#^([\d]+)(.*)#s';

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
        'GR',
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
     * Array of supported shipment types.
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
     * Array of supported shipment types and their numeric counterparts.
     *
     * @var array
     */
    protected $_numericShipmentTypes = array(
        'Gift'              => 0,
        'Documents'         => 1,
        'Commercial Goods'  => 2,
        'Commercial Sample' => 3,
        'Returned Goods'    => 4,
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
     * Array of possible shipping phase codes.
     *
     * @var array
     */
    protected $_shippingPhaseCodes = array(
        '1'  => 'reported',
        '2'  => 'sorted',
        '3'  => 'distribution',
        '4'  => 'delivered',
        '99' => 'not_found',
    );

    /**
     * Array of countires which may send their full street data in a single line,
     * rather than having to split them into streetname, housenr and extension parts
     *
     * @var array
     */
    protected $_allowedFullStreetCountries = array(
        'NL',
        'BE'
    );

    /**
     * Array of EPS product codes and their combi-label counterparts.
     *
     * @var array
     */
    protected $_combiLabelProductCodes = array(
        '4940' => '4950',
        '4924' => '4954',
        '4946' => '4955',
        '4944' => '4952',
    );

    /**
     * @var null|array
     */
    protected $_supportedProductOptions = null;

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
     * Get an array of standard product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getStandardProductCodes($flat = true)
    {
        $standardProductCodes = Mage::getSingleton('postnl_core/system_config_source_standardProductOptions');
        return $standardProductCodes->getAvailableOptions($flat);
    }

    /**
     * Get an array of standard COD product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getStandardCodProductCodes($flat = true)
    {
        $standardProductCodes = Mage::getSingleton('postnl_core/system_config_source_standardProductOptions');
        return $standardProductCodes->getAvailableCodOptions($flat);
    }

    /**
     * Get an array of evening delivery product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvondProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_standardProductOptions');
        return $pakjeGemakProductCodes->getAvailableAvondOptions($flat);
    }

    /**
     * Get an array of evening delivery COD product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvondCodProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_standardProductOptions');
        return $pakjeGemakProductCodes->getAvailableAvondCodOptions($flat);
    }

    /**
     * Get an array of PakjeGemak product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getPakjeGemakProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');
        return $pakjeGemakProductCodes->getAvailableOptions($flat);
    }

    /**
     * Get an array of PakjeGemak COD product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getPakjeGemakCodProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');
        return $pakjeGemakProductCodes->getAvailableCodOptions($flat);
    }

    /**
     * Get an array of PakjeGemak Express product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getPgeProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');
        return $pakjeGemakProductCodes->getAvailablePgeOptions($flat);
    }

    /**
     * Get an array of PakjeGemak Express COD product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getPgeCodProductCodes($flat = true)
    {
        $pakjeGemakProductCodes = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');
        return $pakjeGemakProductCodes->getAvailablePgeCodOptions($flat);
    }

    /**
     * Get an array of pakketautomaat product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getPakketautomaatProductCodes($flat = true)
    {
        $pakketautomaatProductCodes = Mage::getSingleton(
            'postnl_core/system_config_source_pakketautomaatProductOptions'
        );
        return $pakketautomaatProductCodes->getAvailableOptions($flat);
    }

    /**
     * Get an array of eu product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getEuProductCodes($flat = true)
    {
        $euProductCodes = Mage::getSingleton('postnl_core/system_config_source_euProductOptions');
        return $euProductCodes->getAvailableOptions($flat);
    }

    /**
     * Get an array of global product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getGlobalProductCodes($flat = true)
    {
        $globalProductCodes = Mage::getSingleton('postnl_core/system_config_source_globalProductOptions');
        return $globalProductCodes->getAvailableOptions($flat);
    }

    /**
     * Get an array of buspakje product codes.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getBuspakjeProductCodes($flat = true)
    {
        $buspakjeProductCodes = Mage::getSingleton('postnl_core/system_config_source_buspakjeProductOptions');
        return $buspakjeProductCodes->getAvailableOptions($flat);
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
     * Get an array of numeric shipment types
     *
     * @return array
     */
    public function getNumericShipmentTypes()
    {
        return $this->_numericShipmentTypes;
    }

    /**
     * Get an array of possible shipping phases.
     *
     * @return array
     */
    public function getShippingPhases()
    {
        $shippingPhases = $this->_shippingPhases;
        foreach ($shippingPhases as &$value) {
            $value = $this->__($value);
        }

        return $shippingPhases;
    }

    /**
     * Get an array of possible shipping phase codes.
     *
     * @return array
     */
    public function getShippingPhaseCodes()
    {
        return $this->_shippingPhaseCodes;
    }

    /**
     * Get country IDs that allow fullstreet usage
     *
     * @return array
     */
    public function getAllowedFullStreetCountries()
    {
        return $this->_allowedFullStreetCountries;
    }

    /**
     * Get EPS combilabel codes.
     *
     * @return array
     */
    public function getCombiLabelProductCodes()
    {
        return $this->_combiLabelProductCodes;
    }

    /**
     * Checks if infinite label printing is enabled in the module configuration.
     *
     * @return boolean
     */
    public function allowInfinitePrinting()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $enabled = Mage::getStoreConfigFlag(self::XPATH_INFINITE_LABEL_PRINTING, $storeId);

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
     * @param TIG_PostNL_Model_Core_Shipment $shipment
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
    }

    /**
     * Get a list of available product options for a specified shipment
     *
     * @param Mage_Sales_Model_Order_Shipment|TIG_PostNL_Model_Core_Shipment $shipment
     *
     * @return array | null
     */
    public function getProductOptionsForShipment($shipment)
    {
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
            $tempPostnlShipment->setShipment($shipment);
        } else {
            $tempPostnlShipment = $shipment;
        }

        return $tempPostnlShipment->getAllowedProductOptions(false);
    }

    /**
     * Check if a given shipment is PakjeGemak
     *
     * @param TIG_PostNL_Model_Core_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     *
     * @see TIG_PostNL_Model_Core_Shipment->isPakjeGemakShipment();
     */
    public function isPakjeGemakShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            return $shipment->isPakjeGemakShipment();
        }

        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);

        return $tempPostnlShipment->isPakjeGemakShipment();
    }

    /**
     * Check if a given shipment is a pakketautomaat shipment.
     *
     * @param TIG_PostNL_Model_Core_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     *
     * @see TIG_PostNL_Model_Core_Shipment->isDutchShipment();
     */
    public function isPakketautomaatShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            return $shipment->isPakketautomaatShipment();
        }

        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);

        return $tempPostnlShipment->isPakketautomaatShipment();
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
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
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
     * @see TIG_PostNL_Model_Core_Shipment->isEuShipment();
     */
    public function isEuShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            return $shipment->isEuShipment();
        }

        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);

        return $tempPostnlShipment->isEuShipment();
    }

    /**
     * Check if a given shipment has a global destination
     *
     * @param TIG_PostNL_Model_Core_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     *
     * @see TIG_PostNL_Model_Core_Shipment->isGlobalShipment();
     */
    public function isGlobalShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            return $shipment->isGlobalShipment();
        }

        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);

        return $tempPostnlShipment->isGlobalShipment();
    }

    /**
     * Check if a given shipment is COD
     *
     * @param TIG_PostNL_Model_Core_Shipment|Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return boolean
     *
     * @see TIG_PostNL_Model_Core_Shipment->isCod();
     */
    public function isCodShipment($shipment)
    {
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        if ($shipment instanceof $postnlShipmentClass) {
            /**
             * @var TIG_PostNL_Model_Core_Shipment $shipment
             */
            return $shipment->isCod();
        }

        $tempPostnlShipment = Mage::getModel('postnl_core/shipment');
        $tempPostnlShipment->setShipment($shipment);

        return $tempPostnlShipment->isCod();
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
     *
     * @deprecated v1.3.0
     */
    public function getDefaultProductOptions()
    {
        $storeId = Mage::app()->getStore()->getId();

        $defaultDutchOption          = Mage::getStoreConfig(self::XPATH_DEFAULT_STANDARD_PRODUCT_OPTION, $storeId);
        $defaultEuOption             = Mage::getStoreConfig(self::XPATH_DEFAULT_EU_PRODUCT_OPTION, $storeId);
        $defaultGlobalOption         = Mage::getStoreConfig(self::XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION, $storeId);
        $defaultPakketautomaatOption = Mage::getStoreConfig(
            self::XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION,
            $storeId
        );

        $defaultOptions = array(
            'dutch'          => $defaultDutchOption,
            'eu'             => $defaultEuOption,
            'global'         => $defaultGlobalOption,
            'pakketautomaat' => $defaultPakketautomaatOption,
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
         * Only NL shipments support multi-colli shipments.
         */
        if (!$postnlShipment->isDutchShipment()) {
            return 1;
        }

        /**
         * Get this shipment's total weight.
         */
        $weight = $postnlShipment->getTotalWeight(true);

        /**
         * Get the weight per parcel.
         */
        $weightPerParcel = Mage::getStoreConfig(self::XPATH_WEIGHT_PER_PARCEL, $shipment->getStoreId());
        $weightPerParcel = $this->standardizeWeight($weightPerParcel, $shipment->getStoreId());

        /**
         * Calculate the number of parcels needed to ship the total weight of this shipment.
         */
        $parcelCount = ceil($weight / $weightPerParcel);

        if ($parcelCount < 1) {
            $parcelCount = 1;
        }

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

        $validator = new Zend_Validate_Db_RecordExists(
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
        $validator = new Zend_Validate_Db_RecordExists(
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
        $validator = new Zend_Validate_Db_RecordExists(
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
     * Retrieves street name, house number and house number extension from the shipping address.
     * The shipping address may be in multiple street lines configuration or single line configuration. In the case of
     * multi-line, each part of the street data will be in a separate field. In the single line configuration, each part
     * will be in the same field and will have to be split using PREG.
     *
     * PREG cannot be relied on as it is impossible to create a regex that can filter all possible street syntaxes.
     * Therefore we strongly recommend to use multiple street lines. This can be enabled in Magento community in
     * system > config > customer configuration. Or if you use Enterprise, in customers > attributes > manage customer
     * address attributes.
     *
     * @param int                                  $storeId
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param boolean                              $allowFullStreet
     *
     * @return array
     */
    public function getStreetData($storeId, $address, $allowFullStreet = true)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $splitStreet = Mage::helper('postnl/addressValidation')->useSplitStreet($storeId);

        /**
         * Website uses multi-line address mode
         */
        if ($splitStreet) {
            $streetData = $this->_getMultiLineStreetData($storeId, $address);

            /**
             * If $streetData is false it means a required field was missing. In this
             * case the alternative methods are used to obtain the address data.
             */
            if ($streetData !== false) {
                return $streetData;
            }
        }

        /**
         * Website uses single-line address mode
         */
        $allowedFullStreetCountries = $this->getAllowedFullStreetCountries();
        $fullStreet = $address->getStreetFull();

        /**
         * Select countries don't have to split their street values into seperate part
         */
        if ($allowFullStreet === true
            && in_array($address->getCountry(), $allowedFullStreetCountries)
        ) {
            $streetData = array(
                'streetname'           => '',
                'housenumber'          => '',
                'housenumberExtension' => '',
                'fullStreet'           => $fullStreet,
            );
            return $streetData;
        }

        /**
         * All other countries must split them using PREG
         */
        $streetData = $this->_getSplitStreetData($fullStreet);

        return $streetData;
    }

    /**
     * Retrieves street name, house number and housen umber extension from the shipping address in the multiple street
     * ines configuration.
     *
     * @param int                            $storeId
     * @param Mage_Sales_Model_Order_Address $address
     *
     * @return array
     */
    protected function _getMultiLineStreetData($storeId, $address)
    {
        $addressHelper = Mage::helper('postnl/addressValidation');

        $streetnameField = $addressHelper->getStreetnameField($storeId);
        $housenumberField = $addressHelper->getHousenumberField($storeId);

        $streetname = $address->getStreet($streetnameField);
        $housenumber = $address->getStreet($housenumberField);
        $housenumber = trim($housenumber);

        /**
         * If street or house number fields are empty, use alternative options to obtain the address data
         */
        if (empty($streetname) || empty($housenumber)) {
            return false;
        }

        /**
         * Split the house number into a number and an extension
         */
        $splitHouseNumber = $addressHelper->useSplitHousenumber();
        if ($splitHouseNumber) {
            $housenumberExtensionField = $addressHelper->getHousenumberExtensionField();
            $housenumberExtension      = $address->getStreet($housenumberExtensionField);

            /**
             * Make sure the house number is actually split.
             */
            if (!$housenumberExtension && !is_numeric($housenumber)) {
                $housenumberParts     = $this->_splitHousenumber($housenumber);
                $housenumber          = $housenumberParts['number'];
                $housenumberExtension = $housenumberParts['extension'];
            }
        } else {
            $housenumberParts     = $this->_splitHousenumber($housenumber);
            $housenumber          = $housenumberParts['number'];
            $housenumberExtension = $housenumberParts['extension'];
        }

        if (empty($housenumber)) {
            return false;
        }

        $streetData = array(
            'streetname'           => $streetname,
            'housenumber'          => $housenumber,
            'housenumberExtension' => $housenumberExtension,
            'fullStreet'           => '',
        );

        return $streetData;
    }

    /**
     * Splits street data into separate parts for street name, house number and extension.
     *
     * @param string $fullStreet The full street name including all parts
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _getSplitStreetData($fullStreet)
    {
        $result = preg_match(self::SPLIT_STREET_REGEX, $fullStreet, $matches);
        if (!$result || !is_array($matches)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid full street supplied: %s', $fullStreet),
                'POSTNL-0060'
            );
        }

        $streetname = '';
        $housenumber = '';
        if (isset($matches[1])) {
            $streetname = $matches[1];
        }

        if (isset($matches[2])) {
            $housenumber = $matches[2];
        }

        $housenumberParts = $this->_splitHousenumber($housenumber);
        $housenumber = $housenumberParts['number'];
        $housenumberExtension = $housenumberParts['extension'];

        $streetData = array(
            'streetname'           => $streetname,
            'housenumber'          => $housenumber,
            'housenumberExtension' => $housenumberExtension,
            'fullStreet'           => '',
        );

        return $streetData;
    }

    /**
     * Splits a supplier house number into a number and an extension.
     *
     * @param string $housenumber
     *
     * @return array
     *
     * @throws TIG_PostNL_Exception
     */
    protected function _splitHousenumber($housenumber)
    {
        $housenumber = trim($housenumber);
        $result = preg_match(self::SPLIT_HOUSENUMBER_REGEX, $housenumber, $matches);
        if (!$result || !is_array($matches)) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid housnumber supplied: %s', $housenumber),
                'POSTNL-0059'
            );
        }

        $extension = '';
        $number = '';
        if (isset($matches[1])) {
            $number = $matches[1];
        }

        if (isset($matches[2])) {
            $extension = trim($matches[2]);
        }

        $housenumberParts = array(
            'number' => $number,
            'extension' => $extension,
        );

        return $housenumberParts;
    }

    /**
     * Strips non-printable ASCII characters from a string.
     *
     * @param string &$string
     */
    public function stripNonPrintableCharacters(&$string)
    {
        /**
         * Remove the first 32 ASCII characters.
         */
        $string = preg_replace('/[\x00-\x1f]/', '', $string);
    }

    /**
     * Parses a CIF exception. If the last error number is a known error, we replace the message and code with our own.
     *
     * @param TIG_PostNL_Model_Core_Cif_Exception &$exception
     *
     * @return $this
     */
    public function parseCifException(TIG_PostNL_Model_Core_Cif_Exception &$exception)
    {
        $errorNumbers = $exception->getErrorNumbers();
        $errorNumber  = end($errorNumbers);

        $code    = $exception->getCode();
        $message = $exception->getMessage();
        switch ($errorNumber) {
            case '1':
                $code    = 'POSTNL-0181';
                $message = $this->__(
                    'It appears the PostNL username and/or password you have entered is incorrect.'
                );
                break;
            case '2':
                $code    = 'POSTNL-0182';
                $message = $this->__(
                    'Your PostNL account is unfortunately not allowed to perform this action. Please contact PostNL.'
                );
                break;
            case '9':
                $code    = 'POSTNL-0183';
                $message = $this->__(
                    'Unfortunately you have exceeded the maximum amount of PostNL requests you may send each minute.' .
                    ' Please wait a few minutes and try again. If this problem persists, please contact PostNL.'
                );
                break;
            case '10':
                $code    = 'POSTNL-0184';
                $message = $this->__('This PostNL service is currently disabled. Please contact PostNL.');
                break;
            case '11':
                $code    = 'POSTNL-0185';
                $message = $this->__(
                    "There was a problem connecting to PostNL's services. This may be due to a timeout. Please wait a " .
                    'few minutes and try again. If this problem persists, please contact PostNL.'
                );
                break;
            case '19':
                $code    = 'POSTNL-0186';
                $message = $this->__(
                    'Your PostNL customer code appears to be incorrect. Please make sure you have entered the correct' .
                    ' code.'
                );
                break;
            //no default
        }

        $exception->setCode($code)
                  ->setMessage($message);

        return $this;
    }

    /**
     * Logs a CIF request and response for debug purposes.
     *
     * N.B.: if file logging is enabled, the log will be forced
     *
     * @param Zend_Soap_Client $client
     *
     * @return $this
     *
     * @see Mage::log()
     *
     */
    public function logCifCall($client)
    {
        if (!$this->isLoggingEnabled()) {
            return $this;
        }

        $requestXml = $this->formatXml($client->getLastRequest());
        $responseXML = $this->formatXml($client->getLastResponse());

        $logMessage = "<<< REQUEST SENT >>>"
                    . PHP_EOL
                    . $requestXml
                    . PHP_EOL
                    . "<<< RESPONSE RECEIVED >>>"
                    . PHP_EOL
                    . $responseXML;

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CIF_DEBUG_LOG_FILE;
        $this->log($logMessage, Zend_Log::DEBUG, $file);

        return $this;
    }

    /**
     * Logs a CIF exception in the database and/or a log file.
     *
     * @param Mage_Core_Exception | TIG_PostNL_Model_Core_Cif_Exception $exception
     *
     * @return $this
     *
     * @see Mage::logException()
     */
    public function logCifException($exception)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }

        $logMessage = PHP_EOL . $exception->__toString();

        if ($exception instanceof TIG_PostNL_Model_Core_Cif_Exception) {
            $requestXml = $this->formatXml($exception->getRequestXml());
            $responseXML = $this->formatXml($exception->getResponseXml());

            $errorNumbers = $exception->getErrorNumbers();
            if (!empty($errorNumbers)) {
                $errorNumbers = implode(', ', $errorNumbers);
                $logMessage .= PHP_EOL . PHP_EOL . "Error numbers received: {$errorNumbers}\n";
            }

            $logMessage .= PHP_EOL
                         . "<<< REQUEST SENT >>>"
                         . PHP_EOL
                         . $requestXml
                         . PHP_EOL
                         . "<<< RESPONSE RECEIVED >>>"
                         . PHP_EOL
                         . $responseXML;
        }

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::CIF_EXCEPTION_LOG_FILE;
        $this->log($logMessage, Zend_Log::ERR, $file, false, true);

        return $this;
    }
}
