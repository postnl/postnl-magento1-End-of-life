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
class TIG_PostNL_Model_Core_System_Config_Source_WeightUnit
{
    /**
     * Returns an option array for all possible PostNL product options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $availableOptions = array(
            'metric' => array(
                'label' => $helper->__('Metric'),
                'value' => array(
                    array(
                        'value' => 'tonne',
                        'label' => $helper->__('Tonne (t)'),
                    ),
                    array(
                        'value' => 'kilogram',
                        'label' => $helper->__('Kilogram (kg)'),
                    ),
                    array(
                        'value' => 'hectogram',
                        'label' => $helper->__('Hectogram (hg)'),
                    ),
                    array(
                        'value' => 'gram',
                        'label' => $helper->__('Gram (g)'),
                    ),
                    array(
                        'value' => 'carat',
                        'label' => $helper->__('Carat'),
                    ),
                    array(
                        'value' => 'centigram',
                        'label' => $helper->__('Centigram'),
                    ),
                    array(
                        'value' => 'milligram',
                        'label' => $helper->__('Milligram (mg)'),
                    ),
                ),
            ),
            'avoirdupois' => array(
                'label' => $helper->__('Avoirdupois (US)'),
                'value' => array(
                    array(
                        'value' => 'longton',
                        'label' => $helper->__('Long ton')
                    ),
                    array(
                        'value' => 'shortton',
                        'label' => $helper->__('Short ton')
                    ),
                    array(
                        'value' => 'longhundredweight',
                        'label' => $helper->__('Long hundredweight')
                    ),
                    array(
                        'value' => 'shorthundredweight',
                        'label' => $helper->__('Short hundredweight')
                    ),
                    array(
                        'value' => 'stone',
                        'label' => $helper->__('Stone')
                    ),
                    array(
                        'value' => 'pound',
                        'label' => $helper->__('Pound (lb)')
                    ),
                    array(
                        'value' => 'ounce',
                        'label' => $helper->__('Ounce')
                    ),
                    array(
                        'value' => 'grain',
                        'label' => $helper->__('Grain (g)')
                    ),
                ),
            ),
            'troy' => array(
                'label' => $helper->__('Troy'),
                'value' => array(
                    array(
                        'value' => 'troy_pound',
                        'label' => $helper->__('Pound')
                    ),
                    array(
                        'value' => 'troy_ounce',
                        'label' => $helper->__('Ounce')
                    ),
                    array(
                        'value' => 'troy_pennyweight',
                        'label' => $helper->__('Pennyweight')
                    ),
                    array(
                        'value' => 'troy_carat',
                        'label' => $helper->__('carat')
                    ),
                    array(
                        'value' => 'troy_grain',
                        'label' => $helper->__('Grain')
                    ),
                    array(
                        'value' => 'troy_mite',
                        'label' => $helper->__('Mite')
                    ),
                ),
            ),
        );

        return $availableOptions;
    }
}
