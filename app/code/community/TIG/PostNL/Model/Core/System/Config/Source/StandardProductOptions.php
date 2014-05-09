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
class TIG_PostNL_Model_Core_System_Config_Source_StandardProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'        => '3085',
            'label'        => 'Standard shipment',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3087',
            'label'        => 'Extra Cover',
            'isExtraCover' => true,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3094',
            'label'        => 'Extra cover + Return when not home',
            'isExtraCover' => true,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3189',
            'label'        => 'Signature on delivery',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3089',
            'label'        => 'Signature on delivery + Delivery to stated address only',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3389',
            'label'        => 'Signature on delivery + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3096',
            'label'        => 'Signature on delivery + Deliver to stated address only + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3090',
            'label'        => 'Delivery to neighbour + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3385',
            'label'        => 'Deliver to stated address only',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3390',
            'label'        => 'Deliver to stated address only + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3086',
            'label'        => 'COD',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
        ),
        array(
            'value'        => '3091',
            'label'        => 'COD + Extra cover',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
        ),
        array(
            'value'        => '3093',
            'label'        => 'COD + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
        ),
        array(
            'value'        => '3097',
            'label'        => 'COD + Extra cover + Return when not home',
            'isExtraCover' => false,
            'isAvond'      => true,
            'isCod'        => true,
        ),
    );

    /**
     * Gets an array of possible standard delivery product options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions(array('isCod' => false));
    }

    /**
     * Gets an array of possible evening delivery product options.
     *
     * @param boolean $asFlatArray
     *
     * @return array
     */
    public function getAvondOptions($asFlatArray = false)
    {
        return $this->getOptions(array('isAvond' => true, 'isCod' => false), $asFlatArray);
    }

    /**
     * Gets an array of possible evening delivery product options.
     *
     * @param boolean $asFlatArray
     *
     * @return array
     */
    public function getAvondCodOptions($asFlatArray = false)
    {
        return $this->getOptions(array('isAvond' => true, 'isCod' => true), $asFlatArray);
    }

    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
     *
     * @param boolean|int $storeId
     * @param boolean     $codesOnly
     * @param boolean     $isAvond
     * @param boolean     $cod
     *
     * @return array
     */
    public function getAvailableOptions($storeId = false, $codesOnly = false, $isAvond = false, $cod = false)
    {
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $flags = array();
        if ($isAvond) {
            $flags['isAvond'] = true;
        }
        if ($cod) {
            $flags['isCod'] = true;
        }

        $options = $this->getOptions($flags);

        /**
         * Get a list of all possible options
         */
        $availableOptions = array();

        /**
         * Get the list of supported product options from the shop's configuration
         */
        $supportedOptions = Mage::getStoreConfig(self::XML_PATH_SUPPORTED_PRODUCT_OPTIONS, $storeId);
        $supportedOptionsArray = explode(',', $supportedOptions);

        /**
         * Check each standard option to see if it's supprted
         */
        foreach ($options as $option) {
            if (!is_array($option) || !array_key_exists('value', $option)) {
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

    /**
     * Alias for getAvailableOptions() with $isAvond === true.
     *
     * @param bool $storeId
     * @param bool $codesOnly
     *
     * @return array
     */
    public function getAvailableAvondOptions($storeId = false, $codesOnly = false)
    {
        return $this->getAvailableOptions($storeId, $codesOnly, true);
    }

    /**
     * Alias for getAvailableOptions() with $isAvond = true and $cod = true.
     *
     * @param bool $storeId
     * @param bool $codesOnly
     *
     * @return array
     */
    public function getAvailableAvondCodOptions($storeId = false, $codesOnly = false)
    {
        return $this->getAvailableOptions($storeId, $codesOnly, true, true);
    }
}
