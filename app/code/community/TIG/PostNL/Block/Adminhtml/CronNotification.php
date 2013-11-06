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
class TIG_PostNL_Block_Adminhtml_CronNotification extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Check to see if the cron is running. This is done by checking if the last executed cron task
     * was executed less than 4 hours ago.
     * 
     * @return boolean
     */
    public function isCronActive()
    {
        /**
         * Get the last execution time from the cron_schedule table
         */
        $coreResource = Mage::getSingleton('core/resource');
        $readConnection = $coreResource->getConnection('core_read');
        
        $select = $readConnection->select();
        $select->from($coreResource->getTablename('cron/schedule'), array('MAX(executed_at)'));
        
        $lastExecutionTime = $readConnection->fetchOne($select);
        
        /**
         * If no execution time was found it means the cron has never run before
         */
        if (!$lastExecutionTime) {
            return false;
        }
        
        /**
         * Check if the last execution time was more than 4 hours ago.
         * If no crontask has been executed in 4 hours it's likely that something is wrong.
         */
        $currentTimestamp       = Mage::getModel('core/date')->timestamp();
        $oneHourAgoTimestamp    = strtotime('-4 hours', $currentTimestamp);
        $lastExecutionTimestamp = strtotime($lastExecutionTime);
        
        if ($lastExecutionTimestamp < $oneHourAgoTimestamp) {
            return false;
        }
        
        return true;
    }
}
