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
abstract class TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * XML path to supported options configuration setting
     */
    const XML_PATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/cif_product_options/supported_product_options';

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * Gets all possible product options matching an array of flags.
     *
     * @param array   $flags
     * @param boolean $asFlatArray
     *
     * @return array
     */
    public function getOptions($flags = array(), $asFlatArray = false)
    {
        $options = $this->_options;
        if (!empty($flags)) {
            foreach ($options as $key => $option) {
                if (!$this->_optionMatchesFlags($option, $flags)) {
                    unset($options[$key]);
                }
            }
        }

        $this->_translateOptions($options);

        if ($asFlatArray) {
            $this->_flattenOptionArray($options);
        }

        return $options;
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
        foreach ($option as $key => $value) {
            if (array_key_exists($key, $flags) && $value !== $flags[$key]) {
                return false;
            }
        }

        return true;
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
     * Flattens an option array.
     *
     * @param array $options
     *
     * @return array
     */
    protected function _flattenOptionArray($options)
    {
        $flatArray = array();
        foreach ($options as $option) {
            $flatArray[$option['value']] = $option['label'];
        }

        return $flatArray;
    }
}