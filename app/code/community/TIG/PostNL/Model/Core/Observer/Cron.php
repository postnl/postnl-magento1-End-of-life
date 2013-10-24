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
class TIG_PostNL_Model_Core_Observer_Cron
{
    /**
     * Xml path to maximum file storage setting in system/config
     */
    const XML_PATH_MAX_FILE_STORAGE  = 'postnl/advanced/max_temp_file_storage_time';
    
    /**
     * Method to destroy temporary label files that have been stored for too long.
     * 
     * By default the PostNL module creates temporary label files in order to merge them into
     * a single shipping label. These files are then destroyed. However, sometimes these files
     * may survive the script if the script has encountered an error. This method will make
     * sure these files will not survive indefinitiely, which may lead to the file system
     * being overburdoned or the server running out of harddrive space.
     * 
     * @return TIG_PostNL_Model_Core_Observer_Cron
     * 
     * @throws TIG_PostNL_Exception
     */
    public function cleanTempLabels()
    {
        /**
         * Check if the PostNL module is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        /**
         * Directory where all temporary labels are stored. 
         * If this directory does not exist, end the script.
         */
        $tempLabelsDirectory = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label');
        if (!is_dir($tempLabelsDirectory)) {
            return $this;
        }
        
        /**
         * Check the maximum amount of time a temp file may be stored. By default this is 300s (5m).
         * If this settings is empty, end the script.
         */
        $maxFileStorageTime = (int) Mage::getStoreConfig(self::XML_PATH_MAX_FILE_STORAGE, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (empty($maxFileStorageTime)) {
            return $this;
        }
        
        /**
         * Get the temporary label filename constant. This is used to construct the fgilename together with
         * an md5 hash of the content and a timestamp.
         */
        $labelModel = Mage::app()->getConfig()->getModelClassName('postnl_core/label');
        $tempLabelName = $labelModel::TEMP_LABEL_FILENAME;
        
        /**
         * Get all temporary label files in the directory
         */
        $files = glob($tempLabelsDirectory . DS . '*' . $tempLabelName);
        
        /**
         * If the directory cannot be read, throw an exception.
         */
        if ($files === false) {
            throw Mage::exception('TIG_PostNL', 'Unable to read directory: ' . $tempLabelsDirectory);
        }
        
        foreach ($files as $path) {
            /**
             * Get the name of the file. This should contain a timestamp after the first '-'
             */
            $filename = basename($path);
            $nameParts = explode('-', $filename);
            if (!isset($nameParts[1])) {
                continue;
            }
            
            /**
             * Check if the timestamp is older than the maximum storage time
             */
            $time = $nameParts[1];
            if ((time() - $time) < $maxFileStorageTime) {
                continue;
            }
            
            /**
             * Delete the file
             */
            unlink($path);
        }
        
        return $this;
    }

    /**
     * Retrieve barcodes for postnl shipments that do not have one.
     * 
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function getBarcodes()
    {
        /**
         * Check if the PostNL module is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        /**
         * Get all postnl shipments without a barcode
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter('barcode', array('null' => true));
        
        $n = 1000;
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Process a maximum of 1000 shipments (to prevent Cif from being overburdoned).
             * Only successfull requests count towards this number
             */
            if ($n < 1) {
                break;
            }
            
            /**
             * Attempt to generate a barcode. Continue with the next one if it fails.
             */
            try {
                $postnlShipment->generateBarcode()
                               ->addTrackingCodeToShipment()
                               ->save();
                               
                $n--;
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
            }
        }
        
        return $this;
    }
    
    /**
     * Update shipping status for all confirmed, but undelivered shipments.
     * 
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function updateShippingStatus()
    {
        /**
         * Check if the PostNL module is active
         */
        if (!Mage::helper('postnl')->isEnabled()) {
            return $this;
        }
        
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $deliveredStatus = $postnlShipmentModelClass::SHIPPING_PHASE_DELIVERED;
        
        /**
         * Get all postnl shipments with a barcode, that are confirmed and are not yet delivered.
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter(
                                     'barcode', 
                                     array('notnull' => true)
                                 )
                                 ->addFieldToFilter(
                                     'confirm_status', 
                                     array('eq' => $confirmedStatus)
                                 )
                                 ->addFieldToFilter(
                                     'shipping_phase', 
                                     array(
                                         array('neq' => $deliveredStatus), 
                                         array('null' => true)
                                     )
                                 );
        
        /**
         * Request a shipping status update
         */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to update the shipping status. Continue with the next one if it fails.
             */
            try{
                $postnlShipment->updateShippingStatus()
                               ->save();
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
            }
        }
        
        return $this;
    }
}
