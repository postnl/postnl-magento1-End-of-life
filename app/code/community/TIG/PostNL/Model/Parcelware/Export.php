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
class TIG_PostNL_Model_Parcelware_Export extends TIG_PostNL_Model_Core_Cif
{
    /**
     * XML paths to parcelware references
     */
    const XML_PATH_CONTRACT_REF_NR = 'postnl/parcelware_export/contract_ref_nr';
    const XML_PATH_CONTRACT_NAME   = 'postnl/parcelware_export/contract_name';
    const XML_PATH_SENDER_REF_NR   = 'postnl/parcelware_export/sender_ref_nr';
    
    /**
     * Creates a parcelware export csv based for an array of PostNL shipments. This method basically consists of 3 parts:
     *  1. Fetch data from every shipment that we're going to put in the export file.
     *  2. Update the shipments.
     *  3. Actually create the CSV file and return an array containing data for whoever called this method (probably a controller).
     * 
     * @param array $shipments An array of TIG_PostNL_Model_Core_Shipment objects
     * 
     * @return array
     */
    public function exportShipments($postnlShipments)
    {
        $this->setIsGlobal(false);
        $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $helper = Mage::helper('postnl/parcelware');
        
        /**
         * Prepare a transaction save object. We're going to edit all the postbl shipments that we're going to export, however
         * we want all of them to be saved at the same time AFTER the export has been generated.
         */
        $transactionSave = Mage::getModel('core/resource_transaction');
                               
        $content = array();
        foreach ($postnlShipments as $postnlShipment) {
            /**
             * Set each shipment's is_parcelware_exported flag to true
             */
            $postnlShipment->setIsParcelwareExported(true);
            
            /**
             * If auto_confirm is enabled, confirm each shipment manually. Please note that we do not yet save these shipments.
             */
            if ($helper->isAutoConfirmEnabled() === true && !$postnlShipment->isConfirmed()) {
                $postnlShipment->setConfirmStatus($postnlShipment::CONFIRM_STATUS_CONFIRMED)
                               ->setConfirmedAt(Mage::getModel('core/date')->gmtTimestamp());
            }
            
            /**
             * Add the shipment to the transactonsave object.
             */
            $transactionSave->addObject($postnlShipment);
            
            /**
             * If this is a multi-colli shipment we need to treat each parcel as a seperate shipment and therefore, as a seperate
             * row in the csv export file.
             */
            $parcelCount = $postnlShipment->getParcelCount();
            if ($parcelCount > 1) {
                for ($i = 0; $i < $parcelCount; $i++) {
                    /**
                     * Get the shipment's data.
                     */
                    $shipmentData = $this->_getShipmentData($postnlShipment, $parcelCount, $i);
                    
                    $content[] = $shipmentData;
                }
                
                continue;
            }
            
            /**
             * Same as above, only for a single-colli shipment.
             */
            $shipmentData = $this->_getShipmentData($postnlShipment);
            
            $content[] = $shipmentData;
        }
        
        /**
         * Get the CSV headers. Basically these are the column names.
         */
        $csvHeaders = $this->_getCsvHeaders();
        
        /**
         * Prepare to create a new export file.
         */
        $io = Mage::getModel('varien/io_file');
        
        /**
         * Some parameters for the file. Please note that the filename is purely temporary. The name of the file you'll end up
         * downloading will be defined in the controller.
         */
        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';
        
        /**
         * Open and lock the file.
         */
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
 
        /**
         * Write the CSV headers and then each row of data.
         */
        $io->streamWriteCsv($csvHeaders);
        foreach ($content as $item) {
            $io->streamWriteCsv($item);
        }
        
        /**
         * This is what the controller will need to offer the file as a download response.
         */
        $exportArray = array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
        
        /**
         * Remember those shipments we added to the transaction save object? Now we can finally save them all at once.
         */
        $transactionSave->save();
        
        return $exportArray;
    }

