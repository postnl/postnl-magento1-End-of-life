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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_System_Config_Source_CutOffTime
{
    /**
     * Gets an option array for possible cut-off times.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('postnl')->__('No cut-off time'),
            ),
            array(
                'value' => '01:00:00',
                'label' => '01:00',
            ),
            array(
                'value' => '02:00:00',
                'label' => '02:00',
            ),
            array(
                'value' => '03:00:00',
                'label' => '03:00',
            ),
            array(
                'value' => '04:00:00',
                'label' => '04:00',
            ),
            array(
                'value' => '05:00:00',
                'label' => '05:00',
            ),
            array(
                'value' => '06:00:00',
                'label' => '06:00',
            ),
            array(
                'value' => '07:00:00',
                'label' => '07:00',
            ),
            array(
                'value' => '08:00:00',
                'label' => '08:00',
            ),
            array(
                'value' => '09:00:00',
                'label' => '09:00',
            ),
            array(
                'value' => '10:00:00',
                'label' => '10:00',
            ),
            array(
                'value' => '11:00:00',
                'label' => '11:00',
            ),
            array(
                'value' => '12:00:00',
                'label' => '12:00',
            ),
            array(
                'value' => '13:00:00',
                'label' => '13:00',
            ),
            array(
                'value' => '14:00:00',
                'label' => '14:00',
            ),
            array(
                'value' => '15:00:00',
                'label' => '15:00',
            ),
            array(
                'value' => '16:00:00',
                'label' => '16:00',
            ),
            array(
                'value' => '17:00:00',
                'label' => '17:00',
            ),
            array(
                'value' => '18:00:00',
                'label' => '18:00',
            ),
            array(
                'value' => '19:00:00',
                'label' => '19:00',
            ),
            array(
                'value' => '20:00:00',
                'label' => '20:00',
            ),
            array(
                'value' => '21:00:00',
                'label' => '21:00',
            ),
            array(
                'value' => '22:00:00',
                'label' => '22:00',
            ),
            array(
                'value' => '23:00:00',
                'label' => '23:00',
            ),
        );

        return $options;
    }
}
