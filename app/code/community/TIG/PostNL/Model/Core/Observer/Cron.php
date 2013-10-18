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
     * 
     * @var string
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
         * Get all filed in the directory
         */
        $files = glob($tempLabelsDirectory . DS . '*');
        
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
     * @return TIG_PostNL_Exception
     */
    public function getBarcodes()
    {
        /**
         * Get all postnl shipments without a barcode
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter('barcode', array('null' => true));
        
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to generate a barcode. Continue with the next one if it fails.
             */
            try {
                $postnlShipment->generateBarcode()
                               ->addTrackingCodeToShipment()
                               ->save();
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
            }
        }
        
        return $this;
    }
}
