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
class TIG_PostNL_Model_Core_Observer_Cron
{
    /**
     * Xml path to maximum file storage setting in system/config
     */
    const XML_PATH_MAX_FILE_STORAGE  = 'postnl/advanced/max_temp_file_storage_time';

    /**
     * XML path to confirmation expire time setting
     */
    const XML_PATH_CONFIRM_EXPIRE_DAYS = 'postnl/advanced/confirm_expire_days';

    /**
     * XML path to setting that determines whether or not to send track and trace emails
     */
    const XML_PATH_SEND_TRACK_AND_TRACE_EMAIL = 'postnl/cif_labels_and_confirming/send_track_and_trace_email';

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
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('CleanTempLabels cron starting...');

        /**
         * Directory where all temporary labels are stored.
         * If this directory does not exist, end the script.
         */
        $tempLabelsDirectory = Mage::getConfig()->getVarDir('TIG' . DS . 'PostNL' . DS . 'temp_label');
        if (!is_dir($tempLabelsDirectory)) {
            $helper->cronLog('Temp labels directory not found. Exiting cron.');
            return $this;
        }

        /**
         * Check the maximum amount of time a temp file may be stored. By default this is 300s (5m).
         * If this settings is empty, end the script.
         */
        $maxFileStorageTime = (int) Mage::getStoreConfig(self::XML_PATH_MAX_FILE_STORAGE, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (empty($maxFileStorageTime)) {
            $helper->cronLog('No max file storage time defined. Exiting cron.');
            return $this;
        }

        /**
         * Get the temporary label filename constant. This is used to construct the fgilename together with
         * an md5 hash of the content and a timestamp.
         *
         * @var $labelModel TIG_PostNL_Model_Core_Label
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
            $helper->cronLog('Temporary label storage is unreadable. Exiting cron.');
            throw new TIG_PostNL_Exception(
                $helper->__('Unable to read directory: %s', $tempLabelsDirectory),
                'POSTNL-0096'
            );
        }

        $fileCount = count($files);
        if ($fileCount < 1) {
            $helper->cronLog('No temporary labels found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("{$fileCount} temporary labels found.");
        foreach ($files as $path) {
            /**
             * Get the name of the file. This should contain a timestamp after the first '-'
             */
            $filename = basename($path);
            $nameParts = explode('-', $filename);
            if (!isset($nameParts[1])) {
                $helper->cronLog("Invalid file found: {$filename}.");
                continue;
            }

            /**
             * Check if the timestamp is older than the maximum storage time
             */
            $time = $nameParts[1];
            if ((time() - $time) < $maxFileStorageTime) {
                $helper->cronLog("File {$filename} is less than {$maxFileStorageTime}s old. Continuing with the next file.");
                continue;
            }

            /**
             * Delete the file
             */
            $helper->cronLog("Deleting file: {$filename}.");
            unlink($path);
        }

