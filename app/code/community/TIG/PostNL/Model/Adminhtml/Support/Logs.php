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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Adminhtml_Support_Logs
{
    /**
     * Max size for individual log files and the total size of all logs (in bytes).
     */
    const LOG_MAX_SIZE       = '104857600'; //100MB
    const LOG_MAX_TOTAL_SIZE = '1073741824'; //1GB

    /**
     * Get all PostNL log files, merge these into a zip file and return the path to said zip file.
     *
     * @return string
     *
     * @throws TIG_PostNL_Exception
     */
    public function downloadLogs()
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the folder where all PostNL logs are stored and make sure it exists.
         */
        $logFolder = Mage::getBaseDir('var') . DS . 'log' . DS . 'TIG_PostNL';
        if (!is_dir($logFolder)) {
            throw new TIG_PostNL_Exception(
                $helper->__('No valid log files were found.'),
                'POSTNL-0172'
            );
        }

        /**
         * Get all log files in the log folder and a list of all logs that are allowed for this download.
         */
        $logs          = glob($logFolder . DS . '*.log');
        $allowedLogs   = Mage::helper('postnl')->getLogFiles();

        /**
         * Make sure each log is valid and put the valid logs in an array with the log's filename as the key. We need
         * this later on to prevent the entire directory structure from being included in the zip file.
         */
        $logsWithNames = array();
        $totalSize     = 0;
        foreach ($logs as $log) {
            $logName = explode(DS, $log);
            $logName = end($logName);

            /**
             * Make sure this log is allowed.
             */
            if (!in_array($logName, $allowedLogs)) {
                continue;
            }

            /**
             * Make sure the log is a file and is readable.
             */
            if (!is_file($log) || !is_readable($log)) {
                continue;
            }

            /**
             * Make sure the log is not too large. Otherwise we won't be able to read it anyway.
             */
            $fileSize = filesize($log);
            if ($fileSize > self::LOG_MAX_SIZE) {
                $helper->addSessionMessage(
                    'adminhtml/session',
                    'POSTNL-0173',
                    'warning',
                    $helper->__(
                        'Log %s is too large (%.2fMB) and was skipped.',
                        $logName,
                        $fileSize / 1024 / 1024
                    )
                );

                continue;
            }

            /**
             * Add the log's filesize to the total size of all valid logs and add the log to the array.
             */
            $totalSize += $fileSize;
            $logsWithNames[$logName] = $log;
        }

        /**
         * If we have no valid logs, there is nothing to do.
         */
        if (empty($logsWithNames)) {
            throw new TIG_PostNL_Exception(
                $helper->__('No valid log files were found.'),
                'POSTNL-0172'
            );
        }

        /**
         * Make sure the total size of all logs is not too large.
         */
        if ($totalSize > self::LOG_MAX_TOTAL_SIZE) {
            throw new TIG_PostNL_Exception(
                $helper->__('The total size of all log files exceeds the maximum size allowed.'),
                'POSTNL-0174'
            );
        }

        /**
         * Creating the zip file for large logs may take a while, so disable the PHP time limit.
         */
        set_time_limit(0);

        /**
         * Get the path to the final zip file.
         */
        $zipPath = $logFolder
            . 'TIG_PostNL-logs-'
            . date('Ymd-His', Mage::getSingleton('core/date')->timestamp())
            . '.zip';

        /**
         * Open the zip file. Overwriting the previous file if it exists.
         */
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);

        /**
         * Add all the log files.
         */
        foreach ($logsWithNames as $name => $log) {
            $zip->addFile($log, $name);
        }

        /**
         * Close the zip file.
         */
        $zip->close();

        return $zipPath;
    }
}