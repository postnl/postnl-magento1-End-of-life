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
 */

/**
 * Helper class for CIF operations
 */
class TIG_PostNL_Helper_Webservices extends TIG_PostNL_Helper_Data
{
    /**
     * XML paths for security keys
     */
    const XPATH_EXTENSIONCONTROL_UNIQUE_KEY  = 'postnl/general/unique_key';
    const XPATH_EXTENSIONCONTROL_PRIVATE_KEY = 'postnl/general/private_key';

    /**
     * XML path to updateStatistics on/off switch
     */
    const XPATH_SEND_STATISTICS = 'postnl/advanced/send_statistics';

    /**
     * XML path to receiveUpdates on/off switch
     */
    const XPATH_RECEIVE_UPDATES = 'postnl/advanced/receive_updates';

    /**
     * Log filename to log all webservices exceptions
     */
    const WEBSERVICES_EXCEPTION_LOG_FILE = 'TIG_PostNL_Webservices_Exception.log';

    /**
     * Log filename to log webservices calls
     */
    const WEBSERVICES_DEBUG_LOG_FILE = 'TIG_PostNL_Webservices_Debug.log';

    /**
     * Check if the extension has been activated with the extension control system by checking if the unique ket and private key
     * have been entered.
     *
     * @param Mage_Core_Model_Website | null $website
     *
     * @return boolean
     */
    public function canSendStatistics($website = null)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        /**
         * First check if sending statistics is enabled
         */
        if ($website !== null) {
            /**
             * If a website was specified, check if the module may send statistics for that website
             */
            $sendStatistics = $website->getConfig(self::XPATH_SEND_STATISTICS);
        } else {
            /**
             * otherwise, check if ending statistics was enabled in default settings
             */
            $sendStatistics = Mage::getStoreConfigFlag(self::XPATH_SEND_STATISTICS, $storeId);
        }

        if (!$sendStatistics) {
            return false;
        }

        /**
         * Check if the security keys have been entered.
         */
        $privateKey = Mage::getStoreConfig(self::XPATH_EXTENSIONCONTROL_PRIVATE_KEY, $storeId);
        $uniqueKey  = Mage::getStoreConfig(self::XPATH_EXTENSIONCONTROL_UNIQUE_KEY, $storeId);

        if (empty($privateKey) || empty($uniqueKey)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the module may automatically receive updates regarding the module or promotions
     *
     * @return boolean
     */
    public function canReceiveUpdates()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $receiveUpdates =  Mage::getStoreConfigFlag(self::XPATH_RECEIVE_UPDATES, $storeId);
        if (!$receiveUpdates) {
            return false;
        }

        return true;
    }

    /**
     * Encrypts a value
     *
     * @param string $value
     *
     * @return string
     */
    public function encryptValue($value)
    {
        $value = (string) $value;

        $encrypted = Mage::helper('core')->encrypt($value);

        return $encrypted;
    }

    /**
     * Logs a webservice request and response for debug purposes.
     *
     * @param Zend_Soap_Client $client
     *
     * @return $this
     *
     * @see Mage::log()
     */
    public function logWebserviceCall($client)
    {
        if (!$this->isLoggingEnabled()) {
            return $this;
        }

        $this->createLogDir();

        $requestXml = $this->formatXml($client->getLastRequest());
        $responseXML = $this->formatXml($client->getLastResponse());

        $logMessage = "Request sent:\n"
                    . $requestXml
                    . "\nResponse received:\n"
                    . $responseXML;

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::WEBSERVICES_DEBUG_LOG_FILE;
        $this->log($logMessage, Zend_Log::DEBUG, $file);

        return $this;
    }

    /**
     * Logs a webservice exception in the database and/or a log file
     *
     * @param Mage_Core_Exception|TIG_PostNL_Exception|SoapFault $exception
     *
     * @return $this
     *
     * @see Mage::logException()
     */
    public function logWebserviceException($exception)
    {
        if (!$this->isExceptionLoggingEnabled()) {
            return $this;
        }

        $logMessage = "\n" . $exception->__toString();

        $file = self::POSTNL_LOG_DIRECTORY . DS . self::WEBSERVICES_EXCEPTION_LOG_FILE;
        $this->log($logMessage, Zend_Log::ERR, $file, false, true);

        return $this;
    }
}
