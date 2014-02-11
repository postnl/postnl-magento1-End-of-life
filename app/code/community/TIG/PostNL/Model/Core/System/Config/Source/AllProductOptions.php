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
class TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions
{
    /**
     * XML path to supported options configuration setting
     */
    const XML_PATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/cif_product_options/supported_product_options';

    /**
     * Returns an option array for all possible PostNL product options
     *
     * @return array
     *
     * @todo implement COD
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $availableOptions = array(
            'standard_options' => array(
                'label' => $helper->__('Domestic options'),
                'value' => array(
                    '3085' => array(
                        'value' => '3085',
                        'label' => $helper->__('Standard shipment'),
                    ),
                    /**
                     * These are not currently implemented
                     *
                     * @todo implement these options
                     */
                    /*'3086' => array(
                        'value' => '3086',
                        'label' => $helper->__('COD'),
                    ),
                    '3091' => array(
                        'value' => '3091',
                        'label' => $helper->__('COD + Extra cover'),
                    ),
                    '3093' => array(
                        'value' => '3093',
                        'label' => $helper->__('COD + Return when not home'),
                    ),
                    '3097' => array(
                        'value' => '3097',
                        'label' => $helper->__('COD + Extra cover + Return when not home'),
                    ),*/
                    '3087' => array(
                        'value'        => '3087',
                        'label'        => $helper->__('Extra Cover'),
                        'isExtraCover' => true,
                    ),
                    '3094' => array(
                        'value'        => '3094',
                        'label'        => $helper->__('Extra cover + Return when not home'),
                        'isExtraCover' => true,
                    ),
                    '3189' => array(
                        'value' => '3189',
                        'label' => $helper->__('Signature on delivery'),
                    ),
                    '3089' => array(
                        'value' => '3089',
                        'label' => $helper->__('Signature on delivery + Delivery to stated address only'),
                    ),
                    '3389' => array(
                        'value' => '3389',
                        'label' => $helper->__('Signature on delivery + Return when not home'),
                    ),
                    '3096' => array(
                        'value' => '3096',
                        'label' => $helper->__('Signature on delivery + Deliver to stated address only + Return when not home'),
                    ),
                    '3090' => array(
                        'value' => '3090',
                        'label' => $helper->__('Delivery to neighbour + Return when not home'),
                    ),
                    '3385' => array(
                        'value' => '3385',
                        'label' => $helper->__('Deliver to stated address only'),
                    ),
                    '3390' => array(
                        'value' => '3390',
                        'label' => $helper->__('Deliver to stated address only + Return when not home'),
                    ),
                ),
            ),
            'pakjegemak_options' => array(
                'label' => $helper->__('Post Office options'),
                'value' => array(
                    /**
                     * These are not currently implemented
                     *
                     * @todo implement these options
                     */
                    /*'3535' => array(
                        'value' => '3535',
                        'label' => $helper->__('Post Office + COD')
                    ),
                    '3545' => array(
                        'value' => '3545',
                        'label' => $helper->__('Post Office + COD + Notification')
                    ),
                    '3536' => array(
                        'value' => '3536',
                        'label' => $helper->__('Post Office + COD + Extra Cover')
                    ),
                    '3546' => array(
                        'value' => '3546',
                        'label' => $helper->__('Post Office + COD + Extra Cover + Notification')
                    ),*/
                    '3534' => array(
                        'value'        => '3534',
                        'label'        => $helper->__('Post Office + Extra Cover'),
                        'isExtraCover' => true,
                    ),
                    '3544' => array(
                        'value'        => '3544',
                        'label'        => $helper->__('Post Office + Extra Cover + Notification'),
                        'isExtraCover' => true,
                    ),
                    '3533' => array(
                        'value' => '3533',
                        'label' => $helper->__('Post Office + Signature on Delivery')
                    ),
                    '3543' => array(
                        'value' => '3543',
                        'label' => $helper->__('Post Office + Signature on Delivery + Notification')
                    ),
                ),
            ),
            'eu_options' => array(
                'label' => $helper->__('EU options'),
                'value' => array(
                    '4952' => array(
                        'value' => '4952',
                        'label' => $helper->__('EU Pack Special Consumer (incl. signature)'),
                    ),
                    /**
                     * This option will be removed in v1.2.0
                     *
                     * @deprecated v1.1.2
                     */
                    '4955' => array(
                        'value' => '4955',
                        'label' => $helper->__('EU Pack Standard (Belgium only, no signature)'),
                        'isBelgiumOnly' => true,
                    ),
                    /**
                     * These are not currently implemented
                     *
                     * @todo implement these options
                     */
                    /*'4950' => array(
                        'value' => '4950',
                        'label' => $helper->__('EU Pack Special (B2B)'),
                    ),
                    '4954' => array(
                        'value' => '4954',
                        'label' => $helper->__('EU Pack Special COD (Belgium and Luxembourg only)'),
                    ),*/
                ),
            ),
            'global_options' => array(
                'label' => $helper->__('Global options'),
                'value' => array(
                    '4945' => array(
                        'value'        => '4945',
                        'label'        => $helper->__('GlobalPack'),
                        'isExtraCover' => true,
                    ),
                ),
            ),
        );

        $this->_markDefault($availableOptions);

        return $availableOptions;
    }

    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
     *
     * @param boolean $withDefault Determines whether or not a 'default' option is prepended to the array
     * @param boolean $witHExtraCover Flag whether or not to include extra cover options
     * @param boolean|int $storeId
     * @param boolean $codesOnly
     *
     * @return array
     */
    public function getAvailableOptions($withDefault = false, $withExtraCover = true, $storeId = false, $codesOnly = false)
    {
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $helper = Mage::helper('postnl');
        $options = $this->toOptionArray();

        /**
         * Get a list of all possible options
         */
        $availableOptions = array();

        /**
         * prepend the 'default' option
         */
        if ($withDefault === true) {
            $availableOptions[] =  array(
                'value' => 'default',
                'label' => $helper->__('Use default'),
            );
        }

        /**
         * Get the list of supported product options from the shop's configuration
         */
        $supportedOptions = Mage::getStoreConfig(self::XML_PATH_SUPPORTED_PRODUCT_OPTIONS, $storeId);
        $supportedOptionsArray = explode(',', $supportedOptions);

        /**
         * Check each standard option to see if it's supprted
         */
        $availableStandardOptions = array();
        foreach ($options['standard_options']['value'] as $option) {
            if (!in_array($option['value'], $supportedOptionsArray)) {
                continue;
            }

            if (isset($option['isExtraCover']) && $withExtraCover !== true) {
                continue;
            }

            if ($codesOnly === true) {
                $availableOptions[] = $option['value'];
                continue;
            }

            $availableStandardOptions[] = $option;
        }

        /**
         * Check each pakje gemak option to see if it's supprted
         */
        $availablePakjeGemakOptions = array();
        foreach ($options['pakjegemak_options']['value'] as $option) {
            if (!in_array($option['value'], $supportedOptionsArray)) {
                continue;
            }

            if (isset($option['isExtraCover']) && $withExtraCover !== true) {
                continue;
            }

            if ($codesOnly === true) {
                $availableOptions[] = $option['value'];
                continue;
            }

            $availablePakjeGemakOptions[] = $option;
        }

        /**
         * Check each eu option to see if it's supprted
         */
        $availableEuOptions = array();
        foreach ($options['eu_options']['value'] as $option) {
            if (!in_array($option['value'], $supportedOptionsArray)) {
                continue;
            }

            if (isset($option['isExtraCover']) && $withExtraCover !== true) {
                continue;
            }

            if ($codesOnly === true) {
                $availableOptions[] = $option['value'];
                continue;
            }

            $availableEuOptions[] = $option;
        }

        /**
         * Check each eu option to see if it's supprted
         */
        $availableGlobalOptions = array();
        if ($helper->isGlobalAllowed()) {
            foreach ($options['global_options']['value'] as $option) {
                if (!in_array($option['value'], $supportedOptionsArray)) {
                    continue;
                }

                if (isset($option['isExtraCover']) && $withExtraCover !== true) {
                    continue;
                }

                if ($codesOnly === true) {
                    $availableOptions[] = $option['value'];
                    continue;
                }

                $availableGlobalOptions[] = $option;
            }
        }

        /**
         * If we only need the codes, we can return the $availableOptions array. Otherwise, we need to order and merge the
         * other arrays
         */
        if ($codesOnly === true) {
            return $availableOptions;
        }

        /**
         * group all available options
         */
        if (!empty($availableStandardOptions)) {
            $availableOptions['standard_options'] = array(
                'label' => $helper->__('Standard options'),
                'value' => $availableStandardOptions,
            );
        }

        if (!empty($availablePakjeGemakOptions)) {
            $availableOptions['pakjegemak_options'] = array(
                'label' => $helper->__('Post Office options'),
                'value' => $availablePakjeGemakOptions,
            );
        }

        if (!empty($availableEuOptions)) {
            $availableOptions['eu_options'] = array(
                'label' => $helper->__('EU options'),
                'value' => $availableEuOptions,
            );
        }

        if (!empty($availableGlobalOptions)) {
            $availableOptions['global_options'] = array(
                'label' => $helper->__('Global options'),
                'value' => $availableGlobalOptions,
            );
        }

        return $availableOptions;
    }

    /**
     * Get the list of available product options that have extra cover
     *
     * @return array
     */
    public function getExtraCoverOptions($valuesOnly = false)
    {
        /**
         * Get all available options
         */
        $availableOptions = $this->getAvailableOptions(false, true);

        /**
         * Loop through each optGroup and then each option to see if any of them have the isExtraCover flag.
         * Add these to the array of extra cover options.
         */
        $extraCoverOptions = array();
        foreach ($availableOptions as $optionGroup) {
            foreach ($optionGroup['value'] as $option) {
                /**
                 * Add the whole option (value, label and flags)
                 */
                if (isset($option['isExtraCover'])
                    && $option['isExtraCover']
                    && $valuesOnly !== true
                ) {
                    $extraCoverOptions[] = $option;
                    continue;
                }

                /**
                 * Only add the value
                 */
                if (isset($option['isExtraCover'])
                    && $option['isExtraCover']
                    && $valuesOnly === true
                ) {
                    $extraCoverOptions[] = $option['value'];
                    continue;
                }

                continue;
            }
        }

        return $extraCoverOptions;
    }

    /**
     * Marks the default values in the option array
     *
     * @param array &$options
     *
     * @return array
     */
    protected function _markDefault(&$options)
    {
        $helper = Mage::helper('postnl/cif');

        /**
         * Get an array of all default options
         */
        $defaultOptions = $helper->getDefaultProductOptions();


        /**
         * Mark each default option as default if it is present in the available options array
         */
        if (isset($options['standard_options'])
            && isset($options['standard_options']['value'])
            && isset($options['standard_options']['value'][$defaultOptions['dutch']])
        ) {
            $options['standard_options']['value'][$defaultOptions['dutch']]['label'] .= ' ' . $helper->__('(default)');
        }

        if (isset($options['pakjegemak_options'])
            && isset($options['pakjegemak_options']['value'])
            && isset($options['pakjegemak_options']['value'][$defaultOptions['eu']])
        ) {
            $options['pakjegemak_options']['value'][$defaultOptions['pakjegemak']]['label'] .= ' ' . $helper->__('(default)');
        }

        if (isset($options['eu_options'])
            && isset($options['eu_options']['value'])
            && isset($options['eu_options']['value'][$defaultOptions['eu']])
        ) {
            $options['eu_options']['value'][$defaultOptions['eu']]['label'] .= ' ' . $helper->__('(default)');
        }

        if (isset($options['global_options'])
            && isset($options['global_options']['value'])
            && isset($options['global_options']['value'][$defaultOptions['global']])
        ) {
            $options['global_options']['value'][$defaultOptions['global']]['label'] .= ' ' . $helper->__('(default)');
        }

        return $options;
    }
}
