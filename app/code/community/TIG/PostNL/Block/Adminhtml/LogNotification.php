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
 *
 * @method boolean                                    hasMaxLogSize()
 * @method TIG_PostNL_Block_Adminhtml_LogNotification setMaxLogSize(int $value)
 */
class TIG_PostNL_Block_Adminhtml_LogNotification extends TIG_PostNL_Block_Adminhtml_Template
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'postnl_adminhtml_lognotification';

    /**
     * Gets the maximum allowed log size for all PostNL logs.
     *
     * @return mixed
     */
    public function getMaxLogSize()
    {
        if ($this->hasMaxLogSize()) {
            return $this->_getData('max_log_size');
        }

        /**
         * @var $logModelClass TIG_PostNL_Model_Adminhtml_Support_Logs
         */
        $logModelClass = Mage::getConfig()->getModelClassName('postnl_adminhtml/support_logs');
        $maxSize = $logModelClass::LOG_MAX_TOTAL_SIZE;

        $this->setMaxLogSize($maxSize);
        return $maxSize;
    }

    /**
     * Get the total size of all PostNL log files.
     *
     * @return int
     */
    public function getLogSize()
    {
        /**
         * Get the folder where all PostNL logs are stored and make sure it exists.
         */
        $logFolder = Mage::getBaseDir('var') . DS . 'log' . DS . 'TIG_PostNL';
        if (!is_dir($logFolder)) {
            return 0;
        }

        /**
         * Get all log files in the PostNL log folder.
         */
        $logs = glob($logFolder . DS . '*.log');

        /**
         * Calculate the sum of the file sizes of all logs in the PostNL log folder.
         */
        $totalSize     = 0;
        foreach ($logs as $log) {
            /**
             * Make sure the log is a file and is readable.
             */
            if (!is_file($log) || !is_readable($log)) {
                continue;
            }

            $fileSize = filesize($log);

            /**
             * Add the log's file size to the total size of all valid logs and add the log to the array.
             */
            $totalSize += $fileSize;
        }

        return $totalSize;
    }

    /**
     * Get whether the size of all PostNL logs exceeds the maximum allowed.
     *
     * @return bool
     */
    public function logsExceedMaxSize()
    {
        $maxSize = $this->getMaxLogSize();
        $logSize = $this->getLogSize();

        if ($logSize > $maxSize) {
            return true;
        }

        return false;
    }
}
