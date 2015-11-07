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
class TIG_PostNL_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    const TYPE_DATA_UNINSTALL = 'data-uninstall';

    /**
     * Cron expression and cron model definitions for shipping_status cron
     */
    const SHIPPING_STATUS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_shipping_status/schedule/cron_expr';
    const SHIPPING_STATUS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_shipping_status/run/model';

    /**
     * Cron expression and cron model definitions for return_status cron
     */
    const RETURN_STATUS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_return_status/schedule/cron_expr';
    const RETURN_STATUS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_return_status/run/model';

    /**
     * Cron expression and cron model definitions for statistics update cron
     */
    const UPDATE_STATISTICS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_statistics/schedule/cron_expr';
    const UPDATE_STATISTICS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_statistics/run/model';

    /**
     * Cron expression and cron model definitions for updating product attributes.
     */
    const UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH = 'crontab/jobs/postnl_update_product_attribute/schedule/cron_expr';
    const UPDATE_PRODUCT_ATTRIBUTE_MODEL_PATH  = 'crontab/jobs/postnl_update_product_attribute/run/model';

    /**
     * Cron expression and cron model definitions for updating product attributes.
     */
    const UPDATE_DATE_TIME_ZONE_STRING_PATH = 'crontab/jobs/postnl_update_date_time_zone/schedule/cron_expr';
    const UPDATE_DATE_TIME_ZONE_MODEL_PATH  = 'crontab/jobs/postnl_update_date_time_zone/run/model';

    /**
     * XML path to the support tab_expanded setting
     */
    const EXPAND_SUPPORT_PATH = 'postnl/support/expanded';

    /**
     * Test data.
     */
    const DEFAULT_TEST_PASSWORD = 'z9A4LpFd53Z';
    const DEFAULT_WEBSHOP_ID    = '853f9d2a4c5242f097daeaf61637609c';

    /**
     * Xpaths for test data.
     */
    const XPATH_TEST_PASSWORD = 'postnl/cif/test_password';
    const XPATH_WEBSHOP_ID    = 'postnl/cif/webshop_id';

    /**
     * Xpath to supported options configuration setting
     */
    const XPATH_SUPPORTED_PRODUCT_OPTIONS = 'postnl/grid/supported_product_options';

    /**
     * Xpath to the item columns setting.
     */
    const XPATH_PACKING_SLIP_ITEM_COLUMNS = 'postnl/packing_slip/item_columns';

    /**
     * Xpath to the product attribute update data used by the product attribute update cron.
     */
    const XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA = 'postnl/general/product_attribute_update_data';

    /**
     * Xpath to the update date time zone data used by the update date time zone cron.
     */
    const XPATH_UPDATE_DATE_TIME_ZONE_DATA = 'postnl/general/product_attribute_update_data';

    /**
     * Minimum server memory required by the PostNL extension in bytes.
     */
    const MIN_SERVER_MEMORY = 268435456; //256MB

    /**
     * Error codes that might be triggered during setup.
     */
    const SUCCESSFUL_UPDATE_ERROR_CODE           = 'POSTNL-0083';
    const SHIPPING_STATUS_CRON_ERROR_CODE        = 'POSTNL-0084';
    const RETURN_STATUS_CRON_ERROR_CODE          = 'POSTNL-0205';
    const UPDATE_STATISTICS_CRON_ERROR_CODE      = 'POSTNL-0085';
    const UNSUPPORTED_MAGENTO_VERSION_ERROR_CODE = 'POSTNL-0086';
    const SUCCESSFUL_INSTALL_ERROR_CODE          = 'POSTNL-0156';
    const MEMORY_LIMIT_ERROR_CODE                = 'POSTNL-0175';
    const UPDATE_PRODUCT_ATTRIBUTE_ERROR_CODE    = 'POSTNL-0197';
    const UPDATE_DATE_TIME_ZONE_ERROR_CODE       = 'POSTNL-0206';

    /**
     * callAfterApplyAllUpdates flag. Causes applyAfterUpdates() to be called.
     *
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = true;

    /**
     * Module version as stored in the db at the time of the update
     *
     * @var string
     */
    protected $_dbVer;

    /**
     * Module version as specified in the module's configuration at the time of the update
     *
     * @var string
     */
    protected $_configVer;

    /**
     * Array of available cif webservice settings.
     *
     * @var array
     */
    protected $_cifWebserviceVersionSettings = array(
        'cif_version_shippingstatus',
        'cif_version_confirming',
        'cif_version_labelling',
        'cif_version_barcode',
        'cif_version_checkout',
        'cif_version_deliverydate',
        'cif_version_timeframe',
        'cif_version_location',
    );

    /**
     * Set the stored DB version to the specified value
     *
     * @param string $dbVer
     *
     * @return $this
     */
    public function setDbVer($dbVer)
    {
        $this->_dbVer = $dbVer;

        return $this;
    }

    /**
     * Set the stored config version to the specified value
     *
     * @param string $configVer
     *
     * @return $this
     */
    public function setConfigVer($configVer)
    {
        $this->_configVer = $configVer;

        return $this;
    }

    /**
     * Get the stored DB version
     *
     * @return string
     */
    public function getDbVer()
    {
        return $this->_dbVer;
    }

    /**
     * get the stored config version
     *
     * @return string
     */
    public function getConfigVer()
    {
        return $this->_configVer;
    }

    /**
     * Gets an array of cif webservice version settings.
     *
     * @return array
     */
    public function getCifWebserviceVersionSettings()
    {
        return $this->_cifWebserviceVersionSettings;
    }

    /**
     * Store the applied update versions
     *
     * @return parent::applyUpdates()
     */
    public function applyUpdates()
    {
        /**
         * @var Mage_Core_Model_Resource_Resource $resource
         * @var Varien_Simplexml_Element $moduleConfig
         */
        $resource = $this->_getResource();
        $moduleConfig = $this->_moduleConfig;

        $dbVer = $resource->getDbVersion($this->_resourceName);
        $configVer = (string) $moduleConfig->version;

        $this->setDbVer($dbVer);
        $this->setConfigVer($configVer);

        return parent::applyUpdates();
    }

    /**
     * Apply data updates to the system after upgrading.
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyDataUninstall()
    {
        $dataVer   = $this->_getResource()->getDataVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;

        if ($dataVer !== false) {
            $this->_uninstallData($dataVer, $configVer);
        }
        return $this;
    }

    /**
     * Check if the PostNL module has been updated. If so, add an admin notification to the inbox.
     *
     * @return $this
     */
    public function afterApplyAllUpdates()
    {
        $dbVer = $this->getDbVer();
        $configVer = $this->getConfigVer();

        if (version_compare($configVer, $dbVer) != self::VERSION_COMPARE_GREATER) {
            return $this;
        }

        $this->_checkVersionCompatibility();
        $this->_checkMemoryRequirement();

        $helper = Mage::helper('postnl');

        $inbox = Mage::getModel('postnl_admin/inbox');
        if ($dbVer) {
            $title = '['
                   . self::SUCCESSFUL_UPDATE_ERROR_CODE
                   . '] '
                   . $helper->__('PostNL extension has been successfully updated to version v%s.', $configVer);

            $url = $helper->getErrorUrl(self::SUCCESSFUL_UPDATE_ERROR_CODE );
        } else {
            $title = '['
                   . self::SUCCESSFUL_INSTALL_ERROR_CODE
                   . '] '
                   . $helper->__('The PostNL extension v%s has been successfully installed.', $configVer);

            $url = $helper->getErrorUrl(self::SUCCESSFUL_INSTALL_ERROR_CODE );
        }

        $message = $helper->__(
            'You can read the release notes in the <a href="%s" target="_blank" title="TIG knowledgebase">TIG ' .
            'knowledgebase</a>.',
            $helper->getChangelogUrl()
        );

        $inbox->addNotice($title, $message, $url, true)
              ->save();

        return $this;
    }

    /**
     * Run data uninstall scripts
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _uninstallData($oldVersion, $newVersion)
    {
        $this->_modifyResourceDb('data-uninstall', $oldVersion, $newVersion);
        $this->_getResource()->setDataVersion($this->_resourceName, false);

        return $this;
    }

    /**
     * Save resource version
     *
     * @param string $actionType
     * @param string $version
     * @return Mage_Core_Model_Resource_Setup
     */
    protected function _setResourceVersion($actionType, $version)
    {
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DB_UPGRADE:
                $this->_getResource()->setDbVersion($this->_resourceName, $version);
                break;
            case self::TYPE_DATA_INSTALL:
            case self::TYPE_DATA_UPGRADE:
                $this->_getResource()->setDataVersion($this->_resourceName, $version);
                break;

        }

        return $this;
    }

    /**
     * Run module modification files. Return version of last applied upgrade (false if no upgrades applied)
     *
     * @param string $actionType self::TYPE_*
     * @param string $fromVersion
     * @param string $toVersion
     * @return string|false
     * @throws Mage_Core_Exception
     */

    protected function _modifyResourceDb($actionType, $fromVersion, $toVersion)
    {
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DB_UPGRADE:
                $files = $this->_getAvailableDbFiles($actionType, $fromVersion, $toVersion);
                break;
            case self::TYPE_DATA_INSTALL:
            case self::TYPE_DATA_UPGRADE:
            case self::TYPE_DATA_UNINSTALL:
                $files = $this->_getAvailableDataFiles($actionType, $fromVersion, $toVersion);
                break;
            default:
                $files = array();
                break;
        }
        if (empty($files) || !$this->getConnection()) {
            return false;
        }

        $version = false;

        foreach ($files as $file) {
            $fileName = $file['fileName'];
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
            $this->getConnection()->disallowDdlCache();
            try {
                switch ($fileType) {
                    case 'php':
                        $conn   = $this->getConnection();
                        $result = include $fileName;
                        break;
                    case 'sql':
                        $sql = file_get_contents($fileName);
                        if (!empty($sql)) {

                            $result = $this->run($sql);
                        } else {
                            $result = true;
                        }
                        break;
                    default:
                        $result = false;
                        break;
                }

                if ($result) {
                    $this->_setResourceVersion($actionType, $file['toVersion']);
                }
            } catch (Exception $e) {
                // This printf is core Magento
                printf('<pre>%s</pre>', print_r($e, true));
                throw Mage::exception('Mage_Core', Mage::helper('core')->__('Error in file: "%s" - %s', $fileName, $e->getMessage()));
            }
            $version = $file['toVersion'];
            $this->getConnection()->allowDdlCache();
        }
        self::$_hadUpdates = true;
        return $version;
    }

    /**
     * Get data files for modifications
     *
     * @param string $actionType
     * @param string $fromVersion
     * @param string $toVersion
     * @param array $arrFiles
     * @return array
     */
    protected function _getModifySqlFiles($actionType, $fromVersion, $toVersion, $arrFiles)
    {
        $arrRes = array();
        switch ($actionType) {
            case self::TYPE_DB_INSTALL:
            case self::TYPE_DATA_INSTALL:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    if (version_compare($version, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[0] = array(
                            'toVersion' => $version,
                            'fileName'  => $file
                        );
                    }
                }
                break;

            case self::TYPE_DB_UPGRADE:
            case self::TYPE_DATA_UPGRADE:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    $versionInfo = explode('-', $version);

                    // In array must be 2 elements: 0 => version from, 1 => version to
                    if (count($versionInfo)!=2) {
                        break;
                    }
                    $infoFrom = $versionInfo[0];
                    $infoTo   = $versionInfo[1];
                    if (version_compare($infoFrom, $fromVersion) !== self::VERSION_COMPARE_LOWER
                        && version_compare($infoTo, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[] = array(
                            'toVersion' => $infoTo,
                            'fileName'  => $file
                        );
                    }
                }
                break;

            case self::TYPE_DATA_UNINSTALL:
                uksort($arrFiles, 'version_compare');
                foreach ($arrFiles as $version => $file) {
                    if (version_compare($version, $toVersion) !== self::VERSION_COMPARE_GREATER) {
                        $arrRes[0] = array(
                            'toVersion' => $version,
                            'fileName'  => $file
                        );
                    }
                }
                break;

            case self::TYPE_DB_ROLLBACK:
                break;

            case self::TYPE_DB_UNINSTALL:
                break;
        }
        return $arrRes;
    }

    /**
     * Generate a random cron expression for the status update cron for this merchant and store it in the database.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function generateShippingStatusCronExpr()
    {
        /**
         * Generate semi-random values for the cron expression.
         */
        $cronMinute        = mt_rand(0, 59);

        $cronNightHour     = mt_rand(1, 3);
        $cronMorningHour   = $cronNightHour + 9; //9 hours after the night update
        $cronAfternoonHour = $cronMorningHour + 4; //4 hours after the morning update

        /**
         * Generate a cron expr that runs on a specified minute on a specified hour between 1 and 3 AM, between 10 and
         * 12 AM, and between 14 and 16 PM.
         */
        $cronExpr = "{$cronMinute} {$cronNightHour},{$cronMorningHour},{$cronAfternoonHour} * * *";

        /**
         * Store the cron expression in core_config_data.
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::SHIPPING_STATUS_CRON_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::SHIPPING_STATUS_CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::SHIPPING_STATUS_CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::SHIPPING_STATUS_CRON_MODEL_PATH))
                ->setPath(self::SHIPPING_STATUS_CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save shipping_status cron expression: %s', $cronExpr),
                self::SHIPPING_STATUS_CRON_ERROR_CODE,
                $e
            );
        }

        return $this;
    }

    /**
     * Generates a semi-random cron expression for the update statistics cron. This is done to spread out the number of
     * calls across each day.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function generateUpdateStatisticsCronExpr()
    {
        /**
         * Generate random values for the cron expression
         */
        $cronMorningHour   = mt_rand(0, 11);
        $cronAfternoonHour = $cronMorningHour + 12; //half a day after the morning update
        $cronMinute        = mt_rand(0, 59);

        /**
         * Generate a cron expr that runs on a specified minute on a specified hour twice per day.
         */
        $cronExpr = "{$cronMinute} {$cronMorningHour},{$cronAfternoonHour} * * *";

        /**
         * Store the cron expression in core_config_data
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_STATISTICS_CRON_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::UPDATE_STATISTICS_CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_STATISTICS_CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::UPDATE_STATISTICS_CRON_MODEL_PATH))
                ->setPath(self::UPDATE_STATISTICS_CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save update_statistics cron expression: %s', $cronExpr),
                self::UPDATE_STATISTICS_CRON_ERROR_CODE,
                $e
            );
        }

        return $this;
    }

    /**
     * Checks the store's config to see if the extension is compatible with the installed Magento version. If not, a
     * message will be added to Mage_AdminNotification.
     *
     * @return $this
     */
    public function _checkVersionCompatibility()
    {
        if (Mage::registry('postnl_version_compatibility_checked')) {
            return $this;
        }

        $helper = Mage::helper('postnl');
        if ($helper->isEnterprise()) {
            $edition = 'enterprise';
        } else {
            $edition = 'community';
        }

        $supportedVersions = Mage::getConfig()->getNode('tig/compatibility/postnl/' . $edition);
        if ($supportedVersions === false) {
            $this->_addUnsupportedVersionMessage();

            Mage::register('postnl_version_compatibility_checked', true);
            return $this;
        }

        $supportedVersions = (string) $supportedVersions;
        $supportedVersionArray = explode(',', $supportedVersions);

        $installedMagentoVersionInfo = Mage::getVersionInfo();
        $installedMagentoVersion = $installedMagentoVersionInfo['major'] . '.' . $installedMagentoVersionInfo['minor'];

        if (!in_array($installedMagentoVersion, $supportedVersionArray)) {
            $this->_addUnsupportedVersionMessage();

            Mage::register('postnl_version_compatibility_checked', true);
            return $this;
        }

        Mage::register('postnl_version_compatibility_checked', true);
        return $this;
    }

    /**
     * @return $this
     *
     * @throws Exception
     */
    protected function _addUnsupportedVersionMessage()
    {
        $helper = Mage::helper('postnl');

        $title = '['
               . self::UNSUPPORTED_MAGENTO_VERSION_ERROR_CODE
               . '] '
               . $helper->__('The PostNL extension is not compatible with your Magento version!');

        $message = $helper->__(
            'This may cause unexpected behaviour. You may use the PostNL extension on unsupported versions of ' .
            'Magento at your own risk.'
        );

        $url = $helper->getErrorUrl(self::UNSUPPORTED_MAGENTO_VERSION_ERROR_CODE );

        $inbox = Mage::getModel('postnl_admin/inbox');
        $inbox->addCritical(
            $title,
            $message,
            $url,
            true
        )->save();

        return $this;
    }

    /**
     * Make sure that the server meets Magento's (and PostNL's) memory requirements.
     *
     * @return $this
     * @throws Exception
     */
    protected function _checkMemoryRequirement()
    {
        if (Mage::registry('postnl_memory_requirement_checked')) {
            return $this;
        }

        $helper = Mage::helper('postnl');

        if ($helper->getMemoryLimit() < self::MIN_SERVER_MEMORY) {
            $memoryMb = self::MIN_SERVER_MEMORY / 1024 / 1024;
            $title = '['
                   . self::MEMORY_LIMIT_ERROR_CODE
                   . '] '
                   . $helper->__("The server's memory limit is less than %.0fMB.", $memoryMb);

            $message = $helper->__(
                'The PostNL extension requires at least %.0fMB to function properly. Using the PostNL extension on ' .
                'servers with less memory than this may cause unexpected errors.',
                $memoryMb
            );

            $inbox = Mage::getModel('postnl_admin/inbox');
            $inbox->addCritical(
                $title,
                $message,
                $helper->getErrorUrl(self::MEMORY_LIMIT_ERROR_CODE),
                true
            )->save();
        }

        Mage::register('postnl_memory_requirement_checked', true);
        return $this;
    }

    /**
     * Makes sure the PostNL support tab is expanded the first time an admin visits the PostNL system/config/edit page.
     *
     * @return $this
     */
    public function expandSupportTab()
    {
        $configState = array(
            'postnl_support' => 1,
        );

        /**
         * Get all admin users and save the PostNL support tab's state as being expanded for each one.
         *
         * This has the same effect as having every admin log in, go to system/config/edit/section/postnl and manually
         * click on the 'Version & Support' tab before saving the section.
         */
        $adminUsers = Mage::getResourceModel('admin/user_collection');
        foreach ($adminUsers as $adminUser) {
            $this->_saveState($adminUser, $configState);
        }

        return $this;
    }

    /**
     * Save state of configuration field sets.
     * Modified version of the Mage_Adminhtml_System_ConfigController::_saveState() method.
     *
     * @param Mage_Admin_Model_User $adminUser
     * @param array $configState
     *
     * @return $this
     *
     * @see Mage_Adminhtml_System_ConfigController::_saveState()
     */
    protected function _saveState(Mage_Admin_Model_User $adminUser, $configState = array())
    {
        if (!is_array($configState)) {
            return $this;
        }

        $extra = $adminUser->getExtra();
        if (!is_array($extra)) {
            $extra = array();
        }

        if (!isset($extra['configState'])) {
            $extra['configState'] = array();
        }

        foreach ($configState as $fieldset => $state) {
            $extra['configState'][$fieldset] = $state;
        }

        try {
            $adminUser->saveExtra($extra);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Resets a specific webservice version settings to the defaults specified in config.xml. If no parameter is
     * provided, all settings will be reset.
     *
     * Please note that you will have to clear the caches after this method is used. Since this should be used during an
     * upgrade of the extension, you should be doing that anyway.
     *
     * @param boolean|array $webservices
     *
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function resetWebserviceVersions($webservices = false)
    {
        if ($webservices === false) {
            $webservices = $this->getCifWebserviceVersionSettings();
        }

        foreach ($webservices as &$service) {
            if (strpos($service, 'postnl/advanced/') === false) {
                $service = 'postnl/advanced/' . $service;
            }
        }

        $this->resetConfig($webservices);

        return $this;
    }

    /**
     * Resets an array of config fields to factory defaults. This is done by actually deleting the config value from the
     * core_config_data table.
     * Because Magento overwrites the default values in the config.xml files with those from the db, if we remove the
     * values from the db, the default values from the config.xml files will again be used.
     *
     * @param array|string $configFields
     *
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function resetConfig($configFields)
    {
        if (!is_array($configFields)) {
            $configFields = array($configFields);
        }

        $config = Mage::getConfig();
        foreach ($configFields as $setting) {
            $config->deleteConfig($setting);
        }

        return $this;
    }

    /**
     * Saves the default test password in the database as an encrypted string.
     *
     * @return $this
     */
    public function installTestPassword()
    {
        $testPassword = self::DEFAULT_TEST_PASSWORD;
        $encryptedPassword = Mage::helper('core')->encrypt($testPassword);

        /**
         * @var Mage_Core_Model_Config_Data $config
         */
        $config = Mage::getModel('core/config_data')
                      ->load(self::XPATH_TEST_PASSWORD, 'path');

        $config->setValue($encryptedPassword)
               ->setPath(self::XPATH_TEST_PASSWORD)
               ->save();

        return $this;
    }

    /**
     * Saves the default test webshop ID as an encrypted string.
     *
     * @return $this
     */
    public function installWebshopId()
    {
        $testWebshopId = self::DEFAULT_WEBSHOP_ID;
        $encryptedWebshopId = Mage::helper('core')->encrypt($testWebshopId);

        /**
         * @var Mage_Core_Model_Config_Data $config
         */
        $config = Mage::getModel('core/config_data')
                      ->load(self::XPATH_WEBSHOP_ID, 'path');

        $config->setValue($encryptedWebshopId)
               ->setPath(self::XPATH_WEBSHOP_ID)
               ->save();

        return $this;
    }

    /**
     * Moves a config value from one place to another, by copying it's value. If the $removeOldValue parameter is true,
     * we also remove the old value.
     *
     * @param string  $fromXpath
     * @param string  $toXpath
     * @param boolean $removeOldValue
     *
     * @return $this
     *
     * @deprecated v1.4.1 This method has been superseded by the
     *                    TIG_PostNL_Model_Resource_Setup::moveConfigSettingInDb() method.
     */
    public function moveConfigSetting($fromXpath, $toXpath, $removeOldValue = true)
    {
        /**
         * Get the current default value.
         */
        $defaultValue = Mage::getConfig()->getNode($fromXpath, 'default');

        /**
         * First loop through all stores.
         *
         * @var Mage_Core_Model_Store $store
         */
        $stores = Mage::app()->getStores();
        $scope  = 'store';
        foreach ($stores as $store) {
            $scopeId = $store->getId();

            $this->moveConfigSettingForScope($fromXpath, $toXpath, $scope, $scopeId, $removeOldValue, $defaultValue);
        }

        /**
         * Now loop through all websites.
         *
         * @var Mage_Core_Model_Website $website
         */
        $websites = Mage::app()->getWebsites();
        $scope    = 'website';
        foreach ($websites as $website) {
            $scopeId = $website->getId();

            $this->moveConfigSettingForScope($fromXpath, $toXpath, $scope, $scopeId, $removeOldValue, $defaultValue);
        }

        /**
         * Finally, try to move the config setting for the default scope.
         */
        $this->moveConfigSettingForScope(
            $fromXpath,
            $toXpath,
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID,
            $removeOldValue
        );

        return $this;
    }

    /**
     * Move a config setting for a specified scope.
     *
     * @param string  $fromXpath
     * @param string  $toXpath
     * @param string  $scope
     * @param int     $scopeId
     * @param boolean $removeOldValue
     * @param boolean $defaultValue
     *
     * @return $this
     *
     * @deprecated v1.4.1 This method has been superseded by the
     *                    TIG_PostNL_Model_Resource_Setup::moveConfigSettingInDb() method.
     */
    public function moveConfigSettingForScope($fromXpath, $toXpath, $scope = 'default', $scopeId = 0,
                                             $removeOldValue = true, $defaultValue = false)
    {
        $config = Mage::getConfig();

        if ($scope == 'store') {
            $scopeCode = Mage::app()->getStore($scopeId)->getCode();
        } elseif ($scope == 'website') {
            $scopeCode = Mage::app()->getWebsite($scopeId)->getCode();
        } else {
            $scopeCode = null;
        }

        $node = $config->getNode($fromXpath, $scope, $scopeCode);

        /**
         * If the node is not set for the default scope, there is nothing left to do.
         */
        if ($node === false) {
            return $this;
        }

        /**
         * Get the string representation of the value.
         */
        $currentValue = $node->__toString();

        if ($defaultValue !== false && $currentValue == $defaultValue) {
            return $this;
        }

        /**
         * Save the value to the new xpath for the scope from which we got the old value.
         */
        $config->saveConfig($toXpath, $currentValue, $scope, $scopeId);

        /**
         * Optionally remove the value from the old xpath.
         */
        if ($removeOldValue) {
            $config->deleteConfig($fromXpath, $scope, $scopeId);
        }

        return $this;
    }

    /**
     * Adds new supported product codes.
     *
     * @param array|string|int $codes
     *
     * @return $this
     */
    public function addSupportedProductCode($codes)
    {
        if (!is_array($codes)) {
            $codes = array((string) $codes);
        }

        /**
         * Get the currently supported product codes.
         */
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $supportedProductCodes = Mage::getStoreConfig(self::XPATH_SUPPORTED_PRODUCT_OPTIONS, $adminStoreId);

        /**
         * If no supported product codes are set, it means the default option is used, which should already contain the
         * new codes.
         */
        if ($supportedProductCodes === null) {
            return $this;
        }

        $supportedCodesArray = explode(',', $supportedProductCodes);

        /**
         * Add the new codes to the existing codes by merging both arrays and then getting only the unique values.
         * Finally we implode the array, so that we can store it in the core_config_data table.
         */
        $mergedCodes = array_merge($supportedCodesArray, $codes);
        $uniqueCodes = array_unique($mergedCodes);
        $newCodes    = implode(',', $uniqueCodes);

        /**
         * Save the supported product codes.
         */
        Mage::getConfig()->saveConfig(
            self::XPATH_SUPPORTED_PRODUCT_OPTIONS,
            $newCodes,
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        return $this;
    }

    /**
     * Clears the config cache. This should be called after changes have been made to the shop's configuration.
     *
     * @return $this
     */
    public function clearConfigCache()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        return $this;
    }

    /**
     * Sets the order ID of every postNL shipment. this is mostly for convenience's sake. Using the new order ID we can
     * load an order directly from the PostNL shipment without first having to load the Magento shipment.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setOrderId()
    {
        $transactionSave = Mage::getResourceModel('core/transaction');

        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        foreach ($postnlShipmentCollection as $shipment) {
            try {
                /**
                 * The getOrderId() method will calculate and set the order id if none is available.
                 */
                $shipment->getOrderId();
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
                continue;
            }

            if ($shipment->hasDataChanges()) {
                $transactionSave->addObject($shipment);
            }
        }

        $transactionSave->save();

        return $this;
    }

    /**
     * Sets the shipment type of every PostNL shipment. Before 1.3.0 the shipment type was determined on the fly. Since
     * 1.3.0 it is instead set once in the PostNL Shipment table. This method updates the table for all PostNL shipments
     * that do not yet have a shipment type.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setShipmentType()
    {
        $transactionSave = Mage::getResourceModel('core/transaction');

        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        foreach ($postnlShipmentCollection as $shipment) {
            try {
                /**
                 * The getShipmentType() method will calculate and set the shipment type if none is available.
                 */
                $shipment->getShipmentType();
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
                continue;
            }

            if ($shipment->hasDataChanges()) {
                $transactionSave->addObject($shipment);
            }
        }

        $transactionSave->save();

        return $this;
    }

    /**
     * Sets the newly added 'is_buspakje' flag of every PostNL shipment.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setIsBuspakje()
    {
        $transactionSave = Mage::getResourceModel('core/transaction');

        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        foreach ($postnlShipmentCollection as $shipment) {
            /**
             * Set the 'is_buspakje' flag to false for all existing shipments.
             */
            $shipment->setIsBuspakje(false);

            if ($shipment->hasDataChanges()) {
                $transactionSave->addObject($shipment);
            }
        }

        $transactionSave->save();

        return $this;
    }

    /**
     * Add new resources to all admin roles.
     *
     * @param array      $resourcesToAdd The resources to add.
     * @param null|array $resourcesRequired The resources that a role already needs to have.
     *
     * @return $this
     */
    public function addAclRules($resourcesToAdd, $resourcesRequired = null)
    {
        $adminRoles = Mage::getResourceModel('admin/role_collection');

        /**
         * @var Mage_Admin_Model_Rules $role
         */
        foreach ($adminRoles as $role) {
            $rules = Mage::getResourceModel('admin/rules_collection')->getByRoles($role->getId());
            $rules->addFieldToFilter('permission', array('eq' => 'allow'));
            $resources = $rules->getColumnValues('resource_id');

            /**
             * If the role has no resources, it's probably deleted and we shouldn't add any.
             */
            if (!$resources) {
                continue;
            }

            /**
             * If the role has the resource 'all', it already has access to everything.
             */
            if (in_array('all', $resources)) {
                continue;
            }

            /**
             * If any resources are required, check that the role has these resources. If even one of the required
             * resources is missing, skip this role.
             */
            if ($resourcesRequired) {
                foreach ($resourcesRequired as $requiredResource) {
                    if (!in_array($requiredResource, $resources)) {
                        continue(2);
                    }
                }
            }

            /**
             * Add the new resources to the existing ones.
             */
            $resources = array_merge($resources, $resourcesToAdd);

            /**
             * Save the role.
             */
            Mage::getModel('admin/rules')
                ->setRoleId($role->getId())
                ->setResources($resources)
                ->saveRel();
        }

        return $this;
    }

    /**
     * Installs the default value for the PostNL packing slip 'item_columns' configuration setting.
     *
     * @return $this
     */
    public function installPackingSlipItemColumns()
    {
        /**
         * These are the default item columns that need to be added.
         */
        $itemColumns = array (
            'postnl_packing_slip_item_column_0' => array (
                'field'    => 'name',
                'title'    => 'Name',
                'width'    => '255',
                'position' => '10',
            ),
            'postnl_packing_slip_item_column_1' => array (
                'field'    => 'sku',
                'title'    => 'SKU',
                'width'    => '90',
                'position' => '20',
            ),
            'postnl_packing_slip_item_column_2' => array (
                'field'    => 'price',
                'title'    => 'Price',
                'width'    => '70',
                'position' => '30',
            ),
            'postnl_packing_slip_item_column_3' => array (
                'field'    => 'qty',
                'title'    => 'Qty',
                'width'    => '60',
                'position' => '40',
            ),
            'postnl_packing_slip_item_column_4' => array (
                'field'    => 'tax',
                'title'    => 'VAT',
                'width'    => '80',
                'position' => '50',
            ),
            'postnl_packing_slip_item_column_5' => array (
                'field'    => 'subtotal',
                'title'    => 'Subtotal',
                'width'    => '40',
                'position' => '60',
            ),
        );

        /**
         * Save the columns as a serialized array.
         */
        Mage::getConfig()->saveConfig(
            self::XPATH_PACKING_SLIP_ITEM_COLUMNS,
            serialize($itemColumns),
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        return $this;
    }

    /**
     * Updates attribute data for all existing products of specific types.
     *
     * @param array $attributesData An array of attribute data as $attributeCode => $value.
     * @param array $productTypes   An array of product types for which these attributes need to be updated.
     *
     * @return $this
     */
    public function updateAttributeValues($attributesData, $productTypes)
    {
        if (!is_array($productTypes)) {
            $productTypes = array($productTypes);
        }

        /**
         * Get all products which are of the specified types.
         */
        $productCollection = Mage::getResourceModel('catalog/product_collection')
                                 ->addStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID)
                                 ->addFieldToFilter(
                                     'type_id',
                                     array(
                                         'in' => $productTypes
                                     )
                                 );

        /**
         * Update the attributes of these products.
         */
        Mage::getSingleton('catalog/product_action')
            ->updateAttributes($productCollection->getAllIds(), $attributesData, Mage_Core_Model_App::ADMIN_STORE_ID);

        return $this;
    }

    /**
     * Set the product attribute update cron's cron expression and save the necessary attribute data.
     *
     * @param array $data
     *
     * @return $this
     * @throws TIG_PostNL_Exception
     */
    public function setProductAttributeUpdateCron($data)
    {
        /**
         * Check if any existing data is present.
         */
        $existingData = Mage::getStoreConfig(
            self::XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        /**
         * Merge the existing data with the new data.
         */
        if ($existingData) {
            $data = array_merge($data, unserialize($existingData));
        }

        /**
         * Serialize the attribute data for storage in the database.
         */
        $serializedData = serialize($data);

        /**
         * Save the attribute data.
         */
        Mage::getConfig()->saveConfig(
            self::XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA,
            $serializedData,
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        $cronExpr = "*/5 * * * *";

        /**
         * Store the cron expression in core_config_data.
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_PRODUCT_ATTRIBUTE_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::UPDATE_PRODUCT_ATTRIBUTE_MODEL_PATH))
                ->setPath(self::UPDATE_PRODUCT_ATTRIBUTE_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save update_product_attribute cron expression: %s', $cronExpr),
                self::UPDATE_PRODUCT_ATTRIBUTE_ERROR_CODE,
                $e
            );
        }

        return $this;
    }

    /**
     * Install new matrix rate data.
     *
     * @param array $data
     *
     * @return $this
     */
    public function installMatrixRates(array $data)
    {
        try {
            Mage::getResourceModel('postnl_carrier/matrixrate')->import($data);
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Add newly supported shipping methods.
     *
     * @param array|string $methods
     *
     * @return $this
     */
    public function addSupportedShippingMethods($methods)
    {
        if (!is_array($methods)) {
            $methods = array($methods);
        }

        /**
         * Get the current shipping methods for the default config.
         */
        $defaultShippingMethods = Mage::getStoreConfig(
            'postnl/advanced/postnl_shipping_methods',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        $defaultShippingMethods    = explode(',', $defaultShippingMethods);

        /**
         * Merge with the new methods and save the config.
         */
        $newDefaultShippingMethods = array_merge($defaultShippingMethods, $methods);
        Mage::getConfig()->saveConfig(
            'postnl/advanced/postnl_shipping_methods',
            implode(',', $newDefaultShippingMethods),
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        return $this;
    }

    /**
     * Copy a config setting from an old xpath to a new xpath directly in the database, rather than using Magento config
     * entities.
     *
     * @param string $fromXpath
     * @param string $toXpath
     *
     * @return $this
     */
    public function moveConfigSettingInDb($fromXpath, $toXpath)
    {
        $conn = $this->getConnection();

        try {
            $select = $conn->select()
                           ->from($this->getTable('core/config_data'))
                           ->where('path = ?', $fromXpath);

            $result = $conn->fetchAll($select);
            foreach ($result as $row) {
                try {
                    /**
                     * Copy the old setting to the new setting.
                     *
                     * @todo Check if the row already exists.
                     */
                    $conn->insert(
                        $this->getTable('core/config_data'),
                        array(
                            'scope'    => $row['scope'],
                            'scope_id' => $row['scope_id'],
                            'value'    => $row['value'],
                            'path'     => $toXpath
                        )
                    );
                } catch (Exception $e) {
                    Mage::helper('postnl')->logException($e);
                }
            }
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Moves and merges the PostNL active setting to and with the PostNL mode setting.
     *
     * @return $this
     */
    public function moveActiveSetting()
    {
        $conn = $this->getConnection();

        try {
            /**
             * Modify all mode settings with value 0 (Live) to value 2 (the new live mode value).
             */
            $conn->update(
                $this->getTable('core/config_data'),
                array(
                    'value' => 2,
                ),
                array(
                    'path = ?' => 'postnl/cif/mode',
                    'value = ?' => 0
                )
            );
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        try {
            /**
             * Get all scopes for which PostNl is disabled.
             */
            $disabledSelect = $conn->select()
                                   ->from($this->getTable('core/config_data'))
                                   ->where('path = ?', 'postnl/general/active')
                                   ->where('value = ?', 0);

            $disabledRows = $conn->fetchAll($disabledSelect);
            foreach ($disabledRows as $disabledRow) {
                try {
                    /**
                     * Set the mode to 0 (off) for these scopes.
                     */
                    $conn->update(
                        $this->getTable('core/config_data'),
                        array(
                            'value' => 0,
                        ),
                        array(
                            'path = ?'     => 'postnl/cif/mode',
                            'scope_id = ?' => $disabledRow['scope_id'],
                            'scope = ?'    => $disabledRow['scope'],
                        )
                    );
                } catch (Exception $e) {
                    Mage::helper('postnl')->logException($e);
                }
            }
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Generate a random cron expression for the return status update cron for this merchant and store it in the
     * database.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function generateReturnStatusCronExpr()
    {
        /**
         * Generate semi-random values for the cron expression.
         */
        $cronMinute        = mt_rand(0, 59);

        $cronNightHour     = mt_rand(0, 3);
        $cronMorningHour   = $cronNightHour + 8; //8 hours after the night update
        $cronAfternoonHour = $cronMorningHour + 8; //8 hours after the morning update

        /**
         * Generate a cron expr that runs on a specified minute on a specified hour between 0 and 3 AM, between 8 and
         * 11 AM, and between 4 and 7 PM.
         */
        $cronExpr = "{$cronMinute} {$cronNightHour},{$cronMorningHour},{$cronAfternoonHour} * * *";

        /**
         * Store the cron expression in core_config_data.
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::RETURN_STATUS_CRON_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::RETURN_STATUS_CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::RETURN_STATUS_CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::RETURN_STATUS_CRON_MODEL_PATH))
                ->setPath(self::RETURN_STATUS_CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save return_status cron expression: %s', $cronExpr),
                self::RETURN_STATUS_CRON_ERROR_CODE,
                $e
            );
        }

        return $this;
    }

    /**
     * Set the date time zone update cron's cron expression and save the necessary attribute data.
     *
     * @return $this
     * @throws TIG_PostNL_Exception
     */
    public function setDateTimeZoneUpdateCron()
    {
        /**
         * Get the PostNL shipment and order IDs that need to be processed.
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToSelect('entity_id');
        $postnlShipmentIds = $postnlShipmentCollection->getAllIds();

        $postnlOrderCollection = Mage::getResourceModel('postnl_core/order_collection');
        $postnlOrderCollection->addFieldToSelect('entity_id');
        $postnlOrderIds = $postnlOrderCollection->getAllIds();

        $idsToProcess = array(
            'shipment' => $postnlShipmentIds,
            'order'    => $postnlOrderIds,
        );

        /**
         * Serialize the IDs that need to be processed for storage in the database.
         */
        $serializedData = serialize($idsToProcess);

        /**
         * Save the IDs that need to be processed.
         */
        Mage::getConfig()->saveConfig(
            self::XPATH_UPDATE_DATE_TIME_ZONE_DATA,
            $serializedData,
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        $cronExpr = "*/5 * * * *";

        /**
         * Store the cron expression in core_config_data.
         */
        try {
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_DATE_TIME_ZONE_STRING_PATH, 'path')
                ->setValue($cronExpr)
                ->setPath(self::UPDATE_DATE_TIME_ZONE_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::UPDATE_DATE_TIME_ZONE_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::UPDATE_DATE_TIME_ZONE_MODEL_PATH))
                ->setPath(self::UPDATE_DATE_TIME_ZONE_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Unable to save update_date_time_zone cron expression: %s', $cronExpr),
                self::UPDATE_DATE_TIME_ZONE_ERROR_CODE,
                $e
            );
        }

        return $this;
    }

    /**
     * Moves and merges the PostNL delivery options allow backorders setting to and with the PostNL delivery options
     * stock options setting.
     *
     * @return $this
     */
    public function moveDeliveryOptionsStockSetting()
    {
        $conn = $this->getConnection();

        try {
            /**
             * Modify all mode settings with value 1 (allow backorders) to value 'backordered' and modify the path to
             * the new setting.
             */
            $conn->update(
                $this->getTable('core/config_data'),
                array(
                    'path = ?' => 'postnl/delivery_options/stock_options',
                    'value'    => 'backordered',
                ),
                array(
                    'path = ?' => 'postnl/delivery_options/show_options_for_backorders',
                    'value = ?' => 1
                )
            );
        } catch (Exception $e) {
            Mage::helper('postnl')->logException($e);
        }

        return $this;
    }

    /**
     * Prepare attribute values to save.
     *
     * @param array $attr
     *
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
                'apply_to' => $this->_getValue($attr, 'apply_to'),
            )
        );

        return $data;
    }
}
