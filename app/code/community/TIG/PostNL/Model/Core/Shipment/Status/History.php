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
class TIG_PostNL_Model_Core_Shipment_Status_History extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('postnl_core/shipment_status_history');
    }
    
    /**
     * Load a history item based on a postnl shipment id and a status code.
     * 
     * @param int $shipmentId
     * @param string $code
     * 
     * @return TIG_PostNL_Model_Core_Shipment_Status_History
     */
    public function loadShipmentByIdAndCode($shipmentId, $code)
    {
        $collection = $this->getCollection();
        $collection->addFieldToSelect('status_id')
                   ->addFieldToFilter('parent_id', array('eq' => $shipmentId))
                   ->addFieldToFilter('code', array('eq' => $code));
        
        $collection->getSelect()->limit(1); //we only want 1 item
        
        $id = $collection->getFirstItem()->getId();
        
        if ($id) {
            $this->load($id);
        }
        
        return $this;
    }
    
    /**
     * Check if a status history item exists for the given postnl shipment and status code
     * 
     * @param int $shipmentId
     * @param string $code
     * 
     * @return boolean
     */
    public function statusHistoryExists($shipmentId, $code)
    {
        $collection = $this->getCollection();
        $collection->addFieldToSelect('status_id')
                   ->addFieldToFilter('parent_id', array('eq' => $shipmentId))
                   ->addFieldToFilter('code', array('eq' => $code));
                   
        if ($collection->count() > 0) {
            return true;
        }
        
        return false;
    }
}
