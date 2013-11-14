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
     * Activates the webshop
     * 
     * @return TIG_PostNL_Adminhtml_ExtensionControlController
     */
    public function activateAction()
    {
        $websiteCode = $this->getRequest()->getParam('website');
        
        $webservice = Mage::getModel('postnl_extensioncontrol/webservices');
        if (!$websiteCode) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('postnl')->__('Please activate the extension from the website level in system > config.')
            );
            
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }
        
        $website = Mage::getModel('core/website')->load($websiteCode, 'code');
        
        try {
            $webservice->activateWebshop($website->getId());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('postnl')->__('An error occurred while activating the extension: ' . $e->getMessage())
            );
            
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl', 'website' => $websiteCode));
            return $this;
        }
        
        $successMessage = 'This website has been actived. An email has been sent to the specified e-mail address. Please'
                        . ' read this email carefully as it contains important information regarding the activation of'
                        . ' the extension.';
                        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('postnl')->__($successMessage)
        );
        
        $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl', 'website' => $websiteCode));
        return $this;
    }
}