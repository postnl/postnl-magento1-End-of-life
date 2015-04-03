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
 *
 * @method boolean hasStoreId()
 * @method boolean hasWebsite()
 *
 * @method string getStoreCode()
 * @method string getWebsiteCode()
 * @method array  getGroups()
 *
 * @method TIG_PostNL_Model_AddressValidation_System_Config_Backend_ValidateAccount setStoreId(int $value)
 * @method TIG_PostNL_Model_AddressValidation_System_Config_Backend_ValidateAccount setWebsite(Mage_Core_Model_Website $value)
 */
class TIG_PostNL_Model_AddressValidation_System_Config_Backend_ValidateAccount extends Mage_Core_Model_Config_Data
{
    /**
     * Xpaths used to get PostNL account credentials.
     */
    const XPATH_MODE                = 'postnl/cif/mode';
    const XPATH_LIVE_USERNAME       = 'postnl/cif/live_username';
    const XPATH_LIVE_PASSWORD       = 'postnl/cif/live_password';
    const XPATH_TEST_USERNAME       = 'postnl/cif/test_username';
    const XPATH_TEST_PASSWORD       = 'postnl/cif/test_password';
    const XPATH_CUSTOMER_NUMBER     = 'postnl/cif/customer_number';
    const XPATH_CUSTOMER_CODE       = 'postnl/cif/customer_code';
    const XPATH_COLLECTION_LOCATION = 'postnl/cif/collection_location';

    /**
     * Gets the store ID based on the current store scope.
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasStoreId()) {
            return $this->getData('store_id');
        }

        $storeCode = $this->getStoreCode();
        $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();

        $this->setStoreId($storeId);
        return $storeId;
    }

    /**
     * Gets an instance of the current website scope.
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if ($this->hasWebsite()) {
            return $this->getData('website');
        }

        $websiteCode = $this->getWebsiteCode();
        $website = Mage::getModel('core/website')->load($websiteCode, 'code');

        $this->setWebsite($website);
        return $website;
    }

    /**
     * Check that PostNL account settings have been entered and are valid before saving this field.
     *
     * @throws TIG_PostNL_Exception
     *
     * @return Mage_Core_Model_Abstract
     *
     * @see Mage_Core_Model_Abstract::_beforeSave()
     */
    protected function _beforeSave()
    {
        /**
         * If the value has not changed, we don't have to do anything.
         */
        if (!$this->isValueChanged()) {
            return parent::_beforeSave();
        }

        /**
         * If the setting has been disabled we also don't have to do anything.
         */
        if (!$this->getValue()) {
            return parent::_beforeSave();
        }

        /**
         * Check whether the extension is set to test mode and get the username and password for that mode.
         */
        $testMode = $this->_getIsTestMode();
        if ($testMode) {
            $username = $this->_getConfigValue(self::XPATH_TEST_USERNAME);
            $password = $this->_getConfigValue(self::XPATH_TEST_PASSWORD);
        } else {
            $username = $this->_getConfigValue(self::XPATH_LIVE_USERNAME);
            $password = $this->_getConfigValue(self::XPATH_LIVE_PASSWORD);
        }

        /**
         * Get other PostNL account settings.
         */
        $customerNumber = $this->_getConfigValue(self::XPATH_CUSTOMER_NUMBER);
        $customerCode = $this->_getConfigValue(self::XPATH_CUSTOMER_CODE);
        $locationCode = $this->_getConfigValue(self::XPATH_COLLECTION_LOCATION);

        /**
         * Decrypt and then hash the password.
         */
        $password = trim($password);
        $password = sha1(Mage::helper('core')->decrypt($password));

        /**
         * Put all credentials into an array.
         */
        $data = array(
            'password'       => $password,
            'username'       => $username,
            'customerNumber' => $customerNumber,
            'customerCode'   => $customerCode,
            'locationCode'   => $locationCode,
        );

        /**
         * Load the CIF model and set to test mode to false.
         *
         * @var TIG_PostNL_Model_Core_Cif $cif
         */
        $cif = Mage::getModel('postnl_core/cif')
                   ->setTestMode($testMode);

        /**
         * Attempt to generate a barcode to test the account settings. This will result in an exception if the settings
         * are invalid.
         */
        try {
            $response = $cif->generateBarcodePing($data);
        } catch (Exception $e) {
            $errorMessage = $this->_getErrorMessage();

            throw new TIG_PostNL_Exception($errorMessage, 'POSTNL-0114');
        }

        /**
         * The result should be a barcode.
         */
        if (!is_string($response)) {
            $errorMessage = $this->_getErrorMessage();

            throw new TIG_PostNL_Exception($errorMessage, 'POSTNL-0114');
        }

        return parent::_beforeSave();
    }

    /**
     * gets whether the extension is set to test mode.
     *
     * @return boolean
     */
    protected function _getIsTestMode()
    {
        $cifTestMode = false;
        $cifMode = $this->_getConfigValue(self::XPATH_MODE);

        if ($cifMode !== '2') {
            $cifTestMode = true;
        }

        return $cifTestMode;
    }

    /**
     * Gets a config value. First we try to get the value from the fields we are currently trying to save. if the path
     * is not among the fields we're saving, get it from the database for the current scope.
     *
     * @param string $path An xpath to the setting we're trying to retrieve
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    protected function _getConfigValue($path)
    {
        $groups = $this->getGroups();
        $pathParts = explode('/', $path);
        if (count($pathParts) !== 3) {
            throw new InvalidArgumentException(
                'Invalid argument: $path should be a valid xpath.'
            );
        }

        /**
         * Check if the value is among the fields we're currently saving.
         *
         * In the case of password fields, the value '******' might be used. This is not a valid value and should be
         * ignored.
         */
        if (isset($groups[$pathParts[1]]['fields'][$pathParts[2]]['value'])
            && $groups[$pathParts[1]]['fields'][$pathParts[2]]['value'] != '******'
        ) {
            return $groups[$pathParts[1]]['fields'][$pathParts[2]]['value'];
        }

        /**
         * Get the value based on the current scope.
         */
        $scope = $this->getScope();
        switch ($scope) {
            case 'stores':
                $storeId = $this->getStoreId();
                $value = Mage::getStoreConfig($path, $storeId);
                break;
            case 'websites':
                $website = $this->getWebsite();
                $value = $website->getConfig($path);
                break;
            default:
                $value = Mage::getStoreConfig($path, Mage_Core_Model_App::ADMIN_STORE_ID);
                break;
        }

        return $value;
    }

    /**
     * Get the error message in case the PostNL account credentials could not be validated. Normally the controller
     * would catch an exception and parse it to create this message, however we have no control over the system_config
     * controller, so we need to format the message in advance.
     *
     * @return string
     */
    protected function _getErrorMessage()
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the error from the extension's config.xml
         */
        $error = Mage::getConfig()->getNode('tig/errors/POSTNL-0114');
        $link = (string) $error->url;

        /**
         * Form the base error message.
         */
        $errorMessage = '[PostNL-0114] ';
        $errorMessage .= $helper->__(
            'Your PostNL account credentials could not be validated.'
            . ' Please enter valid PostNL account credentials before activating the postcode check functionality.'
        );

        /**
         * Append a link to the TIG knowledgebase if available.
         */
        if ($link) {
            $errorMessage .= ' <a href="'
                           . $link
                           . '" target="_blank" class="postnl-message">'
                           . $helper->__('Click here for more information from the TIG knowledgebase.')
                           . '</a>';
        }

        return $errorMessage;
    }
}
