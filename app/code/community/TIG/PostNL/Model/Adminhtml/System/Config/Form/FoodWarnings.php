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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Adminhtml_System_Config_Form_FoodWarnings extends Mage_Core_Model_Config_Data
{
    /**
     * Country code for the Netherlands.
     */
    const COUNTRY_CODE_NL = 'NL';

    /**
     * String values for the different warnings.
     */
    const WARNING_DOMESTIC_COUNTRY = 'The domestic country is not set to NL. Please keep in mind that Food Delivery is only available in the Netherlands.';
    const WARNING_AVAIALBLE_PRODUCT_CODES = 'You have enabled food delivery, but have not yet enabled a Food product code. Please enable a Food product code in Advanced -> User Settings.';
    const WARNING_ALLOW_SPECIFIC_COUNTRIES = 'You currently selected the options to ship to multiple countries. It seems that these countries also include non-domestic countries. Please keep in mind that Food deliveries can only be shipped to the Netherlands.';

    /**
     * Xpaths to allow specific/all countries for both PostNL specific config and general config.
     */
    const XPATH_ALLOW_SPECIFIC_COUNTRIES  = 'carriers/postnl/sallowspecific';
    const XPATH_SELECT_SPECIFIC_COUNTRIES = 'carriers/postnl/specificcountry';
    const XPATH_GENERAL_ALLOWED_COUNTRIES = 'general/country/allow';

    /**
     * Empty array to insert warnings into.
     * @var array
     */
    protected $_warnings = array();

    /**
     * Empty array to insert food product options into.
     * @var array
     */
    protected $_foodProductOptions = array();

    /**
     * Empty array to insert available product options into.
     * @var array
     */
    protected $_availableProductOptions = array();

    /**
     * Default set scope level.
     * @var int
     */
    protected $_scope = 0;

    /**
     * @var TIG_PostNL_Helper_Data
     */
    protected $_helper;

    /**
     * @var TIG_PostNL_Helper_CIF
     */
    protected $_cifHelper;

    /**
     * @var TIG_PostNL_Helper_Adminhtml
     */
    protected $_adminHelper;

    /**
     * Constructor function which will set some values which will be needed later.
     */
    protected function _construct()
    {
        $this->_helper = Mage::helper('postnl');
        $this->_cifHelper = Mage::helper('postnl/cif');
        $this->_adminHelper = Mage::helper('postnl/adminhtml');

        $this->_scope = $this->_adminHelper->getCurrentScope();
    }

    /**
     * Function which will direct to the different checks which need to be executed.
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->_checkForDomesticCountry();
        $this->_checkForFoodProductOptions();
        $this->_checkForShippingCountries();

        $this->setValue($this->_warnings);
    }

    /**
     * Adds the given warning to the _warning array property.
     *
     * @param string $warning
     *
     * @return $this
     */
    protected function _addWarning($warning)
    {
        $this->_warnings[] = $warning;

        return $this;
    }

    /**
     * Checks if the current domestic country is indeed the Netherlands,
     * since it is impossible to ship food outside the Netherlands.
     *
     * @return $this
     */
    protected function _checkForDomesticCountry()
    {
        $domesticCountry = $this->getDomesticCountry();

        if ($domesticCountry != self::COUNTRY_CODE_NL) {
            $this->_addWarning(self::WARNING_DOMESTIC_COUNTRY);
        }

        return $this;
    }

    /**
     * Checks if at least 1 food product code is available.
     * If this is not the case, the PostNL shipping method cannot be shown in the checkout.
     *
     * @return $this
     */
    protected function _checkForFoodProductOptions()
    {
        $foodProductOptions = $this->getFoodProductOptions();
        $availableProductOptions = $this->getAvailableProductOptions();

        $available = false;

        foreach ($foodProductOptions as $productCode => $foodProductOption) {
            if (!empty($availableProductOptions[$productCode])) {
                $available = true;
                break;
            }
        }

        if (!$available) {
            $this->_addWarning(self::WARNING_AVAIALBLE_PRODUCT_CODES);
        }

        return $this;
    }

    /**
     * Checks for the current scope if the Netherlands is the only country that can be shipped to.
     * Since it is impossible to ship food deliveries outside the Netherlands, this check is built to prevent problems
     * for the merchant.
     *
     * @return $this
     */
    protected function _checkForShippingCountries()
    {
        $availableCountries = $this->getAllAvailableCountries();

        if ($availableCountries != array(self::COUNTRY_CODE_NL)) {
            $this->_addWarning(self::WARNING_ALLOW_SPECIFIC_COUNTRIES);
        }

        return $this;
    }

    /**
     * Returns the current set Domestic country.
     *
     * @return string
     */
    public function getDomesticCountry()
    {
        if (!isset($this->_domesticCountry)) {
            $this->_domesticCountry = $this->_helper->getDomesticCountry();
        }

        return $this->_domesticCountry;
    }

    /**
     * Returns all options which are associated with food delivery.
     *
     * @return array
     */
    public function getFoodProductOptions()
    {
        if (!$this->_foodProductOptions) {
            $dryProductCodes = $this->_cifHelper->getFoodProductCodes();
            $cooledProductCodes = $this->_cifHelper->getCooledProductCodes();
            $this->_foodProductOptions = $dryProductCodes + $cooledProductCodes;
        }

        return $this->_foodProductOptions;
    }

    /**
     * Returns all currently active product options.
     *
     * @return array
     */
    public function getAvailableProductOptions()
    {
        if (!$this->_availableProductOptions) {
            /** @var TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions $allProductOptionsModel */
            $allProductOptionsModel = Mage::getModel('postnl_core/system_config_source_allProductOptions');
            $this->_availableProductOptions = $allProductOptionsModel->getOptions(array(), true, true);
        }

        return $this->_availableProductOptions;
    }

    /**
     * Returns all countries where the current scope can ship to.
     */
    public function getAllAvailableCountries()
    {
        $postnlAllowSpecific = Mage::getStoreConfig(self::XPATH_ALLOW_SPECIFIC_COUNTRIES, $this->_scope);
        if ($postnlAllowSpecific) {
            $availableCountries = Mage::getStoreConfig(self::XPATH_SELECT_SPECIFIC_COUNTRIES, $this->_scope);
        } else {
            $availableCountries = Mage::getStoreConfig(self::XPATH_GENERAL_ALLOWED_COUNTRIES, $this->_scope);
        }

        return explode(',', $availableCountries);
    }

}
