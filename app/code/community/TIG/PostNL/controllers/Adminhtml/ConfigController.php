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
class TIG_PostNL_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Base XML path of config settings taht will be checked
     */
    const XML_BASE_PATH = 'postnl/cif';

    /**
     * XML path to password
     */
    const XML_PATH_LIVE_PASSWORD = 'postnl/cif/live_password';
    const XML_PATH_TEST_PASSWORD = 'postnl/cif/test_password';

    /**
     * @var boolean
     */
    protected $_isTestMode;

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

        $this->_isTestMode = (bool) $data['isTestMode'];

        /**
         * If the password field has not been edited since the last time it was saved, it will contain 6 asteriscs for security
         * reasons. In that case, we need to read and decrypt the password from the database.
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
         * Load the CIF model and set to test mode to false
         *
         * @var TIG_PostNL_Model_Core_Cif $cif
         */
        $cif = Mage::getModel('postnl_core/cif')
                   ->setTestMode($this->_isTestMode);

        /**
         * Attempt to generate a barcode to test the account settings. This will result in an exception if the settings are
         * invalid.
         */
        try {
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
     * Checks each field to see if it has used the 'use default checkbox'. If so, get the default value from the database.
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

        $xpath = self::XML_PATH_LIVE_PASSWORD;
        if ($this->_isTestMode) {
            $xpath = self::XML_PATH_TEST_PASSWORD;
        }

        try {
            $websiteCode = $this->getRequest()->getParam('website');
            if (!$inherit && !empty($websiteCode)) {
                $website = Mage::getModel('core/website')->load($websiteCode, 'code');
                $password = $website->getConfig($xpath);
            } else {
                $password = Mage::getStoreConfig($xpath, $storeId);
            }

            $password = Mage::helper('core')->decrypt($password);
        } catch (Exception $e) {
            return '';
        }

        return trim($password);
    }
}