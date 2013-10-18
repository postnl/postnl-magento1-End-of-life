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
     * Returns an option array for all possible PostNL product options
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $availableOptions = array(
            array(
                'value' => 'default',
                'label' => $helper->__('Use default'),
            ),
            'standard_options' => array(
                'label' => $helper->__('Standard options'),
                'value' => array(
                    array(
                        'value' => '3085',
                        'label' => $helper->__('Standard shipment'),
                    ),
                    array(
                        'value' => '3086',
                        'label' => $helper->__('COD'),
                    ),
                    array(
                        'value' => '3091',
                        'label' => $helper->__('COD + Extra cover'),
                    ),
                    array(
                        'value' => '3093',
                        'label' => $helper->__('COD + Return when not home'),
                    ),
                    array(
                        'value' => '3097',
                        'label' => $helper->__('COD + Extra cover + Return when not home'),
                    ),
                    array(
                        'value' => '3087',
                        'label' => $helper->__('Extra Cover'),
                    ),
                    array(
                        'value' => '3094',
                        'label' => $helper->__('Extra cover + Return when not home'),
                    ),
                    array(
                        'value' => '3189',
                        'label' => $helper->__('Signature on delivery'),
                    ),
                    array(
                        'value' => '3089',
                        'label' => $helper->__('Signature on delivery + Delivery to stated address only'),
                    ),
                    array(
                        'value' => '3389',
                        'label' => $helper->__('Signature on delivery + Return when not home'),
                    ),
                    array(
                        'value' => '3096',
                        'label' => $helper->__('Signature on delivery + Deliver to stated address only + Return when not home'),
                    ),
                    array(
                        'value' => '3090',
                        'label' => $helper->__('Delivery to neighbour + Return when not home'),
                    ),
                    array(
                        'value' => '3385',
                        'label' => $helper->__('Deliver to stated address only'),
                    ),
                    array(
                        'value' => '3094',
                        'label' => $helper->__('Deliver to stated address only + Return when not home'),
                    ),
                ),
            ),
            'pakjegemak_options' => array(
                'label' => $helper->__('PakjeGemak options'),
                'value' => array(
                    array(
                        'value' => '3535',
                        'label' => $helper->__('PakjeGemak + COD')
                    ),
                    array(
                        'value' => '3545',
                        'label' => $helper->__('PakjeGemak + COD + Notification')
                    ),
                    array(
                        'value' => '3536',
                        'label' => $helper->__('PakjeGemak + COD + Extra Cover')
                    ),
                    array(
                        'value' => '3546',
                        'label' => $helper->__('PakjeGemak + COD + Extra Cover + Notification')
                    ),
                    array(
                        'value' => '3534',
                        'label' => $helper->__('PakjeGemak + Extra Cover')
                    ),
                    array(
                        'value' => '3544',
                        'label' => $helper->__('PakjeGemak + Extra Cover + Notification')
                    ),
                    array(
                        'value' => '3533',
                        'label' => $helper->__('PakjeGemak + Signature on Delivery')
                    ),
                    array(
                        'value' => '3543',
                        'label' => $helper->__('PakjeGemak + Signature on Delivery + Notification')
                    ),
                ),
            ),
            'eu_options' => array(
                'label' => $helper->__('EU options'),
                'value' => array(
                    array(
                        'value' => '4940',
                        'label' => $helper->__('EU Pack Special'),
                    ),
                    array(
                        'value' => '4924',
                        'label' => $helper->__('EU Pack Special COD (Belgium and Luxembourg only)'),
                    ),
                    array(
                        'value' => '4946',
                        'label' => $helper->__('EU Pack Standard (Belgium only)'),
                    ),
                    array(
                        'value' => '4944',
                        'label' => $helper->__('EU Pack Special to consumer'),
                    )
                ),
            ),
            'global_options' => array(
                'label' => $helper->__('Global options'),
                'value' => array(
                    array(
                        'value' => '4945',
                        'label' => $helper->__('GlobalPack'),
                    ),
                ),
            ),
        );
        
        return $availableOptions;
    }
}
