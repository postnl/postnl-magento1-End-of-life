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
     * Set the 'phase' attribute. The phase must be formatted as a 2 digit number (i.e. 01, 04, 12, 99 etc.)
     * 
     * @param string | int $phase
     * 
     * @return TIG_PostNL_Model_Core_Shipment_Status_History
     */
    public function setPhase($phase)
    {
        if (strlen($phase) < 2) {
            $phase = '0' . $phase;
        }
        
        $this->setData('phase', $phase);
        return $this;
    }
    
    /**
     * Load a history item based on a postnl shipment id and a status code.
     * 
     * @param int $shipmentId
     * @param string $code
     * 
     * @return TIG_PostNL_Model_Core_Shipment_Status_History
     */
    public function loadByShipmentIdAndCode($shipmentId, $code)
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
     * Check if a status history item exists for the given postnl shipment and status code and if the provided status is newer
     * 
     * @param int $shipmentId
     * @param StdClass $code
     * 
     * @return boolean
     */
    public function statusHistoryIsNew($shipmentId, $status)
    {
        $collection = $this->getCollection();
        $collection->addFieldToSelect('status_id')
                   ->addFieldToFilter('parent_id', array('eq' => $shipmentId))
                   ->addFieldToFilter('code', array('eq' => $status->Code));
                   
        if ($collection->count() > 0) {
            return true;
        }
        
        /**
         * If a given code already exists for this shipment, get both the existing status's and the new status's timestamps so we
         * can check which one is newer.
         */
        $existingStatus = $collection->getFirstItem();
        $existingStatusTime = $existingStatus->getTimestamp();
        
        $statusTime = $status->TimeStamp;
        
        /**
         * Compare both timestamps
         */
        if (strtotime($statusTime) > strtotime($existingStatusTime)) {
            return true;
        }
        
        return false;
    }
}
