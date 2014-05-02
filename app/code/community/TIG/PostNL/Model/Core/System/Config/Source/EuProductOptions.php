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
class TIG_PostNL_Model_Core_System_Config_Source_EuProductOptions
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
     * @todo implement COD and extra cover
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $availableOptions = array(
            array(
                'value' => '4952',
                'label' => $helper->__('EU Pack Special Consumer (incl. signature)'),
            ),
            /**
             * This option has been removed in v1.1.4
             *
             * @deprecated v1.1.2
             */
            /*array(
                'value' => '4955',
                'label' => $helper->__('EU Pack Standard (Belgium only, no signature)'),
                'isBelgiumOnly' => true,
            ),*/
            /**
             * These are not currently implemented
             *
             * @todo implement these options
             */
            /*
            array(
                'value' => '4950',
                'label' => $helper->__('EU Pack Special (B2B)'),
            ),
            array(
                'value' => '4954',
                'label' => $helper->__('EU Pack Special COD (Belgium and Luxembourg only)'),
            ),*/
        );

        if ($helper->canUseEpsBEOnlyOption()) {
            $availableOptions['4955'] = array(
                'value'         => '4955',
                'label'         => $helper->__('EU Pack Standard (Belgium only, no signature)'),
                'isBelgiumOnly' => true,
            );
        }

        return $availableOptions;
    }

    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
     *
     * @param boolean|int $storeId
     * @param boolean     $codesOnly
     *
     * @return array
     */
    public function getAvailableOptions($storeId = false, $codesOnly = false)
    {
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $helper = Mage::helper('postnl');
        $canUseEpsBEOnly = $helper->canUseEpsBEOnlyOption();

        $options = $this->toOptionArray();

        /**
         * Get a list of all possible options
         */
        $availableOptions = array();

        /**
         * Get the list of supported product options from the shop's configuration
         */
        $supportedOptions = Mage::getStoreConfig(self::XML_PATH_SUPPORTED_PRODUCT_OPTIONS, $storeId);
        $supportedOptionsArray = explode(',', $supportedOptions);
        if ($canUseEpsBEOnly) {
            $supportedOptionsArray[] = '4955';
        }

        /**
         * Check each standard option to see if it's supprted
         */
        foreach ($options as $option) {
            if (!array_key_exists('value', $option)) {
                continue;
            }

            if (!in_array($option['value'], $supportedOptionsArray)) {
                continue;
            }

            if ($codesOnly === true) {
                $availableOptions[] = $option['value'];
                continue;
            }

            $availableOptions[] = $option;
        }

        return $availableOptions;
    }
}
