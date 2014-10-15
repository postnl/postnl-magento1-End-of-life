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
class TIG_PostNL_Model_Core_System_Config_Source_ShippingDuration
{
    /**
     * Gets an option array for possible shipping durations.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');

        $options = array(
            array(
                'value' => 1,
                'label' => '1 ' . $helper->__('day'),
            ),
            array(
                'value' => 2,
                'label' => '2 ' . $helper->__('days'),
            ),
            array(
                'value' => 3,
                'label' => '3 ' . $helper->__('days'),
            ),
            array(
                'value' => 4,
                'label' => '4 ' . $helper->__('days'),
            ),
            array(
                'value' => 5,
                'label' => '5 ' . $helper->__('days'),
            ),
            array(
                'value' => 6,
                'label' => '6 ' . $helper->__('days'),
            ),
            array(
                'value' => 7,
                'label' => '7 ' . $helper->__('days'),
            ),
            array(
                'value' => 8,
                'label' => '8 ' . $helper->__('days'),
            ),
            array(
                'value' => 9,
                'label' => '9 ' . $helper->__('days'),
            ),
            array(
                'value' => 10,
                'label' => '10 ' . $helper->__('days'),
            ),
            array(
                'value' => 11,
                'label' => '11 ' . $helper->__('days'),
            ),
            array(
                'value' => 12,
                'label' => '12 ' . $helper->__('days'),
            ),
            array(
                'value' => 13,
                'label' => '13 ' . $helper->__('days'),
            ),
            array(
                'value' => 14,
                'label' => '14 ' . $helper->__('days'),
            ),
        );

        return $options;
    }
}
