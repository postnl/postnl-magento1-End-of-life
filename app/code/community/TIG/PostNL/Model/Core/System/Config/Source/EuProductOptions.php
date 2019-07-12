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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_System_Config_Source_EuProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'             => '4952',
            'label'             => 'EU Pack Special Consumer (incl. signature)',
            'isExtraCover'      => false,
            'isEvening'         => false,
            'isSunday'          => false,
            'countryLimitation' => false,
            'group'             => 'eu_options',
        ),
        array(
            'value'             => '4938',
            'label'             => 'EU Pack Special Evening (incl. signature)',
            'isExtraCover'      => false,
            'isEvening'         => true,
            'isCod'             => false,
            'isSameDay'         => false,
            'statedAddressOnly' => false,
            'countryLimitation' => false,
            'group'             => 'eu_options',
        ),
        array(
            'value'         => '4955',
            'label'         => 'EU Pack Standard (Belgium only, no signature)',
            'isEvening'     => false,
            'isBelgiumOnly' => true,
            'isExtraCover'  => false,
            'group'         => 'eu_options',
        ),
        array(
            'value'         => '4941',
            'label'         => 'EU Pack Standard Evening (Belgium only, no signature)',
            'isEvening'     => true,
            'isBelgiumOnly' => true,
            'isExtraCover'  => false,
            'group'         => 'eu_options',
        )
    );

    /**
     * Gets all possible options.
     *
     * @param array $flags
     * @param bool  $asFlatArray
     * @param bool  $checkAvailable
     *
     * @return array
     */
    public function getOptions($flags = array(), $asFlatArray = false, $checkAvailable = false)
    {
        $options = parent::getOptions($flags, $asFlatArray, $checkAvailable);

        if (!$this->getHelper()->canUseEpsBEOnlyOption()) {
            $options = $this->removeOptions(array('4955', '4941'), $options);
        }

        /** PEPS is not compatible with Evening */
        if (isset($flags['isEvening']) && $flags['isEvening']) {
            return $options;
        }

        if ($this->getHelper()->isPepsAllowed()) {
            /** @var TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions $allOptions */
            $allOptions = Mage::getModel('postnl_core/system_config_source_allProductOptions');
            $pepsProducts = $allOptions->getPepsOptions($asFlatArray);
            $options += $pepsProducts;
        }

        return $options;
    }

    /**
     * @param $optionsToRemove
     * @param $options
     *
     * @return array
     */
    public function removeOptions($optionsToRemove, $options)
    {
        return array_filter($options, function ($option) use ($optionsToRemove) {
            $option = isset($option['value']) ? $option['value'] : $option;
            return !in_array($option, $optionsToRemove);
        });
    }

    /**
     * Get available avond options.
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvailableAvondOptions($flat = false)
    {
        return $this->getOptions(array('isEvening' => true), $flat, true);
    }
}
