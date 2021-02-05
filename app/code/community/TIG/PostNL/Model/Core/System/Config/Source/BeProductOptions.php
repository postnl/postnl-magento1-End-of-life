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
class TIG_PostNL_Model_Core_System_Config_Source_BeProductOptions
    extends TIG_PostNL_Model_Core_System_Config_Source_ProductOptions_Abstract
{
    /**
     * @var array
     */
    protected $_options = array(
        array(
            'value'         => '4946',
            'label'         => 'Belgium standard',
            'isEvening'     => false,
            'isBelgiumOnly' => true,
            'isExtraCover'  => false,
            'group'         => 'be_options',
        ),
        array(
            'value'         => '4941',
            'label'         => 'Belgium standard + Deliver to stated address only',
            'isEvening'     => true,
            'isBelgiumOnly' => true,
            'isExtraCover'  => false,
            'group'         => 'be_options',
        ),
        array(
            'value'         => '4912',
            'label'         => 'Belgium standard + Signature on delivery',
            'isEvening'     => false,
            'isBelgiumOnly' => true,
            'isExtraCover'  => false,
            'group'         => 'be_options',
        ),
        array(
            'value'         => '4914',
            'label'         => 'Belgium Standard + Signature on delivery + Extra Cover',
            'isEvening'     => false,
            'isBelgiumOnly' => true,
            'isExtraCover'  => true,
            'group'         => 'be_options',
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
     * Get a list of available options. This is a filtered/modified version of the array supplied by getOptions();
     *
     * @param boolean $flat
     *
     * @return array
     */
    public function getAvailableOptions($flat = false)
    {
        return $this->getOptions(array('isBe' => true), $flat, true);
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
