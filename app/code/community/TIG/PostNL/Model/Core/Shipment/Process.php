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
class TIG_PostNL_Model_Core_Shipment_Process extends Mage_Index_Model_Process
{
    /**
     * @var bool
     */
    protected $_own = false;

    /**
     * @var null|boolean
     */
    protected $_isLocked = null;

    /**
     * @var null|resource
     */
    protected $_lockFile = null;

    /**
     * Get lock file resource
     *
     * @return resource | TIG_PostNL_Model_Core_Shipment_Process
     */
    protected function _getLockFile()
    {
        if ($this->_lockFile) {
            return $this->_lockFile;
        }

        $varDir = Mage::getConfig()->getVarDir('locks');
        $file = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';

        if (is_file($file) && is_writable($file)) {
            if($this->_lockIsExpired()){
                unlink($file);//remove file
                $this->_lockFile = fopen($file, 'x');//create new lock file
            }else{
                $this->_lockFile = fopen($file, 'w');
            }
        } else {
            $this->_lockFile = fopen($file, 'x');
        }

        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        fwrite($this->_lockFile, date('r', $dateModel->gmtTimestamp()));

        return $this->_lockFile;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process running and fast lock validation.
     *
     * @return $this
     */
    public function lock()
    {
        $this->_isLocked = true;
        $this->_own = true;

        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);

        return $this;
    }

    /**
     * Lock and block process.
     *
     * @return $this
     */
    public function lockAndBlock()
    {
        $this->_isLocked = true;
        $this->_getLockFile();

        flock($this->_getLockFile(), LOCK_EX);

        return $this;
    }

    /**
     * Unlock process.
     *
     * @return $this
     */
    public function unlock()
    {
        $this->_isLocked = false;
        $this->_own = false;

        $file = $this->_getLockFile();

        flock($file, LOCK_UN);
        fclose($file);

        //remove lockfile
        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';
        @unlink($lockFile);

        $this->_lockFile = null;

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

        if ($this->_own === true) {
            $this->_isLocked = false;

            return false;
        }

        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';

        if (!is_file($lockFile)) {
            $this->_isLocked = false;

            return false;
        }

        //if the lock exists and exists for longer then 5minutes then remove lock & return false
        if($this->_lockIsExpired()){
            @unlink($lockFile);
            $this->_isLocked = false;

            return false;
        }

        $this->_isLocked = true;

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

        if (!is_file($file) || !is_readable($file)) {
            return false;
        }

        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        $fiveMinAgo = $dateModel->gmtTimestamp();

        $contents   = file_get_contents($file);
        $lockTime   = strtotime($contents);
        $lockExpiration = $lockTime + 300; //300s = 5min

        if($lockExpiration <= $fiveMinAgo){
            return true;
        }

        return false;
    }

    /**
     * Destroy the lock file if it still exists
     *
     * @return void
     */
    public function __destruct()
    {
        $varDir   = Mage::getConfig()->getVarDir('locks');
        $lockFile = $varDir . DS . 'postnl_process_' . $this->getId() . '.lock';

        if (is_file($lockFile)) {
            @unlink($lockFile);
        }

        return;
    }
}
