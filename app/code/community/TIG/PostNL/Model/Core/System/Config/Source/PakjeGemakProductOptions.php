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
            /**
             * These are not currently implemented
             * 
             * TODO implement these options
             */
            /*array(
                'value' => '3535',
                'label' => $helper->__('Post Office + COD')
            ),
            array(
                'value' => '3545',
                'label' => $helper->__('Post Office + COD + Notification')
            ),
            array(
                'value' => '3536',
                'label' => $helper->__('Post Office + COD + Extra Cover')
            ),
            array(
                'value' => '3546',
                'label' => $helper->__('Post Office + COD + Extra Cover + Notification')
            ),*/
            array(
                'value'        => '3534',
                'label'        => $helper->__('Post Office + Extra Cover'),
                'isExtraCover' => true,
            ),
            array(
                'value'        => '3544',
                'label'        => $helper->__('Post Office + Extra Cover + Notification'),
                'isExtraCover' => true,
            ),
            array(
                'value' => '3533',
                'label' => $helper->__('Post Office + Signature on Delivery')
            ),
            array(
                'value' => '3543',
                'label' => $helper->__('Post Office + Signature on Delivery + Notification')
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