    /**
     * Gets all shipment data for a CSV row.
     * 
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * 
     * @return array
     */
    protected function _getShipmentData($postnlShipment, $parcelCount = false, $count = false)
    {
        /**
         * @var Mage_Sales_Model_Order_Shipment
         */
        $shipment = $postnlShipment->getShipment();
        
        $this->setStoreId($shipment->getStoreId());
        
        /**
         * Get the address and reference data for this shipment. These are simple associative arrays
         */
        $addressData   = $this->_getAddressData($postnlShipment, $shipment);
        $referenceData = $this->_getReferenceData();
        
        /**
         * If this is part of a multi-colli shipment, we need to get slightly different parameters.
         */
        if ($parcelCount) {
                $shipmentData = array(
                    'ProductCodeDelivery' => $postnlShipment->getProductCode(),
                    'Barcode'             => $postnlShipment->getBarcode($count),
                    'MainBarcode'         => $postnlShipment->getMainBarcode(),
                    'GroupSequence'       => $count + 1,
                    'GroupCount'          => $parcelCount,
                );
        } else {
            $shipmentData = array(
                'ProductCodeDelivery' => $postnlShipment->getProductCode(),
                'Barcode'             => $postnlShipment->getMainBarcode(),
                'MainBarcode'         => $postnlShipment->getMainBarcode(),
                'GroupSequence'       => 1,
                'GroupCount'          => 1,
            );
        }
        
        /**
         * If this is an international (GlobalPack) shipment, we need some additional information regarding the contents of the
         * shipment.
         */
        $globalPackData = array();
        if ($postnlShipment->isGlobalShipment()) {
            $this->setIsGlobal(true);
            $globalPackData = $this->_getGlobalPackData($postnlShipment);
        }
        
        /**
         * Merge all the data we fetched above. Please note that the order in which we merge these arrays is critical to ensure
         * all values are placed in their respective columns.
         */
        $shipmentData = array_merge(
            $addressData, 
            $shipmentData, 
            $referenceData, 
            $globalPackData
        );
        
        return $shipmentData;
    }

    /**
     * Get all address data for a given shipment
     * 
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * @param Mage_Sales_Model_Shipments $shipment
     * 
     * @return array
     */
    protected function _getAddressData($postnlShipment, $shipment)
    {
        $address = $shipment->getShippingAddress();
        $streetData = $this->_getStreetData($address, false);
        
        $data = array(
            'CompanyName' => $address->getCompany(),
            'FirstName'   => $address->getFirstname(),
            'Name'        => $address->getLastname(),
            'Street'      => $streetData['streetname'],
            'HouseNr'     => $streetData['housenumber'],
            'HouseNrExt'  => $streetData['housenumberExtension'],
            'Zipcode'     => $address->getPostcode(),
            'City'        => $address->getCity(),
            'Countrycode' => $address->getCountryId(),
        );
        
        return $data;
    }
    
    /**
     * Get parcelware reference data for this shipment
     * 
     * @return array
     */
    protected function _getReferenceData()
    {
        $data = array(
            'ContractReference' => $this->_getContractReference(),
            'ContractName'      => $this->_getContractName(),
            'SenderReference'   => $this->_getSenderReference(),
        );
        
        return $data;
    }
    
    /**
     * Get GlobalPack data for a shipment. This is based on TIG_PostNL_Model_Core_Cif::getCustoms()
     * 
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     * 
     * @return array
     * 
     * @see TIG_PostNL_Model_Core_Cif::getCustoms()
     */
    protected function _getGlobalPackData($postnlShipment)
    {
        $shipment = $postnlShipment->getShipment();
        $shipmentType = $postnlShipment->getShipmentType();
        
        $globalPackData = array(
            'ShipmentType'           => $shipmentType, // Gift / Documents / Commercial Goods / Commercial Sample / Returned Goods
            'HandleAsNonDeliverable' => 'false',
            'Invoice'                => 'false',
            'Certificate'            => 'false',
            'License'                => 'false',
            'Currency'               => 'EUR',
        );
        
        /**
         * Check if the shipment should be treated as abandoned when it can't be delivered or if it should be returned to the sender
         */
        if ($postnlShipment->getTreatAsAbandoned()) {
            $globalPackData['HandleAsNonDeliverable'] = 'true';
        }
        
        /**
         * Add license info
         */
        if ($this->_getCustomsLicense()) {
            $globalPackData['License'] = $this->_getCustomsLicense();
        }
        
        /**
         * Add certificate info
         */
        if ($this->_getCustomsCertificate()) {
            $globalPackData['Certificate'] = $this->_getCustomsCertificate();
        }
        
        /**
         * Add invoice info
         * 
         * This is mandatory for certain shipment types as well as when neither a certificate nor a license is available
         */
        $invoiceRequiredShipmentTypes = $this->getInvoiceRequiredShipmentTypes();
        if (in_array($shipmentType, $invoiceRequiredShipmentTypes)
            || ($globalPackData['License'] == 'false'
                && $globalPackData['Certificate'] == 'false'
            )
        ) {
            $shipmentId = $shipment->getIncrementId();
            $globalPackData['Invoice'] = $shipmentId;
        }
        
        /**
         * Add information about the contents of the shipment
         */
        $itemCount = 0;
        $content = array();
        $items = $this->_sortCustomsItems($shipment->getAllItems());
        
        foreach ($items as $item) {
            /**
             * A maximum of 5 rows are allowed
             */
            if (++$itemCount > 5) {
                break;
            }
            
            /**
             * Calculate the item's weight in kg
             */
            $itemWeight = Mage::helper('postnl/cif')->standardizeWeight(
                $item->getWeight(), 
                $this->getStoreId()
            );
            
            /**
             * Item weight may not be less than 1 gram
             */
            if ($itemWeight < 0.01) {
                $itemWeight = 0.01;
            }
            
            $itemData = array(
                'Product_' . $itemCount . '_Description'     => $this->_getCustomsDescription($item),
                'Product_' . $itemCount . '_Quantity'        => $item->getQty(),
                'Product_' . $itemCount . '_Weight'          => $itemWeight * $item->getQty(),
                'Product_' . $itemCount . '_Value'           => ($this->_getCustomsValue($item) * $item->getQty()),
                'Product_' . $itemCount . '_HSTariffNr'      => $this->_getHSTariff($item),
                'Product_' . $itemCount . '_CountryOfOrigin' => $this->_getCountryOfOrigin($item),
            );
            
            $globalPackData = array_merge($globalPackData, $itemData);
        }
        
        return $globalPackData;
    }
    
