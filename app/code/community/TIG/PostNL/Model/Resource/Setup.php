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
     * Cron expression and cron model definitions for shipping_status cron
     */
    const SHIPPING_STATUS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_shipping_status/schedule/cron_expr';
    const SHIPPING_STATUS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_shipping_status/run/model';
    
    /**
     * Cron expression and cron model definitions for statistics update cron
     */
    const UPDATE_STATISTICS_CRON_STRING_PATH = 'crontab/jobs/postnl_update_statistics/schedule/cron_expr';
    const UPDATE_STATISTICS_CRON_MODEL_PATH  = 'crontab/jobs/postnl_update_statistics/run/model';
    
    /**
     * XML path to the supporttab_expanded setting
     */
    const EXPAND_SUPPORT_PATH = 'postnl/support/expanded';
    
    /**
     * CMS block identifier for PostNL Checkout instructions CMS block
     */
    const POSTNL_CHECKOUT_INSTRUCTIONS_CMS_BLOCK_IDENTIFIER = 'postnl_checkout_instructions';
    
    /**
     * callAfterApplyAllUpdates flag. Causes applyAFterUpdates() to be called.
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
     * Set the stored DB version to the specified value
     * 
     * @param string $dbVer
     * 
     * @return TIG_PostNL_Model_Resource_Setup
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
     * @return TIG_PostNL_Model_Resource_Setup
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
        
        $this->_checkVersionCompatibility();
        
        if (version_compare($configVer, $dbVer) != self::VERSION_COMPARE_GREATER) {
            return $this;
        }
        
        $helper = Mage::helper('postnl');
        
        $inbox = Mage::getModel('postnl/inbox');
        $inbox->addNotice(
                  '[POSTNL-0083-N] ' . $helper->__('PostNL extension has been successfully updated to version %s.', $configVer),
                  '[POSTNL-0083-N] ' . $helper->__('PostNL extension has been successfully updated to version %s.', $configVer),
                  'http://servicedesk.totalinternetgroup.nl/entries/31921907', 
                  true
              )
              ->save();
              
        return $this;
    }
    
    /**
     * generate a random cron expression for the status update cron for this merchant and store it in the database
     * 
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function generateShippingStatusCronExpr()
    {
        /**
         * Generate random values for the cron expression
         */
        $cronMorningHour   = mt_rand(10, 12);
        $cronAfternoonHour = $cronMorningHour + 4; //4 hours after the morning update
        $cronMinute        = mt_rand(0, 59);
        
        /**
         * Generate a cron expr that runs on a specified minute on a specified hour between 10 and 12 AM, and between 14 and 16 PM.
         */
        $cronExpr = "{$cronMinute} {$cronMorningHour},{$cronAfternoonHour} * * *";
        
        /**
         * Store the cron expression in core_config_data
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
                'POSTNL-0084',
                $e
            );
        }
        
        return $this;
    }
    
    /**
     * Generates a semi-random cron expression for the update statistics cron. This is done to spread out the number of calls
     * across each day.
     * 
     * @return TIG_PostNL_Model_Resource_Setup
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
                'POSTNL-0085',
                $e
            );
        }
        
        return $this;
    }
    
    /**
     * Adds a CMS block containing instructions for the customer on how to use PostNL Checkout.
     * 
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function addPostnlCheckoutInstructions()
    {
        $currentStore = Mage::app()->getStore()->getId();
        $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        
        Mage::app()->setCurrentStore($adminStoreId);
        
        $instructionsBlock = Mage::getModel('cms/block')
                                 ->load(self::POSTNL_CHECKOUT_INSTRUCTIONS_CMS_BLOCK_IDENTIFIER);
        
        $stores = array_keys(Mage::app()->getStores());
        
        $parameters = array(
            'title'      => 'PostNL Checkout instructions',
            'identifier' => self::POSTNL_CHECKOUT_INSTRUCTIONS_CMS_BLOCK_IDENTIFIER,
            'content'    => $this->getPostnlCheckoutInstructionsContent(),
            'is_active'  => 1,
            'stores'     => $stores,
        );
        
        $informationRequirement->setData($parameters)
                               ->save();
                               
        Mage::app()->setCurrentStore($currentStore);
        return $this;
    }

    /**
     * Gets the content of the PostNL Checkout instructions CMS block
     * 
     * @return string
     * 
     * @todo add the actual content
     */
    public function getPostnlCheckoutInstructionsContent()
    {
        $content = "";
        
        return $content;
    }

    /**
     * Checks the store's config to see if the extension is compatible with the installed Magento version. If not, a message will
     * be added to Mage_Adminnotification.
     * 
     * @return TIG_PostNL_Model_Resource_Setup
     */
    public function _checkVersionCompatibility()
    {
        $helper = Mage::helper('postnl');
        if ($helper->isEnterprise()) {
            $edition = 'enterprise';
        } else {
            $edition = 'community';
        }
        
        $inbox = Mage::getModel('postnl/inbox');
        
        $supportedVersions = Mage::getConfig()->getNode('tig/compatibility/postnl/' . $edition);
        if ($supportedVersions === false) {
            $message = '[POSTNL-0086-W] ' 
                     . $helper->__(
                           'The PostNL extension is not compatible with your Magento version! This may cause unexpected behaviour.'
                       );
                       
            $inbox->addCritical(
                      $message,
                      $message,
                      'http://servicedesk.totalinternetgroup.nl/entries/31925577', 
                      true
                  )
                  ->save();
                  
            return $this;
        }
        
        $supportedVersions = (string) $supportedVersions;
        $supportedVersionArray = explode(',', $supportedVersions);
        
        $installedMagentoVersionInfo = Mage::getVersionInfo();
        $installedMagentoVersion = $installedMagentoVersionInfo['major'] . '.' . $installedMagentoVersionInfo['minor'];
        
        if (!in_array($installedMagentoVersion, $supportedVersionArray)) {
            $message = '[POSTNL-0086-W] ' 
                     . $helper->__(
                           'The PostNL extension is not compatible with your Magento version! This may cause unexpected behaviour.'
                       );
                       
            $inbox->addCritical(
                      $message,
                      $message,
                      'http://servicedesk.totalinternetgroup.nl/entries/31925577',
                      true
                  )
                  ->save();
                  
            return $this;
        }
        
        return $this;
    }
}
