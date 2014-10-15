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
class TIG_PostNL_Model_Adminhtml_System_Config_Source_OrderGridMassaction
{
    /**
     * Returns an option array for available order grid mass actions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $options = array(
            array(
                'value' => '',
                'label' => $helper->__('None'),
            ),
            array(
                'value' => 'postnl_create_shipments',
                'label' => $helper->__('Create shipments'),
            ),
            array(
                'value' => 'postnl_create_shipment_print_label_and_confirm',
                'label' => $helper->__('Create shipments, print labels and confirm'),
            ),
            array(
                'value' => 'postnl_create_shipment_print_packing_slip_and_confirm',
                'label' => $helper->__('Create shipments, print packing slips and confirm'),
            ),
            array(
                'value' => 'postnl_print_packing_slips',
                'label' => $helper->__('Print packing slips'),
            ),
        );

        return $options;
    }
}
