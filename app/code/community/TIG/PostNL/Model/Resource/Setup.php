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
class TIG_PostNL_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * callAfterApplyAllUpdates flag. Causes applyAFterUpdates() to be called.
     * 
     * @var boolean
     */
    protected $_callAfterApplyAllUpdates = true;
    
    protected $_dbVer;
    protected $_configVer;
    
    public function setDbVer($dbVer)
    {
        $this->_dbVer = $dbVer;
        
        return $this;
    }
    
    public function setConfigVer($configVer)
    {
        $this->_configVer = $configVer;
        
        return $this;
    }
    
    public function getDbVer()
    {
        return $this->_dbVer;
    }
    
    public function getConfigVer()
    {
        return $this->_configVer;
    }
    
    /**
     * Store the applied update versions
     * 
     * @return parent::applyUpdates()
     */
    public function applyUpdates()
    {
        $dbVer = $this->_getResource()->getDbVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;
        
        $this->setDbVer($dbVer);
        $this->setConfigVer($configVer);
        
        return parent::applyUpdates();
    }

    /**
     * Check if the PostNL module has been updated. If so, add an admin notification to the inbox.
     *
     * @return TIG_PostNL_Model_Resource_Setup
     * 
     * @todo prevent window from being shown after every login
     */
    public function afterApplyAllUpdates()
    {
        $dbVer = $this->getDbVer();
        $configVer = $this->getConfigVer();
        
        if (version_compare($configVer, $dbVer) != self::VERSION_COMPARE_GREATER) {
            return $this;
        }
        
        $helper = Mage::helper('postnl');
        
        $inbox = Mage::getModel('adminnotification/inbox');
        $inbox->addNotice(
                  $helper->__('PostNL module has been successfully updated to version %s.', $configVer),
                  $helper->__('PostNL module has been successfully updated to version %s.', $configVer),
                  '', 
                  true
              )
              ->save();
              
        return $this;
    }
}
