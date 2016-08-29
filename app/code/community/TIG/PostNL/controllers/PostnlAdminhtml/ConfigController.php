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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_PostnlAdminhtml_ConfigController extends TIG_PostNL_Controller_Adminhtml_Config
{
    /**
     * Base XML path of config settings that will be checked.
     */
    const XML_BASE_PATH = 'postnl/cif';

    /**
     * XML paths to passwords.
     */
    const XPATH_LIVE_PASSWORD = 'postnl/cif/live_password';
    const XPATH_TEST_PASSWORD = 'postnl/cif/test_password';

    /**
     * XML path to the domestic country.
     */
    const XPATH_POSTNL_CIF_ADDRESS_COUNTRY = 'postnl/cif_address/country';

    /**
     * XML path to the use Dutch products setting.
     */
    const XPATH_USE_DUTCH_PRODUCTS = 'postnl/cif_labels_and_confirming/use_dutch_products';

    /**
     * @var boolean
     */
    protected $_isTestMode = false;

    /**
     * @var bool
     */
    protected $_doRefresh = false;

    /**
     * Validate the extension's account settings.
     *
     * @return $this
     */
    public function validateAccountAction()
    {
        /**
         * Get all post data
         */
        $data = $this->getRequest()->getPost();

        /**
         * Validate that all required fields are entered
         */
        if (!isset($data['customerNumber'])
            || !isset($data['customerCode'])
            || !isset($data['username'])
            || !isset($data['password'])
            || !isset($data['locationCode'])
            || !isset($data['isTestMode'])
        ) {
            $this->getResponse()
                 ->setBody('missing_data');

            return $this;
        }

        $data = $this->_getInheritedValues($data);

        if ($data['isTestMode'] === 'true') {
            $this->_isTestMode = true;
        }

        /**
         * Attempt to generate a barcode to test the account settings. This will result in an exception if the settings
         * are invalid.
         */
        try {
            /**
             * If the password field has not been edited since the last time it was saved, it will contain 6 asterisks
             * for security reasons. In that case, we need to read and decrypt the password from the database.
             */
            if ($data['password'] == '******') {
                $data['password'] = $this->_getPassword(false);
            } elseif ($data['password'] == 'inherit') {
                $data['password'] = $this->_getPassword(true);
            }

            /**
             * Hash the password
             */
            $data['password'] = sha1($data['password']);

            /**
             * Load the CIF model and set to test mode to false.
             */
            /** @var TIG_PostNL_Model_Core_Cif $cif */
            $cif = Mage::getModel('postnl_core/cif');
            $cif->setTestMode($this->_isTestMode);

            $response = $cif->generateBarcodePing($data);
        } catch (Exception $e) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * A positive result would be a string, namely a barcode.
         */
        if (!is_string($response)) {
            $this->getResponse()
                 ->setBody('invalid_response');

            return $this;
        }

        $this->getResponse()
             ->setBody('ok');

        return $this;
    }

    /**
     * Checks each field to see if it has used the 'use default checkbox'. If so, get the default value from the
     * database.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _getInheritedValues($data)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $baseXpath = self::XML_BASE_PATH;

        $usernameXpath = $baseXpath . '/live_username';
        if ($this->_isTestMode) {
            $usernameXpath = $baseXpath . '/test_username';
        }

        foreach ($data as $key => &$value) {
            if ($value != 'inherit') {
                continue;
            }

            switch ($key) {
                case 'customerNumber':
                    $value = Mage::getStoreConfig($baseXpath . '/customer_number', $storeId);
                    break;
                case 'customerCode':
                    $value = Mage::getStoreConfig($baseXpath . '/customer_code', $storeId);
                    break;
                case 'username':
                    $value = Mage::getStoreConfig($usernameXpath, $storeId);
                    break;
                case 'locationCode':
                    $value = Mage::getStoreConfig($baseXpath . '/collection_location', $storeId);
                    break;
                //No default
                //Note that the password field is not checked. That field has it's own check later on.
            }
        }

        return $data;
    }

    /**
     * Gets the password from system/config.
     * Passwords will be decrypted using Magento's encryption key and then hashed using sha1
     *
     * @param boolean $inherit
     *
     * @return string
     */
    protected function _getPassword($inherit = false)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $xpath = self::XPATH_LIVE_PASSWORD;
        if ($this->_isTestMode) {
            $xpath = self::XPATH_TEST_PASSWORD;
        }

        $websiteCode = $this->getRequest()->getParam('website');
        if (!$inherit && !empty($websiteCode)) {
            /** @var Mage_Core_Model_Website $website */
            $website = Mage::getModel('core/website')->load($websiteCode, 'code');
            $password = $website->getConfig($xpath);
        } else {
            $password = Mage::getStoreConfig($xpath, $storeId);
        }

        /** @var Mage_Core_Helper_Data $helper */
        $helper = Mage::helper('core');
        $password = $helper->decrypt($password);

        return trim($password);
    }

    /**
     * Export shipping table rates in csv format.
     *
     * @return $this
     */
    public function exportTableratesAction()
    {
        $fileName   = 'tablerates.csv';

        /**
         * @var TIG_PostNL_Block_Adminhtml_Carrier_Postnl_Tablerate_Grid $gridBlock
         */
        $gridBlock  = $this->getLayout()->createBlock('postnl_adminhtml/carrier_postnl_tablerate_grid');
        $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));

        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/postnl/condition_name');
        }

        $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);

        $content = $gridBlock->getCsvFile();

        $this->postDispatch();
        $this->_prepareDownloadResponse($fileName, $content);

        return $this;
    }

    /**
     * Export shipping matrix rates in csv format.
     *
     * @return $this
     */
    public function exportMatrixratesAction()
    {
        $fileName   = 'matrixrates.csv';

        /**
         * @var TIG_PostNL_Block_Adminhtml_Carrier_Postnl_Matrixrate_Grid $gridBlock
         */
        $gridBlock  = $this->getLayout()->createBlock('postnl_adminhtml/carrier_postnl_matrixrate_grid');
        $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));

        $gridBlock->setWebsiteId($website->getId());

        $content = $gridBlock->getCsvFile();

        $this->postDispatch();
        $this->_prepareDownloadResponse($fileName, $content);

        return $this;
    }

    /**
     * Download all PostNL log files as a zip file.
     *
     * @return $this
     */
    public function downloadLogsAction()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        if (!$helper->checkIsPostnlActionAllowed('download_logs')) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0155', 'error',
                $this->__('The current user is not allowed to perform this action.')
            );

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }

        /**
         * Get a zip file containing all valid PostNL logs.
         */
        try {
            /** @var TIG_PostNL_Model_Adminhtml_Support_Logs $logsModel */
            $logsModel = Mage::getModel('postnl_adminhtml/support_logs');
            $zip = $logsModel->downloadLogs();
        } catch (TIG_PostNL_Exception $e) {
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        } catch (Exception $e) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0010', 'error',
                $this->__('An error occurred while processing this action.')
            );

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }

        $zipName = explode(DS, $zip);
        $zipName = end($zipName);

        /**
         * Offer the zip file as a download response. The 'rm' key will cause Magento to remove the zip file from the
         * server after it's finished.
         */
        $content = array(
            'type'  => 'filename',
            'value' => $zip,
            'rm'    => true,
        );

        $this->postDispatch();
        $this->_prepareDownloadResponse($zipName, $content);

        return $this;
    }

    /**
     * Saves a step in the PostNL configuration wizard.
     *
     * This functionality is nearly identical to Magento's configuration save action.
     *
     * @return $this
     *
     * @see Mage_Adminhtml_System_ConfigController::saveAction()
     */
    public function saveWizardStepAction()
    {
        $groups = $this->getRequest()->getPost('groups');

        try {
            if (!$this->_isSectionAllowed($this->getRequest()->getParam('section'))) {
                $this->getResponse()
                     ->setBody('redirect');

                return $this;
            }

            $this->_isDomesticCountryChanged();
            $this->_isUseDutchProductsChanged();

            /**
             * custom save logic
             */
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            /** @var Mage_Adminhtml_Model_Config_Data $configData */
            $configData = Mage::getSingleton('adminhtml/config_data');
            /** @noinspection PhpUndefinedMethodInspection */
            $configData->setSection($section)
                       ->setWebsite($website)
                       ->setStore($store)
                       ->setGroups($groups)
                       ->save();

            /**
             * reinit configuration
             */
            Mage::getConfig()->reinit();
            Mage::dispatchEvent('admin_system_config_section_save_after', array(
                    'website' => $website,
                    'store'   => $store,
                    'section' => $section
                ));
            Mage::app()->reinitStores();

            /**
             * website and store codes can be used in event implementation, so set them as well
             */
            Mage::dispatchEvent("admin_system_config_changed_section_{$section}",
                array('website' => $website, 'store' => $store)
            );

            $this->_saveState($this->getRequest()->getPost('config_state'));

            /**
             * Save the next wizard step as the current step the admin user is on.
             */
            $nextStep = $this->getRequest()->getPost('next_step_hash');
            if ($nextStep) {
                $this->_saveCurrentWizardStep($nextStep);
            }

            if ($this->_doRefresh) {
                $this->getResponse()
                    ->setBody('refresh');
            } else {
                $this->getResponse()
                    ->setBody('success');
            }
        } catch (TIG_PostNL_Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody(
                     $helper->getSessionMessage($e->getCode(), 'error', $e->getMessage()
                 )
            );

            return $this;
        } catch (Mage_Core_Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody($e->getMessage());

            return $this;
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody(
                     Mage::helper('adminhtml')->__('An error occurred while saving this configuration:')
                     . ' '
                     . $e->getMessage()
                 );

            return $this;
        }

        return $this;
    }

    /**
     * Saves the hidden state for a specified admin notification.
     *
     * @return $this
     */
    public function hideNotificationAction()
    {
        $notificationCode = $this->getRequest()->getParam('notification_code');
        if (!$notificationCode) {
            $this->getResponse()
                 ->setBody('missing_code');

            return $this;
        }

        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Admin_Model_User $adminUser */
        $adminUser = $session->getUser();
        if (!$adminUser) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        try {
            $extra = $adminUser->getExtra();

            $extra['postnl']['hidden_notification'][$notificationCode] = true;

            $adminUser->saveExtra($extra);
        } catch (Exception $e) {
            /** @var TIG_PostNL_Helper_Data $helper */
            $helper = Mage::helper('postnl');
            $helper->logException($e);

            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        $this->getResponse()
             ->setBody('success');

        return $this;
    }

    /**
     *  Custom save logic for section
     */
    protected function _saveSection ()
    {
        $method = '_save' . uc_words($this->getRequest()->getParam('section'), '');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $section
     * @return bool
     */
    protected function _isSectionAllowed($section)
    {
        try {
            /** @var Mage_Admin_Model_Session $session */
            $session = Mage::getSingleton('admin/session');
            $resourceLookup = "admin/system/config/{$section}";
            if ($session->getData('acl') instanceof Mage_Admin_Model_Acl) {
                $resourceId = $session->getData('acl')->get($resourceLookup)->getResourceId();
                if (!$session->isAllowed($resourceId)) {
                    throw new Exception('');
                }
                return true;
            }
        }
        catch (Zend_Acl_Exception $e) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        catch (Exception $e) {
            $this->deniedAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        return false;
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = array())
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        /** @noinspection PhpUndefinedMethodInspection */
        /** @var Mage_Admin_Model_User $adminUser */
        $adminUser = $session->getUser();
        if (is_array($configState)) {
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

            $adminUser->saveExtra($extra);
        }

        return true;
    }

    /**
     * Check if the domestic country is changed. If that is the case we want to reload the page so all settings are
     * adjusted for the correct domestic country.
     *
     * @return $this
     */
    protected function _isDomesticCountryChanged()
    {
        if (!$this->_doRefresh) {
            $this->_doRefresh = $this->_isValueChanged(self::XPATH_POSTNL_CIF_ADDRESS_COUNTRY);
        }

        return $this;
    }

    /**
     * Check if the can use Dutch products option is changed. In that case we want to reload the page.
     *
     * @return $this
     */
    protected function _isUseDutchProductsChanged()
    {
        if (!$this->_doRefresh) {
            $this->_doRefresh = $this->_isValueChanged(self::XPATH_USE_DUTCH_PRODUCTS);
        }

        return $this;
    }

    /**
     * Check if a particular value is changed
     *
     * @param $xPath
     *
     * @return bool
     */
    protected function _isValueChanged($xPath)
    {
        /**
         * Xpath: postnl/GROUP/VALUE
         */
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($section, $group, $field) = explode('/', $xPath);
        $groups = $this->getRequest()->getPost('groups');

        if (
            array_key_exists($group, $groups) &&
            array_key_exists($field, $groups[$group]['fields'])
        ) {
            $value = $groups[$group]['fields'][$field]['value'];
            $configValue = Mage::getStoreConfig($xPath);

            return $value != $configValue;
        }

        return false;
    }
}
