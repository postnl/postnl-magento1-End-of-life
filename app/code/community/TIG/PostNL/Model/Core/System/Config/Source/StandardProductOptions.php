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
class TIG_PostNL_Model_Core_System_Config_Source_StandardProductOptions
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
     * @todo implement COD
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('postnl');
        $availableOptions = array(
            array(
                'value' => '3085',
                'label' => $helper->__('Standard shipment'),
            ),
            /**
             * These are not currently implemented
             * 
             * TODO implement these options
             */
            /*array(
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
            ),*/
            array(
                'value'        => '3087',
                'label'        => $helper->__('Extra Cover'),
                'isExtraCover' => true,
            ),
            array(
                'value'        => '3094',
                'label'        => $helper->__('Extra cover + Return when not home'),
                'isExtraCover' => true,
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
                'value' => '3390',
                'label' => $helper->__('Deliver to stated address only + Return when not home'),
            ),
        );
        
        return $availableOptions;
    }
    
    /**
     * Get a list of available options. This is a filtered/modified version of the array supplied by toOptionArray();
     * 
     * @param boolean|int $storeId
     * @param boolean $codesOnly
     * 
     * @return array
     */
    public function getAvailableOptions($storeId = false, $codesOnly = false)
    {
        if ($storeId === false) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
        
        $helper = Mage::helper('postnl');
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
        
        /**
         * Check each standard option to see if it's supprted
         */
        $availableStandardOptions = array();
        foreach ($options as $option) {
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
