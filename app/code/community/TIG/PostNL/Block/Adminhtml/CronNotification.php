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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Block_Adminhtml_CronNotification extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_cronnotification';

    /**
     * Cron expression for cronjobs working in 'always' cron mode
     */
    const ALWAYS_CRON_EXPR = 'always';

    /**
     * Check to see if the cron is running. This is done by checking if the last executed cron task
     * was executed less than 1 hour ago.
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

        /**
         * Create an array of cronjobs that need to be ignored
         */
        $cronjobs = Mage::getConfig()->getNode('crontab/jobs')->asArray();
        $ignoreCronjobs = array();
        foreach ($cronjobs as $cronjob => $cronData) {
            if (!isset($cronData['schedule']) || !isset($cronData['schedule']['cron_expr'])) {
                continue;
            }

            /**
             * Cron jobs with the cron_expr 'always' work on a different cron mode
             */
            if($cronData['schedule']['cron_expr'] == self::ALWAYS_CRON_EXPR) {
                $ignoreCronjobs[] = $cronjob;
            }
        }

        /**
         * Select the last executed cronjob
         */
        $select = $readConnection->select();
        $select->from($coreResource->getTablename('cron/schedule'), array('MAX(executed_at)'));

        /**
         * Filter out the invalid cronjobs
         */
        if (!empty($ignoreCronjobs)) {
            $ignoreCronjobs = implode(',', $ignoreCronjobs);
            $select->where('job_code NOT IN (?)', $ignoreCronjobs);
        }

        /**
         * Get the last execution time of a PostNL cronjob
         */
        $lastExecutionTime = $readConnection->fetchOne($select);

        /**
         * If no execution time was found it means the cron has never run before
         */
        if (!$lastExecutionTime) {
            return false;
        }

        /**
         * Check if the last execution time was more than an hour ago.
         * If no crontask has been executed in an hour it's likely that something is wrong.
         */
        $currentTime = new DateTime();
        $currentTime->setTimestamp(Mage::getModel('core/date')->gmtTimestamp());

        $oneHourAgo        = $currentTime->sub(new DateInterval('PT1H'));
        $lastExecutionTime = new DateTime($lastExecutionTime);

        if ($lastExecutionTime < $oneHourAgo) {
            return false;
        }

        return true;
    }
}
