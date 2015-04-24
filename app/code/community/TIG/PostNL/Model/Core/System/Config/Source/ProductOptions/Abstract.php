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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
abstract class TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * Xpath to supported options configuration setting
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/grid/supported_product_options';

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var array
     */
    protected $_groups = array();

    /**
     * Gets all possible product options matching an array of flags.
     *
     * @param array   $flags
     * @param boolean $asFlatArray
     * @param boolean $checkAvailable
     *
     * @return array
     */
    public function getOptions($flags = array(), $asFlatArray = false, $checkAvailable = false)
    {
        $options = $this->_options;
        if (!empty($flags)) {
            foreach ($options as $key => $option) {
                if (!$this->_optionMatchesFlags($option, $flags)) {
                    unset($options[$key]);
                }
            }
        }

        if ($checkAvailable) {
            $this->_filterAvailable($options);
        }

        $this->_translateOptions($options);

        if ($asFlatArray) {
            $this->_flattenOptionArray($options);
        }

        return $options;
    }

    /**
     * Gets product options grouped by their 'group' key.
     *
     * @param array   $flags
     * @param boolean $filterAvailable
     *
     * @return array
     */
    public function getGroupedOptions($flags = array(), $filterAvailable = false)
    {
        $options = $this->getOptions($flags, false, $filterAvailable);

        if (empty($this->_groups)) {
            return $options;
        }

        $groupedOptions = $this->_groupOptions($options);

        return $groupedOptions;
    }


    /**
     * Returns an option array for all possible PostNL product options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by getOptions();
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvailableOptions($flat = false)
    {
        return $this->getOptions(array(), $flat, true);
    }

    /**
     * Checks if an option array item is valid for a given array of flags.
     *
     * @param array $option
     * @param array $flags
     *
     * @return bool
     */
    protected function _optionMatchesFlags($option, $flags)
    {
        foreach($flags as $key => $value) {
            if (!array_key_exists($key, $option)) {
                return false;
            }

            if ($option[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Groups an array of options based on the 'group' key.
     *
     * @param $options
     *
     * @return array
     */
    protected function _groupOptions($options)
    {
        $helper = Mage::helper('postnl');
        $groups = $this->_groups;

        $sortedOptions = array();
        foreach ($options as $key => $option) {
            if (!array_key_exists('group', $option)) {
                continue;
            }

            $group = $option['group'];
            $sortedOptions[$group][$key] = $option;
        }

        $groupedOptions = array();
        foreach ($groups as $group => $label) {
            if (!array_key_exists($group, $sortedOptions)) {
                continue;
            }

            $groupedOptions[$group] = array(
                'label' => $helper->__($label),
                'value' => $sortedOptions[$group],
            );
        }

        return $groupedOptions;
    }

    /**
     * Translates the labels of an option array.
     *
     * @param array $options
     *
     * @return array
     */
    protected function _translateOptions(array &$options)
    {
        $helper = Mage::helper('postnl');
        foreach ($options as &$option) {
            $option['label'] = $helper->__($option['label']);
        }

        return $options;
    }

    /**
     * Filters the options based on options that are available.
     *
     * @param array $options
     *
     * @return array
     */
    protected function _filterAvailable(&$options)
    {
        $helper = Mage::helper('postnl');
        $canUseEpsBEOnly = $helper->canUseEpsBEOnlyOption();

        $storeId = Mage::app()->getStore()->getId();

        /**
         * Get the list of supported product options from the shop's configuration
         */
        $supportedOptions = Mage::getStoreConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS, $storeId);
        $supportedOptionsArray = explode(',', $supportedOptions);
        if ($canUseEpsBEOnly) {
            $supportedOptionsArray[] = '4955';
        }

        foreach ($options as $key => $option) {
            $code = $option['value'];
            if (!in_array($code, $supportedOptionsArray)) {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * Flattens an option array.
     *
     * @param array $options
     *
     * @return array
     */
    protected function _flattenOptionArray(&$options)
    {
        $flatArray = array();
        foreach ($options as $option) {
            $flatArray[$option['value']] = $option['label'];
        }

        $options = $flatArray;

        return $options;
    }
}