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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Core_Observer_Cron
{
    /**
     * Xml path to maximum file storage setting in system/config
     */
    const XPATH_MAX_FILE_STORAGE  = 'postnl/advanced/max_temp_file_storage_time';

    /**
     * XML path to confirmation expire time setting
     */
    const XPATH_CONFIRM_EXPIRE_DAYS = 'postnl/advanced/confirm_expire_days';

    /**
     * XML path to setting that determines whether or not to send track and trace emails
     */
    const XPATH_SEND_TRACK_AND_TRACE_EMAIL = 'postnl/track_and_trace/send_track_and_trace_email';

    /**
     * Xpath to the product attribute update data used by the product attribute update cron.
     */
    const XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA = 'postnl/general/product_attribute_update_data';

    /**
     * Xpath to the return_expire_days setting.
     */
    const XPATH_RETURN_EXPIRE_DAYS = 'postnl/advanced/return_expire_days';

    /**
     * Cron expression definition for updating product attributes.
     */
    const UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH = 'crontab/jobs/postnl_update_product_attribute/schedule/cron_expr';

    /**
     * Maximum number of products to update per cron run.
     */
    const MAX_PRODUCTS_TO_UPDATE = 250;

    /**
     * @var array
     */
    protected $_timeZones = array();

    /**
     * @return array
     */
    public function getTimeZones()
    {
        return $this->_timeZones;
    }

    /**
     * @param array $timeZones
     *
     * @return $this
     */
    public function setTimeZones($timeZones)
    {
        $this->_timeZones = $timeZones;

        return $this;
    }

    /**
     * @param int|string $storeId
     *
     * @return string
     */
    public function getTimeZone($storeId)
    {
        $timeZones = $this->getTimeZones();
        if (isset($timeZones[$storeId])) {
            return $timeZones[$storeId];
        }

        $timeZone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeId);
        $timeZones[$storeId] = $timeZone;

        $this->setTimeZones($timeZones);
        return $timeZone;
    }

    /**
     * Method to destroy temporary label files that have been stored for too long.
     *
     * By default the PostNL module creates temporary label files in order to merge them into a single shipping label.
     * These files are then destroyed. However, sometimes these files may survive the script if the script has
     * encountered an error. This method will make sure these files will not survive indefinitely, which may lead to the
     * file system being overburdened or the server running out of hard drive space.
     *
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function cleanTempLabels()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
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
         * If this setting is empty, end the script.
         */
        $maxFileStorageTime = (int) Mage::getStoreConfig(
            self::XPATH_MAX_FILE_STORAGE,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );
        if (empty($maxFileStorageTime)) {
            $helper->cronLog('No max file storage time defined. Exiting cron.');
            return $this;
        }

        /**
         * Get the temporary label filename constant. This is used to construct the filename together with
         * an md5 hash of the content and a timestamp.
         *
         * @var $labelModel TIG_PostNL_Model_Core_Label
         */
        /** @noinspection PhpParamsInspection */
        $labelModel    = Mage::app()->getConfig()->getModelClassName('postnl_core/label');
        $tempLabelName = $labelModel::TEMP_LABEL_FILENAME;

        $helper->cronLog('Attempting to read temp label files from %s.', $tempLabelsDirectory);

        /**
         * If the directory cannot be read, throw an exception.
         */
        if (!is_readable($tempLabelsDirectory)) {
            $helper->cronLog('Temporary label storage is unreadable. Exiting cron.');
            throw new TIG_PostNL_Exception(
                $helper->__('Unable to read directory: %s', $tempLabelsDirectory),
                'POSTNL-0096'
            );
        }

        /**
         * Get all temporary label files in the directory
         */
        $files = glob($tempLabelsDirectory . DS . '*' . $tempLabelName);

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
                $helper->cronLog(
                    "File {$filename} is less than {$maxFileStorageTime}s old. Continuing with the next file."
                );
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
     * @return $this
     *
     * @throws TIG_PostNL_Exception
     */
    public function cleanOldLocks()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
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

        $helper->cronLog('Attempting to read lock files from %s.', $locksDirectory);

        /**
         * If the directory cannot be read, throw an exception.
         */
        if (!is_readable($locksDirectory)) {
            $helper->cronLog('Lock storage is unreadable. Exiting cron.');
            throw new TIG_PostNL_Exception(
                $helper->__('Unable to read directory: %s', $locksDirectory),
                'POSTNL-0096'
            );
        }

        /**
         * Get all PostNL lock files in the directory
         */
        $files = glob($locksDirectory . DS . 'postnl_process_*');

        $fileCount = count($files);
        if ($fileCount < 1) {
            $helper->cronLog('No PostNL locks found. Exiting cron.');
            return $this;
        }

        /**
         * Locks may only exist for 3600 seconds (1 hour) before being removed
         */
        $maxFileStorageTime = 3600;
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getSingleton('core/date');
        $now = $dateModel->gmtTimestamp();
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
     * @return $this
     */
    public function getBarcodes()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl/cif');

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
        $postnlShipmentCollection->addFieldToFilter('main_barcode', array('null' => true))
                                 ->addFieldToFilter('shipment_id', array('notnull' => true));

        if ($postnlShipmentCollection->getSize() < 1) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Getting barcodes for {$postnlShipmentCollection->getSize()} shipments.");

        $counter = 1000;
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            if (!$postnlShipment->getShipment(false)) {
                continue;
            }

            /**
             * Process a maximum of 1000 shipments (to prevent Cif from being overburdened).
             * Only successful requests count towards this number
             */
            if ($counter < 1) {
                break;
            }

            if (!$postnlShipment->canGenerateBarcode()) {
                continue;
            }

            /**
             * Attempt to generate a barcode. Continue with the next one if it fails.
             */
            try {
                $helper->cronLog("Getting barcodes for shipment #{$postnlShipment->getId()}.");
                $postnlShipment->generateBarcodes();

                $printReturnLabel = $helper->isReturnsEnabled($postnlShipment->getStoreId());
                if ($printReturnLabel && $postnlShipment->canGenerateReturnBarcode()) {
                    $postnlShipment->generateReturnBarcode();
                }

                $postnlShipment->save();

                $counter--;
            } catch (Exception $e) {
                $helper->logException($e);
            }
        }

        $helper->cronLog('GetBarcodes cron has finished.');

        return $this;
    }

    /**
     * Update shipping status for all confirmed, but undelivered shipments.
     *
     * @return $this
     */
    public function updateShippingStatus()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
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
        /** @noinspection PhpParamsInspection */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus          = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $deliveredStatus          = $postnlShipmentModelClass::SHIPPING_PHASE_DELIVERED;

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
                                 )
                                 ->addFieldToFilter(
                                     'shipment_id',
                                     array(
                                         'notnull' => true
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
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to update the shipping status. Continue with the next one if it fails.
             */
            try{
                if (!$postnlShipment->getShipment(false)) {
                    continue;
                }

                $helper->cronLog("Updating shipping status for shipment #{$postnlShipment->getShipment()->getId()}");

                if (!$postnlShipment->canUpdateShippingStatus()) {
                    $postnlShipment->unlock();
                    $helper->cronLog("Updating shipment #{$postnlShipment->getShipment()->getId()} is not allowed. " .
                        "Continuing with next shipment.");
                    continue;
                }

                $postnlShipment->updateShippingStatus()
                               ->save();
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                $postnlShipment->unlock();

                $this->_parseErrorCodes($e, $postnlShipment);
            } catch (Exception $e) {
                $postnlShipment->unlock();

                $helper->logException($e);
            }
        }

        $helper->cronLog('UpdateShippingStatus cron has finished.');

        return $this;
    }

    /**
     * Update return shipment status for all shipments whose return labels have been printed.
     *
     * @return $this
     */
    public function updateReturnStatus()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('UpdateReturnStatus cron starting...');

        /**
         * @var $postnlShipmentModelClass TIG_PostNL_Model_Core_Shipment
         */
        /** @noinspection PhpParamsInspection */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $deliveredStatus = $postnlShipmentModelClass::SHIPPING_PHASE_DELIVERED;

        /**
         * Get the date on which we can no longer requests return status updates for shipments.
         */
        $maxReturnDuration = Mage::getStoreConfig(self::XPATH_RETURN_EXPIRE_DAYS, Mage_Core_Model_App::ADMIN_STORE_ID);
        $returnExpireDate  = new DateTime('now', new DateTimeZone('UTC'));
        $returnExpireDate->sub(new DateInterval("P{$maxReturnDuration}D"));

        /**
         * Get all postnl shipments with a barcode, that are confirmed and are not yet delivered.
         *
         * Resulting SQL:
         * SELECT  `main_table` . *
         * FROM  `tig_postnl_shipment` AS  `main_table`
         * WHERE (
         *     return_labels_printed =1
         * )
         * AND (
         *     confirm_status =  'confirmed'
         * )
         * AND (
         *     (
         *         (
         *             return_phase !=  '4'
         *         )
         *         OR (
         *             return_phase IS NULL
         *         )
         *     )
         * )
         * AND (
         *     shipment_id IS NOT NULL
         * )
         * AND (
         *     confirmed_at >=  '{$returnExpireDate->format('Y-m-d')}'
         * )
         */
        $postnlShipmentCollection = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipmentCollection->addFieldToFilter(
                                     'return_labels_printed',
                                     array('eq' => 1)
                                 )
                                 ->addFieldToFilter(
                                     'confirm_status',
                                     array('eq' => $confirmedStatus)
                                 )
                                 ->addFieldToFilter(
                                     'return_phase',
                                     array(
                                         array('neq' => $deliveredStatus),
                                         array('null' => true)
                                     )
                                 )
                                 ->addFieldToFilter(
                                     'shipment_id',
                                     array(
                                         'notnull' => true
                                     )
                                 )
                                 ->addFieldToFilter(
                                     'confirmed_at',
                                     array(
                                         'gteq' => $returnExpireDate->format('Y-m-d')
                                     )
                                 );

        if ($postnlShipmentCollection->getSize() < 1) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Return status will be updated for {$postnlShipmentCollection->getSize()} shipments.");

        /**
         * Request a return status update
         */
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to update the return status. Continue with the next one if it fails.
             */
            try{
                if (!$postnlShipment->getShipment(false)) {
                    continue;
                }

                $helper->cronLog("Updating return status for shipment #{$postnlShipment->getShipment()->getId()}");

                if (!$postnlShipment->canUpdateReturnStatus()) {
                    $postnlShipment->unlock();
                    $helper->cronLog(
                        "Updating return status for shipment #{$postnlShipment->getShipment()->getId()} is not " .
                        "allowed. Continuing with next shipment."
                    );
                    continue;
                }

                $postnlShipment->updateReturnStatus()
                               ->save();
            } catch (TIG_PostNL_Model_Core_Cif_Exception $e) {
                $postnlShipment->unlock();

                $this->_parseErrorCodes($e, $postnlShipment, true);
            } catch (Exception $e) {
                $postnlShipment->unlock();

                $helper->logException($e);
            }
        }

        $helper->cronLog('UpdateShippingStatus cron has finished.');

        return $this;
    }

    /**
     * Parses an TIG_PostNL_Model_Core_Cif_Exception exception in order to process specific error codes
     *
     * @param TIG_PostNL_Model_Core_Cif_Exception $e
     * @param TIG_PostNL_Model_Core_Shipment      $postnlShipment
     * @param boolean                             $isReturnStatus
     *
     * @return $this
     */
    protected function _parseErrorCodes($e, $postnlShipment, $isReturnStatus = false)
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Certain error numbers are processed differently
         */
        $errorNumbers = $e->getErrorNumbers();

        if (!$errorNumbers) {
            $helper->logException($e);
            return $this;
        }

        /**
         * @var $cifAbstractModelClassName TIG_PostNL_Model_Core_Cif_Abstract
         */
        /** @noinspection PhpParamsInspection */
        $cifAbstractModelClassName = Mage::getConfig()->getModelClassName('postnl_core/cif_abstract');
        foreach ($errorNumbers as $errorNumber) {
            if ($errorNumber != $cifAbstractModelClassName::SHIPMENT_NOT_FOUND_ERROR_NUMBER) { // Collo not found error
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
            $yesterday = new DateTime('now', new DateTimeZone('UTC'));
            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getSingleton('core/date');
            $yesterday->setTimestamp($dateModel->gmtTimestamp())
                      ->sub(new DateInterval('P1D'));

            $yesterday = $yesterday->getTimestamp();

            if ($confirmedAt > $yesterday) {
                return $this;
            }

            /**
             * Set 'shipment not found' status
             */
            $helper->cronLog(
                "Shipment #{$postnlShipment->getId()} could not be found by CIF and was confirmed more than 1 day ago!"
            );

            if (true === $isReturnStatus) {
                $postnlShipment->setReturnPhase($postnlShipment::SHIPPING_PHASE_NOT_APPLICABLE)
                               ->save();
            } else {
                $postnlShipment->setShippingPhase($postnlShipment::SHIPPING_PHASE_NOT_APPLICABLE)
                               ->save();
            }

            return $this;
        }

        $helper->logException($e);
        return $this;
    }

    /**
     * Removes expired confirmations by resetting the postnl shipment to a pre-confirm state.
     *
     * @return $this
     */
    public function expireConfirmation()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl/cif');

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
        /** @noinspection PhpParamsInspection */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;
        $collectionPhase = $postnlShipmentModelClass::SHIPPING_PHASE_COLLECTION;

        $confirmationExpireDays = Mage::getStoreConfig(
            self::XPATH_CONFIRM_EXPIRE_DAYS,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        $expireTimestamp = new DateTime('now', new DateTimeZone('UTC'));
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getSingleton('core/date');
        $expireTimestamp->setTimestamp($dateModel->gmtTimestamp())
                        ->sub(new DateInterval("P{$confirmationExpireDays}D"));

        $expireDate = $expireTimestamp->format('Y-m-d H:i:s');

        $helper->cronLog("All confirmation placed before {$expireDate} will be expired.");

        /**
         * Get all postnl shipments that have been confirmed over X days ago and who have not yet been shipped.
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
                                 )
                                 ->addFieldToFilter(
                                     'shipment_id',
                                     array(
                                         'notnull' => true
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
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            /**
             * Attempt to reset the shipment to a pre-confirmed status
             */
            try{
                if (!$postnlShipment->getShipment(false)) {
                    continue;
                }

                $helper->cronLog("Expiring confirmation of shipment #{$postnlShipment->getId()}");
                $postnlShipment->resetConfirmation()
                               ->setConfirmStatus($postnlShipment::CONFIRM_STATUS_CONFIRM_EXPIRED);

                /**
                 * Generate new barcodes as the current ones have expired.
                 */
                if ($postnlShipment->canGenerateBarcode()) {
                    $postnlShipment->generateBarcodes();
                }


                $printReturnLabel = $helper->isReturnsEnabled($postnlShipment->getStoreId());
                if ($printReturnLabel && $postnlShipment->canGenerateReturnBarcode()) {
                    $postnlShipment->generateReturnBarcode();
                }

                $postnlShipment->save();
            } catch (Exception $e) {
                $helper->logException($e);
            }
        }
        $helper->cronLog('ExpireConfirmation cron has finished.');

        return $this;
    }

    /**
     * Send a track & trace e-mail to the customer.
     *
     * @return $this
     */
    public function sendTrackAndTraceEmail()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active.
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('SendTrackAndTraceEmail cron starting...');

        /**
         * Check each storeview if sending track & trace emails is allowed.
         */
        $allowedStoreIds = array();
        foreach (array_keys(Mage::app()->getStores()) as $storeId) {
            if (Mage::getStoreConfig(self::XPATH_SEND_TRACK_AND_TRACE_EMAIL, $storeId)) {
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
        /** @noinspection PhpParamsInspection */
        $postnlShipmentModelClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');
        $confirmedStatus = $postnlShipmentModelClass::CONFIRM_STATUS_CONFIRMED;

        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getSingleton('core/date');
        $twentyMinutesAgo = new DateTime('now', new DateTimeZone('UTC'));
        $twentyMinutesAgo->setTimestamp($dateModel->gmtTimestamp())
                         ->sub(new DateInterval('PT20M'));

        $twentyMinutesAgo = $twentyMinutesAgo->format('Y-m-d H:i:s');

        $oneDayAgo = new DateTime('now', new DateTimeZone('UTC'));
        $oneDayAgo->setTimestamp($dateModel->gmtTimestamp())
                  ->sub(new DateInterval('P1DT20M'));

        $oneDayAgo = $oneDayAgo->format('Y-m-d H:i:s');

        $helper->cronLog("Track and trace email will be sent for all shipments that were confirmed on or before " .
            "{$twentyMinutesAgo}.");

        /**
         * Get all postnl shipments that have been confirmed over 20 minutes ago whose track & trace e-mail has not yet
         * been sent.
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
         *     AND confirmed_at >= '{$$oneDayAgo}'
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
         * AND (
         *     shipment_id IS NOT NULL
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
                                     array(
                                         'from' => $oneDayAgo,
                                         'to'   => $twentyMinutesAgo,
                                     )
                                 )
                                 ->addFieldToFilter(
                                    'track_and_trace_email_sent',
                                    array(
                                        array('null' => true),
                                        array('eq' => '0')
                                    )
                                 )
                                 ->addFieldToFilter(
                                     'shipment_id',
                                     array(
                                         'notnull' => true
                                     )
                                 );

        /**
         * Check to see if there are any results.
         */
        if (!$postnlShipmentCollection->getSize()) {
            $helper->cronLog('No valid shipments found. Exiting cron.');
            return $this;
        }

        $helper->cronLog("Track & trace emails will be sent for {$postnlShipmentCollection->getSize()} shipments.");

        /**
         * Send the track and trace email for all shipments.
         */
        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipmentCollection as $postnlShipment) {
            if (!$postnlShipment->getShipment(false)) {
                continue;
            }

            /**
             * Check if sending the email is allowed for this shipment.
             */
            $storeId = $postnlShipment->getStoreId();
            if (!in_array($storeId, $allowedStoreIds) || !$postnlShipment->canSendTrackAndTraceEmail()) {
                $helper->cronLog(
                    "Sending the track and trace email is not allowed for shipment #{$postnlShipment->getId()}."
                );
                continue;
            }

            /**
             * Attempt to send the email.
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
     * Deletes labels belonging to shipments that have been delivered as well as labels who have no associated
     * shipments.
     *
     * @return $this
     */
    public function removeOldLabels()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog('RemoveOldLabels cron starting...');

        /**
         * Get the PostNL Shipment class name for later use
         *
         * @var $postnlShipmentClass TIG_PostNL_Model_Core_Shipment
         */
        /** @noinspection PhpParamsInspection */
        $postnlShipmentClass = Mage::getConfig()->getModelClassName('postnl_core/shipment');

        /**
         * Get the label collection
         */
        /** @var TIG_PostNL_Model_Core_Resource_Shipment_Label_Collection $labelsCollection */
        $labelsCollection = Mage::getResourceModel('postnl_core/shipment_label_collection');

        /**
         * We only need the label IDs
         */
        $labelsCollection->addFieldToSelect('label_id');

        /** @var Mage_Core_Model_Resource $resource */
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
         * Get the date on which we can no longer requests return status updates for shipments.
         */
        $maxReturnDuration = Mage::getStoreConfig(self::XPATH_RETURN_EXPIRE_DAYS, Mage_Core_Model_App::ADMIN_STORE_ID);
        $returnExpireDate  = new DateTime('now', new DateTimeZone('UTC'));
        $returnExpireDate->sub(new DateInterval("P{$maxReturnDuration}D"));

        /**
         * Filter the collection by the lack of a parent_id OR shipping_phase being 'delivered'. Also filter based on
         * the confirm date. This is to allow return shipments enough time to be returned.
         *
         * Resulting query:
         * SELECT `main_table`.`label_id` , `postnl_shipment`.`shipping_phase`
         * FROM `tig_postnl_shipment_label` AS `main_table`
         * LEFT JOIN `tig_postnl_shipment` AS `postnl_shipment`
         *     ON `main_table`.`parent_id` = `postnl_shipment`.`entity_id`
         * WHERE (
         *     (
         *         parent_id IS NULL
         *     )
         *     OR (
         *         shipping_phase = 4
         *     )
         * )
         * AND
         * (
         *     (
         *         confirmed_at >= {$returnExpireDate}
         *     )
         *     OR (
         *         return_phase = 4
         *     )
         * )
         */
        $labelsCollection->addFieldToFilter(
                             array('parent_id', 'shipping_phase'),
                             array(
                                 array(
                                     'null' => true
                                 ),
                                 array(
                                     'eq' => $postnlShipmentClass::SHIPPING_PHASE_DELIVERED
                                 ),
                             )
                         )
                         ->addFieldToFilter(
                             array('confirmed_at', 'return_phase'),
                             array(
                                 array(
                                    'gteq' => $returnExpireDate->format('Y-m-d')
                                 ),
                                 array(
                                     'eq' => $postnlShipmentClass::SHIPPING_PHASE_DELIVERED
                                 )
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
        /** @var TIG_PostNL_Model_Core_Shipment_Label $label */
        foreach ($labelsCollection as $label) {
            $helper->cronLog("Deleting label #{$label->getId()}.");
            $label->delete();
        }
        $helper->cronLog('RemoveOldLabels cron has finished.');

        return $this;
    }

    /**
     * Update products with newly added PostNL attributes. This cron will process 250 products per run.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function updateProductAttribute()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        $helper->cronLog($helper->__('UpdateProductAttribute cron starting...'));

        Mage::app()->getCacheInstance()->cleanType('config');

        $data = Mage::getStoreConfig(self::XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA, Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!$data) {
            /**
             * If all attributes have been processed, remove the cron from the schedule.
             */
            $helper->cronLog($helper->__('All attributes have been processed. Removing cron.'));

            $this->_removeAttributeUpdateCron();
            return $this;
        }

        $data = unserialize($data);
        $currentAttributeData = current($data);
        if (empty($currentAttributeData[0]) || empty($currentAttributeData[1])) {
            /**
             * If all attributes have been processed, remove the cron from the schedule.
             */
            $helper->cronLog($helper->__('All attributes have been processed. Removing cron.'));

            $this->_removeAttributeUpdateCron();
            return $this;
        }

        $helper->cronLog(
            $helper->__('Updating product attribute data: %s', var_export($currentAttributeData, true))
        );

        /**
         * Get all products that need to be updated.
         */
        /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
        $productCollection = Mage::getResourceModel('catalog/product_collection')
                                 ->addStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID)
                                 ->addFieldToFilter(
                                     'type_id',
                                     array(
                                         'in' => $currentAttributeData[1]
                                     )
                                 );

        foreach (array_keys($currentAttributeData[0]) as $attribute) {
            $productCollection->addAttributeToSelect($attribute, 'left')
                              ->addFieldToFilter($attribute, array('null' => true));
        }

        $productCollection->getSelect()->limit(self::MAX_PRODUCTS_TO_UPDATE);

        /**
         * If there are fewer than 250 products remaining, this will be the last time this cron is run.
         */
        $finalRun = false;
        $allIds = $productCollection->getAllIds();
        if (count($allIds) < self::MAX_PRODUCTS_TO_UPDATE) {
            $finalRun = true;
        }

        $helper->cronLog($helper->__('Updating product IDs: %s', var_export($allIds, true)));

        if (!empty($allIds)) {
            try {
                /**
                 * Update the attributes of these products.
                 */
                /** @var Mage_Catalog_Model_Product_Action $productAction */
                $productAction = Mage::getSingleton('catalog/product_action');
                $productAction->updateAttributes(
                    $allIds,
                    $currentAttributeData[0],
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );
            } catch (Exception $e) {
                /**
                 * If an error occurred not all products were processed, so the cron is not finished quite yet.
                 */
                $finalRun = false;
                $helper->logException($e);
                $helper->cronLog($helper->__('An error occurred while processing this attribute.'));
            }
        }

        if ($finalRun) {
            $helper->cronLog($helper->__('No products left to update.'));

            /**
             * Remove the processed attributes from the attribute data array.
             */
            array_shift($data);

            if (!empty($data)) {
                /**
                 * If there is still data left, update the data for the next run.
                 */
                Mage::getConfig()->saveConfig(
                    self::XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA,
                    serialize($data),
                    'default',
                    Mage_Core_Model_App::ADMIN_STORE_ID
                );

                Mage::app()->getCacheInstance()->cleanType('config');
            } else {
                /**
                 * If all attributes have been processed, remove the cron from the schedule.
                 */
                $helper->cronLog($helper->__('All attributes have been processed. Removing cron.'));

                $this->_removeAttributeUpdateCron();
            }
        }

        $helper->cronLog($helper->__('UpdateProductAttribute cron has finished.'));

        return $this;
    }

    /**
     * Remove the updateProductAttribute cron.
     *
     * @return $this
     * @throws Exception
     */
    protected function _removeAttributeUpdateCron()
    {
        Mage::getConfig()->saveConfig(
            self::XPATH_PRODUCT_ATTRIBUTE_UPDATE_DATA,
            null,
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        /** @var Mage_Core_Model_Config_Data $configData */
        $configData = Mage::getModel('core/config_data')
                          ->load(TIG_PostNL_Model_Resource_Setup::UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH, 'path');
        $configData->setValue(null)
                   ->setPath(self::UPDATE_PRODUCT_ATTRIBUTE_STRING_PATH)
                   ->save();

        Mage::app()->getCacheInstance()->cleanType('config');

        return $this;
    }

    /**
     * Modify the confirm- and delivery dates for all PostNL orders and shipments. These dates are currently entered in
     * the storeview's timezone. These should be entered in the UTC timezone.
     *
     * @return $this
     */
    public function updateDateTimeZone()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        $helper->cronLog($helper->__('UpdateDateTimeZone cron starting...'));

        Mage::app()->getCacheInstance()->cleanType('config');

        $data = Mage::getStoreConfig(
            TIG_PostNL_Model_Resource_Setup::XPATH_UPDATE_DATE_TIME_ZONE_DATA,
            Mage_Core_Model_App::ADMIN_STORE_ID
        );
        if (!$data) {
            $helper->cronLog($helper->__('No IDs found. Exiting cron.'));
            return $this;
        }

        $data = unserialize($data);

        /**
         * Process the shipments.
         */
        if (!empty($data['shipment'])) {
            $data['shipment'] = $this->_updatePostnlShipmentDateTimeZone($data['shipment']);
        }

        /**
         * Process the orders.
         */
        if (!empty($data['order'])) {
            $data['order'] = $this->_updatePostnlOrderDateTimeZone($data['order']);
        }

        if (empty($data['shipment']) && empty($data['order'])) {
            /**
             * All orders and shipments have been processed so we can remove the cron.
             */
            $helper->cronLog($helper->__('All orders and shipments have been processed. Removing cron.'));

            Mage::getConfig()->saveConfig(
                TIG_PostNL_Model_Resource_Setup::XPATH_UPDATE_DATE_TIME_ZONE_DATA,
                null,
                'default',
                Mage_Core_Model_App::ADMIN_STORE_ID
            );

            /** @var Mage_Core_Model_Config_Data $configData */
            $configData = Mage::getModel('core/config_data')
                              ->load(TIG_PostNL_Model_Resource_Setup::UPDATE_DATE_TIME_ZONE_STRING_PATH, 'path');
            $configData->setValue(null)
                       ->setPath(TIG_PostNL_Model_Resource_Setup::UPDATE_DATE_TIME_ZONE_STRING_PATH)
                       ->save();
        }

        Mage::getConfig()->saveConfig(
            TIG_PostNL_Model_Resource_Setup::XPATH_UPDATE_DATE_TIME_ZONE_DATA,
            serialize($data),
            'default',
            Mage_Core_Model_App::ADMIN_STORE_ID
        );

        Mage::app()->getCacheInstance()->cleanType('config');

        $helper->cronLog($helper->__('UpdateDateTimeZone cron has finished.'));

        return $this;
    }

    /**
     * Update the time zone of the confirm- and delivery date value for PostNL shipments.
     *
     * @param array $ids
     *
     * @return array
     */
    protected function _updatePostnlShipmentDateTimeZone($ids)
    {
        /**
         * Get all PostNL shipments.
         */
        $postnlShipments = Mage::getResourceModel('postnl_core/shipment_collection');
        $postnlShipments->addFieldToFilter('entity_id', array('in' => $ids));
        $postnlShipments->getSelect()->limit(100);

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /** @var TIG_PostNL_Model_Core_Shipment $postnlShipment */
        foreach ($postnlShipments as $postnlShipment) {
            $helper->cronLog($helper->__('Updating shipment ID: %s', $postnlShipment->getId()));

            /**
             * Remove this shipment's ID from the IDs array. Even if it fails for this shipment, we don't want to try
             * again.
             */
            $key = array_search($postnlShipment->getId(), $ids);
            if($key !== false) {
                unset($ids[$key]);
            }

            /**
             * Get the shipment's timezone.
             */
            $timeZone = $this->getTimeZone($postnlShipment->getStoreId());

            /**
             * Get the confirm and delivery dates in their current timezone (whichever timezone the storeview is in).
             */
            $confirmDate  = $postnlShipment->getConfirmDate();
            $deliveryDate = $postnlShipment->getDeliveryDate();

            /**
             * Modify the dates to the UTC timezone.
             */
            $confirmDateTime = new DateTime($confirmDate, new DateTimeZone($timeZone));
            $confirmDateTime->setTimezone(new DateTimeZone('UTC'));

            $deliveryDateTime = new DateTime($deliveryDate, new DateTimeZone($timeZone));
            $deliveryDateTime->setTimezone(new DateTimeZone('UTC'));

            /**
             * Update the dates.
             */
            $postnlShipment->setConfirmDate($confirmDateTime->getTimestamp())
                           ->setDeliveryDate($deliveryDateTime->getTimestamp());

            /**
             * Save the shipment.
             */
            try {
                $postnlShipment->save();
            } catch (Exception $e) {
                $helper->cronLog($helper->__('Updating shipment ID %s failed.', $postnlShipment->getId()));
                $helper->logException($e);
            }
        }

        return $ids;
    }

    /**
     * Update the time zone of the confirm- and delivery date value for PostNL orders.
     *
     * @param array $ids
     *
     * @return array
     */
    protected function _updatePostnlOrderDateTimeZone($ids)
    {
        /**
         * Get all PostNL shipments.
         */
        $postnlOrders = Mage::getResourceModel('postnl_core/order_collection');
        $postnlOrders->addFieldToFilter('entity_id', array('in' => $ids));
        $postnlOrders->getSelect()->limit(100);

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /** @var TIG_PostNL_Model_Core_Order $postnlOrder */
        foreach ($postnlOrders as $postnlOrder) {
            $helper->cronLog($helper->__('Updating order ID: %s', $postnlOrder->getId()));

            /**
             * Remove this order's ID from the IDs array. Even if it fails for this shipment, we don't want to try
             * again.
             */
            $key = array_search($postnlOrder->getId(), $ids);
            if($key !== false) {
                unset($ids[$key]);
            }

            /**
             * Get the order's timezone.
             */
            $timeZone = $this->getTimeZone($postnlOrder->getStoreId());

            /**
             * Get the confirm and delivery dates in their current timezone (whichever timezone the storeview is in).
             */
            $confirmDate  = $postnlOrder->getConfirmDate();
            $deliveryDate = $postnlOrder->getDeliveryDate();

            /**
             * Modify the dates to the UTC timezone.
             */
            $confirmDateTime = new DateTime($confirmDate, new DateTimeZone($timeZone));
            $confirmDateTime->setTimezone(new DateTimeZone('UTC'));

            $deliveryDateTime = new DateTime($deliveryDate, new DateTimeZone($timeZone));
            $deliveryDateTime->setTimezone(new DateTimeZone('UTC'));

            /**
             * Update the dates.
             */
            $postnlOrder->setConfirmDate($confirmDateTime->getTimestamp())
                           ->setDeliveryDate($deliveryDateTime->getTimestamp());

            /**
             * Save the order.
             */
            try {
                $postnlOrder->save();
            } catch (Exception $e) {
                $helper->cronLog($helper->__('Updating order ID %s failed.', $postnlOrder->getId()));
                $helper->logException($e);
            }
        }

        return $ids;
    }

    /**
     * Check the integrity of the PostNL order and shipment data.
     *
     * @return $this
     *
     * @throws Exception
     */
    public function integrityCheck()
    {
        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * Check if the PostNL module is active
         */
        if (!$helper->isEnabled()) {
            return $this;
        }

        $helper->cronLog($helper->__('IntegrityCheck cron starting...'));

        /** @var TIG_PostNL_Model_Core_Service_IntegrityCheck $integrityCheckModel */
        $integrityCheckModel = Mage::getModel('postnl_core/service_integrityCheck');
        try {
            $errors = $integrityCheckModel->integrityCheck();

            if (!empty($errors)) {
                $helper->cronLog($helper->__('The following errors were found: %s', var_export($errors, true)));
            } else {
                $helper->cronLog($helper->__('No errors found.'));
            }

            Mage::getResourceSingleton('postnl_core/integrity')->saveIntegrityCheckResults($errors);

            $helper->cronLog($helper->__('Results have been saved.'));
        } catch (Exception $e) {
            $helper->cronLog($helper->__("An error occurred while checking the PostNL extension's data integrity."));
            $helper->logException($e);
        }

        $helper->cronLog($helper->__('IntegrityCheck cron has finished.'));

        return $this;
    }
}