        $helper->cronLog('CleanTempLabels cron has finished.');
        return $this;
    }

    /**
     * Method to destroy old lock files.
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     *
     * @throws TIG_PostNL_Exception
     */
    public function cleanOldLocks()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('CleanOldLocks cron starting...');

        /**
         * Directory where all lock files are stored
         */
        $locksDirectory = Mage::getConfig()->getVarDir('locks');
        if (!is_dir($locksDirectory)) {
            $helper->cronLog('Locks directory not found. Exiting cron.');
            return $this;
        }

        /**
         * Get all PostNL lock files in the directory
         */
        $files = glob($locksDirectory . DS . 'postnl_process_*');

        /**
         * If the directory cannot be read, throw an exception.
         */
        if ($files === false) {
            $helper->cronLog('Lock storage is unreadable. Exiting cron.');
            throw new TIG_PostNL_Exception(
                $helper->__('Unable to read directory: %s', $locksDirectory),
                'POSTNL-0096'
            );
        }

        $fileCount = count($files);
        if ($fileCount < 1) {
            $helper->cronLog('No PostNL locks found. Exiting cron.');
            return $this;
        }

        /**
         * Locks may only exist for 3600 seconds (1 hour) before being removed
         */
        $maxFileStorageTime = 3600;
        $now = Mage::getModel('core/date')->gmtTimestamp();
        $maxTimestamp = $now - $maxFileStorageTime; //1 hour ago

        $helper->cronLog("{$fileCount} locks found.");
        foreach ($files as $path) {
            if (!is_file($path)) {
                continue;
            }

            /**
             * First we must open and unlock the file
             */
            $file = fopen($path, 'r+');
            flock($file, LOCK_UN);
            fclose($file);

            /**
             * The file should contain a date
             */
            $timestamp = strtotime(file_get_contents($path));

            /**
             * If the file is more than 1 hour old, delete it
             */
            if ($timestamp < $maxTimestamp) {
                $helper->cronLog("Deleting file: {$path}.");
                @unlink($path);
            }
        }

        $helper->cronLog('CleanOldLocks cron has finished.');
        return $this;
    }

    /**
     * Retrieve barcodes for postnl shipments that do not have one.
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function getBarcodes()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('GetBarcodes cron starting...');

        /**
         * Get all postnl shipments without a barcode
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter('main_barcode', array('null' => true));

        if ($postnlShipmentCollection->getSize() < 1) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Getting barcodes for {$postnlShipmentCollection->getSize()} shipments.");

        $counter = 1000;
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Process a maximum of 1000 shipments (to prevent Cif from being overburdoned).
             * Only successfull requests count towards this number
             */
            if ($counter < 1) {
                break;
            }

            /**
             * Attempt to generate a barcode. Continue with the next one if it fails.
             */
            try {
                $helper->cronLog("Getting barcodes for shipment #{$postnlShipment->getId()}.");
                $postnlShipment->generateBarcodes()
                               ->save();

                $counter--;
            } catch (Exception $e) {
                Mage::helper('postnl')->logException($e);
            }
        }

        $helper->cronLog('GetBarcodes cron has finished.');

        return $this;
    }

    /**
     * Update shipping status for all confirmed, but undelivered shipments.
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function updateShippingStatus()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('UpdateShippingStatus cron starting...');

        /**
         * @var $postnlShipmentModelClass TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $deliveredStatus = $postnlShipmentModelClass::SHIPPING_PHASE_DELIVERED;

        /**
         * Get all postnl shipments with a barcode, that are confirmed and are not yet delivered.
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter(
                                     'main_barcode',
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

        if ($postnlShipmentCollection->getSize() < 1) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Shipping status will be updated for {$postnlShipmentCollection->getSize()} shipments.");

        /**
         * Request a shipping status update
         */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to update the shipping status. Continue with the next one if it fails.
             */
            try{
                if (!$postnlShipment->getShipment()) {
                    continue;
                }

                $helper->cronLog("Updating shipping status for shipment #{$postnlShipment->getShipment()->getId()}");

                if (!$postnlShipment->canUpdateShippingStatus()) {
                    $postnlShipment->unlock();
                    $helper->cronLog("Updating shipment #{$postnlShipment->getShipment()->getId()} is not allowed. Continuing with next shipment.");
                    continue;
                }

                $postnlShipment->updateShippingStatus()
                               ->save();
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                $postnlShipment->unlock();

                $this->_parseErrorCodes($e, $postnlShipment);
            } catch (Exception $e) {
                $postnlShipment->unlock();

                Mage::helper('postnl')->logException($e);
            }
        }

        $helper->cronLog('UpdateShippingStatus cron has finished.');

        return $this;
    }

    /**
     * Parses an TIG_PostNL_Model_Core_Cif_Exception exception in order to process cpecific error codes
     *
     * @param TIG_PostNL_Model_Core_Cif_Exception $e
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    protected function _parseErrorCodes($e, $postnlShipment)
    {
        $helper = Mage::helper('postnl');

        /**
         * Certain error numbers are processed differently
         */
        $errorNumbers = $e->getErrorNumbers();

        if (!$errorNumbers) {
            $helper->logException($e);
            return $this;
        }

        foreach ($errorNumbers as $errorNumber) {
            if ($errorNumber != '13') { // Collo not found error
                $helper->logException($e);
                return $this;
            }

            /**
             * If the shipment's shipping phase has already been set to 'shipment not found' there is no need to proceed
             */
            if ($postnlShipment->getShippingPhase() == $postnlShipment::SHIPPING_PHASE_NOT_APPLICABLE) {
                return $this;
            }

            /**
             * Check if the shipment was confirmed more than a day ago
             */
            $confirmedAt = strtotime($postnlShipment->getConfirmedAt());
            $now = Mage::getModel('core/date')->gmtTimestamp();
            $yesterday = strtotime('-1 day', $now);

            if ($confirmedAt > $yesterday) {
                return $this;
            }

            /**
             * Set 'shipment not found' status
             */
            $helper->cronLog(
                "Shipment #{$postnlShipment->getId()} could not be found by CIF and was confirmed more than 1 day ago!"
            );
            $postnlShipment->setShippingPhase($postnlShipment::SHIPPING_PHASE_NOT_APPLICABLE)
                           ->save();

            return $this;
        }

        $helper->logException($e);
        return $this;
    }

    /**
     * Removes expired confirmations by resetting the postnl shipment to a pre-confirm state
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function expireConfirmation()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('ExpireConfirmation cron starting...');

        /**
         * @var $postnlShipmentModelClass TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $collectionPhase = $postnlShipmentModelClass::SHIPPING_PHASE_COLLECTION;

        $confirmationExpireDays = Mage::getStoreConfig(self::XML_PATH_CONFIRM_EXPIRE_DAYS, Mage_Core_Model_App::ADMIN_STORE_ID);
        $expireTimestamp = strtotime("-{$confirmationExpireDays} days", Mage::getModel('core/date')->gmtTimestamp());
        $expireDate = date('Y-m-d H:i:s', $expireTimestamp);

        $helper->cronLog("All confirmation placed before {$expireDate} will be expired.");

        /**
         * Get all postnl shipments that have been confirmed over X days ago and who have not yet been shipped (shipping_phase
         * other than 'collection')
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter(
                                     'confirm_status',
                                     array('eq' => $confirmedStatus)
                                 )
                                 ->addFieldToFilter(
                                     'shipping_phase',
                                     array(
                                         array('eq' => $collectionPhase),
                                         array('null' => true)
                                     )
                                 )
                                 ->addFieldToFilter(
                                     'confirmed_at',
                                     array(
                                         array('lt' => $expireDate),
                                         array('null' => true)
                                     )
                                 );

        /**
         * Check to see if there are any results
         */
        if (!$postnlShipmentCollection->getSize()) {
            $helper->cronLog('No expired confirmations found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Number of expired confirmations found: {$postnlShipmentCollection->getSize()}");

        /**
         * Reset the shipments to 'unconfirmed' status
         */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to reset the shipment to a pre-confirmed status
             */
            try{
                $helper->cronLog("Expiring confirmation of shipment #{$postnlShipment->getId()}");
                $postnlShipment->resetConfirmation()
                               ->setConfirmStatus($postnlShipment::CONFIRM_STATUS_CONFIRM_EXPIRED)
                               ->generateBarcodes() //generate new barcodes as the current ones have expired
                               ->save();
            } catch (Exception $e) {
                $helper->logException($e);
            }
        }
        $helper->cronLog('ExpireConfirmation cron has finished.');

        return $this;
    }

    /**
     * Send a track & trace e-mail to the customer
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function sendTrackAndTraceEmail()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('SendTrackAndTraceEmail cron starting...');

        /**
         * Check each storeview if sending track & trace emails is allowed
         */
        $allowedStoreIds = array();
        foreach (array_keys(Mage::app()->getStores()) as $storeId) {
            if (Mage::getStoreConfig(self::XML_PATH_SEND_TRACK_AND_TRACE_EMAIL, $storeId)) {
                $allowedStoreIds[] = $storeId;
            }
        }

        if (empty($allowedStoreIds)) {
            $helper->cronLog('Sending track & trace emails is disabled in all stores. Exiting cron.');
            return $this;
        }

        /**
         * @var $postnlShipmentModelClass TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;

        $twentyMinutesAgo = strtotime("-20 minutes", Mage::getModel('core/date')->gmtTimestamp());
        $twentyMinutesAgo = date('Y-m-d H:i:s', $twentyMinutesAgo);

        $helper->cronLog("Track and trace email will be sent for all shipments that were confirmed on or before {$twentyMinutesAgo}.");

        /**
         * Get all postnl shipments that have been confirmed over 20 minutes ago whose track & trace e-mail has not yet been sent
         *
         * Resulting SQL:
         * SELECT `main_table` . *
         * FROM `tig_postnl_shipment` AS `main_table`
         * WHERE (
         *     confirm_status = '{$confirmedStatus}'
         * )
         * AND (
         *     labels_printed =1
         * )
         * AND (
         *     confirmed_at <= '{$twentyMinutesAgo}'
         * )
         * AND (
         *     (
         *         (
         *             track_and_trace_email_sent IS NULL
         *         )
         *         OR (
         *             track_and_trace_email_sent = '0'
         *         )
         *     )
         * )
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter(
                                     'confirm_status',
                                     array('eq' => $confirmedStatus)
                                 )
                                 ->addFieldToFilter(
                                     'labels_printed',
                                     array('eq' => 1)
                                 )
                                 ->addFieldToFilter(
                                     'confirmed_at',
                                     array('lteq' => $twentyMinutesAgo)
                                 )
                                 ->addFieldToFilter(
                                    'track_and_trace_email_sent',
                                    array(
                                        array('null' => true),
                                        array('eq' => '0')
                                    )
                                 );

        /**
         * Check to see if there are any results
         */
        if (!$postnlShipmentCollection->getSize()) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Track & trace emails will be sent for {$postnlShipmentCollection->getSize()} shipments.");

        /**
         * Send the track and trace email for all shipments
         */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Check if sending the email is allowed for this shipment
             */
            $storeId = $postnlShipment->getStoreId();
            if (!in_array($storeId, $allowedStoreIds) || !$postnlShipment->canSendTrackAndTraceEmail()) {
                $helper->cronLog("Sending the track and trace email is not allowed for shipment #{$postnlShipment->getId()}.");
                continue;
            }

            /**
             * Attempt to send the email
             */
            try{
                $helper->cronLog("Sending track and trace email for shipment #{$postnlShipment->getId()}");
                $postnlShipment->sendTrackAndTraceEmail()
                               ->setTrackAndTraceEmailSent(true)
                               ->save();
            } catch (Exception $e) {
                $helper->logException($e);
            }
        }
        $helper->cronLog('SendTrackAndTraceEmail cron has finished.');

        return $this;
    }

    /**
     * Deletes labels belonging to shipments that have been delievered as well as labels who have no associated shipments.
     *
     * @return TIG_PostNL_Model_Core_Observer_Cron
     */
    public function removeOldLabels()
    {
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('RemoveOldLabels cron starting...');

        /**
         * Get the PostNL Shipment classname for later use
         *
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        /**
         * Get the label collection
         */
        $labelsCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');

        /**
         * We only need the label IDs
         */
        $labelsCollection->addFieldToSelect('label_id');

        $resource = Mage::getSingleton('core/resource');

        $select = $labelsCollection->getSelect();

        /**
         * Join the collection with the postnl shipments collection
         */
        $select->joinLeft(
            array('postnl_shipment' => $resource->getTableName('postnl_core/shipment')),
            '`main_table`.`parent_id`=`postnl_shipment`.`entity_id`',
            array(
                'shipping_phase' => 'postnl_shipment.shipping_phase',
            )
        );

        /**
         * Filter the collection by the lack of a parent_id OR shipping_phase being 'delivered'
         *
         * Resulting query:
         * SELECT `main_table`.`label_id` , `postnl_shipment`.`shipping_phase`
         * FROM `tig_postnl_shipment_label` AS `main_table`
         * LEFT JOIN `tig_postnl_shipment` AS `postnl_shipment` ON `main_table`.`parent_id` = `postnl_shipment`.`entity_id`
         * WHERE (
         *     (
         *         parent_id IS NULL
         *     )
         *     OR (
         *         shipping_phase =4
         *     )
         * )
         */
        $labelsCollection->addFieldToFilter(
            array('parent_id', 'shipping_phase'),
            array(
                array('null' => true),
                array('eq' => $postnlShipmentClass::SHIPPING_PHASE_DELIVERED),
            )
        );

        $labelCollectionSize = $labelsCollection->getSize();
        if ($labelCollectionSize < 1) {
            $helper->cronLog('No labels need to be removed. Exiting cron.');
            return $this;
        }

        $helper->cronLog("{$labelCollectionSize} labels will be removed.");

        /**
         * Delete the labels
         */
        foreach ($labelsCollection as $label) {
            $helper->cronLog("Deleting label #{$label->getId()}.");
            $label->delete();
        }
        $helper->cronLog('RemoveOldLabels cron has finished.');

        return $this;
    }
}