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
class TIG_PostNL_Model_Core_Shipment_Process extends Mage_Index_Model_Process
{
    protected $_isLocked = null;
    
    /**
     * Get lock file resource
     *
     * @return resource | TIG_Buckaroo3Extended_Model_Process
     */
    protected function _getLockFile()
    {
        if ($this->_lockFile !== null) {
            return $this->_lockFile;
        }
        
        $varDir = Mage::getConfig()->getVarDir('locks');
        $file = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';
        
        if (is_file($file)) {
            if($this->_lockIsExpired()){
                unlink($file);//remove file 
                $this->_lockFile = fopen($file, 'x');//create new lock file
            }else{
                $this->_lockFile = fopen($file, 'w');
            }
        } else {
            $this->_lockFile = fopen($file, 'x');
        }
        
        fwrite($this->_lockFile, date('r'));
        
        return $this->_lockFile;
    }
    
    /**
     * Lock process without blocking.
     * This method allow protect multiple process running and fast lock validation.
     *
     * @return TIG_Buckaroo3Extended_Model_Process
     */
    public function lock()
    {
        $this->_isLocked = true;
        
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);

        return $this;
    }
    
    /**
     * Lock and block process
     * 
     * @return TIG_Buckaroo3Extended_Model_Process
     */
    public function lockAndBlock()
    {
        $this->_isLocked = true;
        $file = $this->_getLockFile();
        
        flock($this->_getLockFile(), LOCK_EX);
        
        return $this;
    }
    
    /**
     * Unlock process
     *
     * @return TIG_Buckaroo3Extended_Model_Process
     */
    public function unlock()
    {
        $this->_isLocked = false;
        $file = $this->_getLockFile();
        
        flock($file, LOCK_UN);
        fclose($file); 
        
        //remove lockfile
        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';
        @unlink($lockFile);
        
        return $this;
    }

    /**
     * Check if process is locked
     *
     * @return bool
     */
    public function isLocked()
    {
        if ($this->_isLocked !== null) {
            return $this->_isLocked;
        }
        
        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';
        
        if (!is_file($lockFile)) {
            return false;
        }
        
        //if the lock exists and exists for longer then 5minutes then remove lock & return false
        if($this->_lockIsExpired()){
            @unlink($lockFile);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Checks if the lock has expired
     * 
     * @return bool
     */
    protected function _lockIsExpired(){
        $varDir     = Mage::getConfig()->getVarDir('locks');
        $file       = $varDir . DS . 'postnl_process_'.$this->getId().'.lock';
        
        if(!is_file($file)){
            return false;
        }
        
        
        $fiveMinAgo = time() - 300;//300
        $contents   = file_get_contents($file);
        $time       = strtotime($contents);
        
        if($time <= $fiveMinAgo){
            return true;
        }
        
        return false;
    }
    
    public function __desctruct()
    {
        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';
        
        if (is_file($lockFile)) {
            @unlink($lockFile);
        }
    }
}