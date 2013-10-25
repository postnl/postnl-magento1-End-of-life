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
class TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions
{
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
            /**
             * These are not currently implemented
             * 
             * TODO implement these options
             */
            /*array(
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
            ),*/
            array(
                'value'        => '3534',
                'label'        => $helper->__('PakjeGemak + Extra Cover'),
                'isExtraCover' => true,
            ),
            array(
                'value'        => '3544',
                'label'        => $helper->__('PakjeGemak + Extra Cover + Notification'),
                'isExtraCover' => true,
            ),
            array(
                'value' => '3533',
                'label' => $helper->__('PakjeGemak + Signature on Delivery')
            ),
            array(
                'value' => '3543',
                'label' => $helper->__('PakjeGemak + Signature on Delivery + Notification')
            ),
        );
        
        return $availableOptions;
    }
}