    /**
     * Gets CSV headers for the parcelware export file.
     * 
     * @return array
     */
    protected function _getCsvHeaders()
    {
        $csvHeaders = array(
            'CompanyName',
            'FirstName',
            'Name',
            'Street',
            'HouseNr',
            'HouseNrExt',
            'Zipcode',
            'City',
            'Countrycode',
            'ProductCodeDelivery',
            'Barcode',
            'MainBarcode',
            'GroupSequence',
            'GroupCount',
            'ContractReference',
            'ContractName',
            'SenderReference',
        );
        
        if (!$this->getIsGlobal()) {
            return $csvHeaders;
        }
        
        $globalPackHeaders = array(
            'ShipmentType',
            'HandleAsNonDeliverable',
            'Invoice',
            'Certificate',
            'License',
            'Currency',
            'Product_1_Description',
            'Product_1_Quantity',
            'Product_1_Weight',
            'Product_1_Value',
            'Product_1_HSTariffNr',
            'Product_1_CountryOfOrigin',
            'Product_2_Description',
            'Product_2_Quantity',
            'Product_2_Weight',
            'Product_2_Value',
            'Product_2_HSTariffNr',
            'Product_2_CountryOfOrigin',
            'Product_3_Description',
            'Product_3_Quantity',
            'Product_3_Weight',
            'Product_3_Value',
            'Product_3_HSTariffNr',
            'Product_3_CountryOfOrigin',
            'Product_4_Description',
            'Product_4_Quantity',
            'Product_4_Weight',
            'Product_4_Value',
            'Product_4_HSTariffNr',
            'Product_4_CountryOfOrigin',
            'Product_5_Description',
            'Product_5_Quantity',
            'Product_5_Weight',
            'Product_5_Value',
            'Product_5_HSTariffNr',
            'Product_5_CountryOfOrigin',
        );
        
        $csvHeaders = array_merge($csvHeaders, $globalPackHeaders);

        return $csvHeaders;
    }

    /**
     * Get the contract reference number
     * 
     * @return string
     */
    protected function _getContractReference()
    {
        $contractReference = Mage::getStoreConfig(self::XML_PATH_CONTRACT_REF_NR, $this->getStoreId());
        
        return $contractReference;
    }

    /**
     * Get the contract name
     * 
     * @return string
     */
    protected function _getContractName()
    {
        $contractName = Mage::getStoreConfig(self::XML_PATH_CONTRACT_NAME, $this->getStoreId());
        
        return $contractName;
    }

    /**
     * Get the sender address reference number
     * 
     * @return string
     */
    protected function _getSenderReference()
    {
        $senderReference = Mage::getStoreConfig(self::XML_PATH_SENDER_REF_NR, $this->getStoreId());
        
        return $senderReference;
    }
}
