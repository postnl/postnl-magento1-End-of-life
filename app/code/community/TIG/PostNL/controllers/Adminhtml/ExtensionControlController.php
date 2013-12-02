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
     * Activates the webshop. Uses the latest entered email. Does not save any other fields.
     * 
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    public function activateAction()
    {
        $groups = $this->getRequest()->getParam('groups');
        
        /**
         * Get the last email address entered if available. Immediately save it as well.
         */
        $email = false;
        if (isset($groups['general']['fields']['email']['value'])) {
            $email = $groups['general']['fields']['email']['value'];
            Mage::getModel('core/config')->saveConfig(self::XML_PATH_EMAIL, $email);
        }
        
        $webservice = Mage::getModel('postnl_extensioncontrol/webservices');
        
        try {
            /**
             * Activate the webshop
             */
            $webservice->activateWebshop($email);
        } catch (Exception $e) {
            /**
             * The most common cause of ran exception here is that the email address used is already known. In this case we can
             * immediately proceed to step 2 of the activation process.
             */
            Mage::helper('postnl')->logException($e);
            try {
                /**
                 * The first update statistics call will, when it succeeds, cause the module to be fully activated.
                 */
                $this->_updateStatistics();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('postnl')->__('The extension has been successfully activated.')
                );
            } catch (Exception $e) {
                /**
                 * If the update statistics also fails, some different error has occurred.
                 */
                Mage::helper('postnl')->logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('postnl')->__('An error occurred while activating the extension.')
                );
            }
            
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }
        
        /**
         * Set the activation status to the next level and inform the merchant.
         */
        Mage::getModel('core/config')->saveConfig(self::XML_PATH_IS_ACTIVATED, 1);
        $successMessage = 'This website has been actived. An email has been sent to the specified e-mail address. Please'
                        . ' read this email carefully as it contains important information regarding the activation of'
                        . ' the extension.';
                        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('postnl')->__($successMessage)
        );
        
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
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
                
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
        return $this;
    }

    /**
     * Attempts to update this shop's statistics in order to fully activate
     * 
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    protected function _updateStatistics()
    {   
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        
        $uniqueKey  = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_UNIQUE_KEY, $adminStoreId);
        $privateKey = Mage::getStoreConfig(self::XML_PATH_EXTENSIONCONTROL_PRIVATE_KEY, $adminStoreId);
        
        if (!$uniqueKey || !$privateKey) {
            return $this;
        }
        
        $webservices = Mage::getModel('postnl_extensioncontrol/webservices');
        $webservices->updateStatistics();
        
        return $this;
    }

}