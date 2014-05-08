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
class TIG_PostNL_Adminhtml_ExtensionControlController extends Mage_Adminhtml_Controller_Action
{
    /**
     * XML path to extensioncontrol email setting
     */
    const XML_PATH_EMAIL = 'postnl/general/email';

    /**
     * XML path to 'is_activated' flag
     */
    const XML_PATH_IS_ACTIVATED = 'postnl/general/is_activated';

    /**
     * XML paths for security keys
     */
    const XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY  = 'postnl/general/unique_key';
    const XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY = 'postnl/general/private_key';

    /**
     * XML path for active setting
     */
    const XML_PATH_ACTIVE = 'postnl/general/active';

    /**
     * Error code for 'website already exists' error
     */
    const SHOP_ALREADY_REGISTERED_FAULTCODE = 'API-2-6';

    /**
     * Activate the extension by registering it with the extension control service
     *
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    public function activateAction()
    {
        $activationStatus = Mage::getStoreConfig(self::XML_PATH_IS_ACTIVATED, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$activationStatus) {
            $this->_registerWebshop();
        } elseif ($activationStatus == 1) {
            $this->_updateStatistics();
        }

        Mage::helper('postnl')->saveConfigState(array('postnl_general' => 1));

        Mage::app()->cleanCache();

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
        return $this;
    }

    /**
     * Registers a new webshop
     *
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    protected function _registerWebshop()
    {
        $groups = $this->getRequest()->getParam('groups');

        /**
         * Get the last email address entered if available. Immediately save it as well.
         */
        $email = false;
        if (isset($groups['general']['fields']['email']['value'])) {
            $email = $groups['general']['fields']['email']['value'];
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_EMAIL, $email);

            /**
             * reinit configuration
             */
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
        }

        $helper = Mage::helper('postnl');
        $webservice = Mage::getModel('postnl_extensioncontrol/webservices');
        try {
            /**
             * Activate the webshop
             */
            $webservice->activateWebshop($email);
        } catch (SoapFault $e) {
            /**
             * If the webshop is already registered (email, hostname combo exists), continue the activation by sendinging a
             * signle update statistics request.
             */
            if (isset($e->faultcode) && $e->faultcode == self::SHOP_ALREADY_REGISTERED_FAULTCODE) {
                Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 1);

                return $this->_updateStatistics();
            }

            $helper = Mage::helper('postnl');
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);
            return $this;
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            return $this;
        }

        Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 1);


        $helper->addSessionMessage('adminhtml/session', null, 'success',
            $this->__(
                'Your webshop has been registered. Within a few minutes you will recieve an email at the emailaddress you specified. Please read this email carefully as it contains instructions on how to finish the extension activation procedure.'
            )
        );

        return $this;
    }

    /**
     * Activates the webshop by attempting a single updateStatistics call
     *
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    protected function _updateStatistics()
    {
        $groups = $this->getRequest()->getParam('groups');

        /**
         * If either the unique key or the private key were just entered without saving the config first, we need to encrypt and
         * save them.
         */
        $configChanged = false;
        if (isset($groups['general']['fields'])) {
            /**
             * Get the general fields array
             */
            $generalFields = $groups['general']['fields'];

            /**
             * Check if the 'active' option was set and is a valid value (not empty)
             */
            if (isset($generalFields['active']['value'])) {
                $active = $generalFields['active']['value'];
                if (!empty($active)) {
                    Mage::getModel('core/config')->saveConfig(self::XML_PATH_ACTIVE, $active);

                    $configChanged = true;
                }
            }

            /**
             * Check if the unique key was set and is a valid value (not empty and not just asterisks)
             */
            if (isset($generalFields['unique_key']['value'])) {
                $uniqueKey = $generalFields['unique_key']['value'];
                if (!empty($uniqueKey) && !preg_match('/^\*+$/', $uniqueKey)) {
                    /**
                     * Encrypt and save the unique key
                     */
                    $encryptedUniqueKey = Mage::helper('postnl/webservices')->encryptValue($uniqueKey);
                    Mage::getModel('core/config')->saveConfig(self::XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY, $encryptedUniqueKey);

                    $configChanged = true;
                }
            }

            /**
             * Do the same for the private key
             */
            if (isset($generalFields['private_key']['value'])) {
                $privateKey = $generalFields['private_key']['value'];
                if (!empty($privateKey) && !preg_match('/^\*+$/', $privateKey)) {
                    /**
                     * Encrypt and save the private key
                     */
                    $encryptedPrivateKey = Mage::helper('postnl/webservices')->encryptValue($privateKey);
                    Mage::getModel('core/config')->saveConfig(self::XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY, $encryptedPrivateKey);

                    $configChanged = true;
                }
            }
        }

        /**
         * If the config has changed we need to reinit
         */
        if ($configChanged) {
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
        }

        /**
         * If either the unique or private key was not saved, get it from the config
         */
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        if (!isset($uniqueKey)) {
            $uniqueKey = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY, $adminStoreId);
        }

        if (!isset($privateKey)) {
            $privateKey = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY, $adminStoreId);
        }

        $helper = Mage::helper('postnl');

        if (!$uniqueKey || !$privateKey) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0008', 'notice',
                $this->__('Please fill in your unique and private keys and try again.')
            );
            return $this;
        }

        /**
         * Try to update the shop's statistics once in order to fully activate the extension
         */
        try {
            $webservices = Mage::getModel('postnl_extensioncontrol/webservices');
            $webservices->updateStatistics(true);
        } catch (Exception $e) {
            $helper->logException($e);
            $helper->addExceptionSessionMessage('adminhtml/session', $e);

            return $this;
        }

        Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 2);

        $helper->addSessionMessage('adminhtml/session', null, 'success',
            $this->__('The extension has been successfully activated!')
        );

        return $this;
    }

    /**
     * Deactivates the module so it can be reactivated under a different name. It will reactivate itself automatically if not
     * settings are altered.
     *
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    public function showActivationFieldsAction()
    {
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 0);

        Mage::helper('postnl')->saveConfigState(array('postnl_general' => 1));

        Mage::app()->cleanCache();

        $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
        return $this;
    }
}