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
class TIG_PostNL_Block_Core_ShippingStatus extends Mage_Core_Block_Template
{
    const CLASS_UNCONFIRMED  = '';
    const CLASS_CONFIRMED    = 'status-confirmed';
    const CLASS_DISTRIBUTION = 'status-distribution';
    const CLASS_TRANSIT      = 'status-transit';
    const CLASS_DELIVERED    = 'status-delivered';
    
    public function getShippingStatus($shipment)
    {
        $postnlShipment = Mage::getModel('postnl_core/shipment')->load($shipment->getId(), 'shipment_id');
        if (!$postnlShipment->getId()) {
            return self::CLASS_UNCONFIRMED;
        }
        
        switch ($postnlShipment->getShippingPhase()) {
            case '1': 
                $class = self::CLASS_CONFIRMED;
                break;
            case '2': 
                $class = self::CLASS_DISTRIBUTION;
                break;
            case '3': 
                $class = self::CLASS_TRANSIT;
                break;
            case '4': 
                $class = self::CLASS_DELIVERED;
                break;
            default:
                $class = self::CLASS_UNCONFIRMED;
                break;
        }
        
        return $class;
    }
}
