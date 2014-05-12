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
class TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'        => '3534',
            'label'        => 'Post Office + Extra Cover',
            'isExtraCover' => true,
            'isPge'        => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3544',
            'label'        => 'Post Office + Extra Cover + Notification',
            'isExtraCover' => true,
            'isPge'        => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3533',
            'label'        => 'Post Office + Signature on Delivery',
            'isExtraCover' => false,
            'isPge'        => false,
            'isCod'        => false,
        ),
        array(
            'value'        => '3543',
            'label'        => 'Post Office + Signature on Delivery + Notification',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => false,
        ),
        array(
            'value'        => '3535',
            'label'        => 'Post Office + COD',
            'isExtraCover' => false,
            'isPge'        => false,
            'isCod'        => true,
        ),
        array(
            'value'        => '3545',
            'label'        => 'Post Office + COD + Notification',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => true,
        ),
        array(
            'value'        => '3536',
            'label'        => 'Post Office + COD + Extra Cover',
            'isExtraCover' => false,
            'isPge'        => false,
            'isCod'        => true,
        ),
        array(
            'value'        => '3546',
            'label'        => 'Post Office + COD + Extra Cover + Notification',
            'isExtraCover' => false,
            'isPge'        => true,
            'isCod'        => true,
        ),
    );

    /**
     * Returns an option array for all possible PostNL product options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions(array('isCod' => false));
    }

    /**
     * Gets an array of possible PGE product options.
     *
     * @param boolean $asFlatArray
     *
     * @return array
     */
    public function getPgeOptions($asFlatArray = false)
    {
        return $this->getOptions(array('isCod' => false, 'isPge' => true), $asFlatArray);
    }

    /**
     * Gets an array of possible PGE COD product options.
     *
     * @param boolean $asFlatArray
     *
     * @return array
     */
    public function getPgeCodOptions($asFlatArray = false)
    {
        return $this->getOptions(array('isCod' => true, 'isPge' => true), $asFlatArray);
    }

    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
     *
     * @param boolean|int $storeId
     * @param boolean     $codesOnly
     * @param boolean     $isPge
     * @param boolean     $cod
     *
     * @return array
     */
    public function getAvailableOptions($storeId = false, $codesOnly = false, $isPge = false, $cod = false)
    {
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $flags = array();
        if ($isPge) {
            $flags['isPge'] = true;
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
         * Check each standard option to see if it's supported
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
     * Alias for getAvailableOptions() with $cod = true.
     *
     * @param bool $storeId
     * @param bool $codesOnly
     *
     * @return array
     */
    public function getAvailableCodOptions($storeId = false, $codesOnly = false)
    {
        return $this->getAvailableOptions($storeId, $codesOnly, false, true);
    }

    /**
     * Alias for getAvailableOptions() with $isPge = true.
     *
     * @param bool $storeId
     * @param bool $codesOnly
     *
     * @return array
     */
    public function getAvailablePgeOptions($storeId = false, $codesOnly = false)
    {
        return $this->getAvailableOptions($storeId, $codesOnly, true);
    }

    /**
     * Alias for getAvailableOptions() with $isPge = true and $cod = true.
     *
     * @param bool $storeId
     * @param bool $codesOnly
     *
     * @return array
     */
    public function getAvailablePgeCodOptions($storeId = false, $codesOnly = false)
    {
        return $this->getAvailableOptions($storeId, $codesOnly, true, true);
    }
}
