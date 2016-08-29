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
 *
 * @method TIG_PostNL_Model_Parcelware_Export setIsGlobal(boolean $value)
 * @method boolean                            getIsGlobal()
 */
class TIG_PostNL_Model_Parcelware_Export extends TIG_PostNL_Model_Core_Cif
{
    /**
     * XML paths to Parcelware references
     */
    const XPATH_CONTRACT_REF_NR = 'postnl/parcelware_export/contract_ref_nr';
    const XPATH_CONTRACT_NAME   = 'postnl/parcelware_export/contract_name';
    const XPATH_SENDER_REF_NR   = 'postnl/parcelware_export/sender_ref_nr';

    /**
     * The separator used for the Parcelware export.
     */
    const CSV_SEPARATOR = ';';

    /**
     * @var Mage_Core_Model_Resource_Transaction|void
     */
    protected $_transactionSave;

    /**
     * Gets the transaction save object.
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function getTransactionSave()
    {
        if ($this->_transactionSave) {
            return $this->_transactionSave;
        }

        /**
         * Prepare a transaction save object. We're going to edit all the postnl shipments that we're going to export,
         * however we want all of them to be saved at the same time AFTER the export has been generated.
         */
        $transactionSave = Mage::getModel('core/resource_transaction');

        $this->setTransactionSave($transactionSave);
        return $transactionSave;
    }

    /**
     * @param mixed $transactionSave
     *
     * @return TIG_PostNL_Model_Parcelware_Export
     */
    public function setTransactionSave($transactionSave)
    {
        $this->_transactionSave = $transactionSave;

        return $this;
    }

    /**
     * Creates a Parcelware export csv based for an array of PostNL shipments. This method basically consists of 3
     * parts:
     *  1. Fetch data from every shipment that we're going to put in the export file.
     *  2. Update the shipments.
     *  3. Actually create the CSV file and return an array containing data for whoever called this method (probably a
     *     controller).
     *
     * @param array $postnlShipments An array of TIG_PostNL_Model_Core_Shipment objects
     *
     * @return array
     */
    public function exportShipments($postnlShipments)
    {
        $this->setIsGlobal(false);
        $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);

        /**
         * Get the CSV headers. Basically these are the column names.
         */
        $csvHeaders = $this->_getCsvHeaders();

        /**
         * Get the CSV data.
         */
        $content = $this->getCsvData($postnlShipments);

        /**
         * Prepare to create a new export file.
         */
        /** @var Varien_Io_File $io */
        $io = Mage::getModel('varien/io_file');

        /**
         * Some parameters for the file. Please note that the filename is purely temporary. The name of the file you'll
         * end up downloading will be defined in the controller.
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
        $io->streamWrite(implode(self::CSV_SEPARATOR, $csvHeaders));
        foreach ($content as $item) {
            /**
             * Remove any comma's and semicolon as these will break Parcelware's import.
             */
            foreach ($item as &$value) {
                $value = str_replace(array(',', self::CSV_SEPARATOR), '', $value);
            }

            $io->streamWrite(PHP_EOL . implode(self::CSV_SEPARATOR, $item));
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
        $this->getTransactionSave()->save();

        return $exportArray;
    }

    /**
     * Get the data for the CSV export from an array of PostNL shipments.
     *
     * @param array $postnlShipments
     *
     * @return array
     */
    public function getCsvData($postnlShipments)
    {
        /** @var TIG_PostNL_Helper_Parcelware $helper */
        $helper = Mage::helper('postnl/parcelware');
        $autoConfirmEnabled = $helper->isAutoConfirmEnabled();

        $transactionSave = $this->getTransactionSave();

        $content = array();

        /**
         * @var TIG_PostNL_Model_Core_Shipment $postnlShipment
         */
        foreach ($postnlShipments as $postnlShipment) {
            /**
             * Set each shipment's is_parcelware_exported flag to true
             */
            $postnlShipment->setIsParcelwareExported(true);

            /**
             * If auto_confirm is enabled, confirm each shipment manually. Please note that we do not yet save these
             * shipments.
             */
            if ($autoConfirmEnabled === true && !$postnlShipment->isConfirmed()) {
                $postnlShipment->registerConfirmation();
            }

            /**
             * Add the shipment to the transactonsave object.
             */
            $transactionSave->addObject($postnlShipment);

            /**
             * If this is a multi-colli shipment we need to treat each parcel as a separate shipment and therefore, as a
             * separate row in the csv export file.
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

        return $content;
    }

    /**
     * Gets all shipment data for a CSV row.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @param bool                           $parcelCount
     * @param bool                           $count
     * @return array
     */
    protected function _getShipmentData($postnlShipment, $parcelCount = false, $count = false)
    {
        /** @var TIG_PostNL_Helper_Parcelware $helper */
        $helper = Mage::helper('postnl/parcelware');

        /**
         * @var Mage_Sales_Model_Order_Shipment
         */
        $shipment = $postnlShipment->getShipment();

        $this->setStoreId($shipment->getStoreId());

        /**
         * Get the data for this shipment. The data consists of several associative arrays.
         */
        $addressData    = $this->_getAddressData($shipment);
        $addressData['SMSnr'] = $this->_getMobilePhoneNumber($postnlShipment);

        $pakjeGemakData = $this->_getPakjeGemakAddressData($postnlShipment);
        $referenceData  = $this->_getReferenceData();
        $extraCover     = array($postnlShipment->getExtraCoverAmount());
        $productOptions = $this->_getProductOptions($postnlShipment);

        /**
         * Get the current GMT timestamp as a point of reference
         */
        /** @var Mage_Core_Model_Date $dateModel */
        $dateModel = Mage::getModel('core/date');
        $now = $dateModel->gmtTimestamp();

        /**
         * Get the confirm and delivery dates for this shipment
         */
        $deliveryDate = '';
        $confirmDate  = date('Y-m-d', strtotime($postnlShipment->getConfirmDate(), $now));

        $postnlOrder = $postnlShipment->getPostnlOrder();

        if ($postnlOrder !== false) {
            $deliveryDate = date('Y-m-d', strtotime($postnlOrder->getDeliveryDate(), $now));
        }

        /**
         * If this is part of a multi-colli shipment, we need to get slightly different parameters.
         */
        if ($parcelCount) {
            $barcode = $postnlShipment->getBarcode($count);

            $shipmentData = array(
                'ProductCodeDelivery' => $this->_getParcelwareProductCode($postnlShipment),
                'GroupSequence'       => $count + 1,
                'GroupCount'          => $parcelCount,
                'ConfirmDate'         => $confirmDate,
                'DeliveryDate'        => $deliveryDate,
            );
        } else {
            $barcode = $postnlShipment->getMainBarcode();

            $shipmentData = array(
                'ProductCodeDelivery' => $this->_getParcelwareProductCode($postnlShipment),
                'GroupSequence'       => 1,
                'GroupCount'          => 1,
                'ConfirmDate'         => $confirmDate,
                'DeliveryDate'        => $deliveryDate,
            );
        }

        $mainBarcode           = $postnlShipment->getMainBarcode();
        $mainBarcodeComponents = $helper->splitBarcode($mainBarcode, $shipment->getStoreId());
        $mainBarcodeNumber     = $mainBarcodeComponents['number'];

        $barcodeComponents = $helper->splitBarcode($barcode, $shipment->getStoreId());
        $barcodeNumber     = $barcodeComponents['number'];

        $shipmentData['Barcode']     = $barcodeNumber;
        $shipmentData['MainBarcode'] = $mainBarcodeNumber;

        /**
         * If this is an international (GlobalPack) shipment, we need some additional information regarding the contents
         * of the shipment.
         */
        $globalPackData = array();
        if ($postnlShipment->isGlobalShipment()) {
            $this->setIsGlobal(true);
            $globalPackData = $this->_getGlobalPackData($postnlShipment);
        }

        /**
         * Merge all the data we fetched above. Please note that the order in which we merge these arrays is critical to
         * ensure all values are placed in their respective columns.
         */
        $shipmentData = array_merge(
            $addressData,
            $pakjeGemakData,
            $shipmentData,
            $referenceData,
            $extraCover,
            $productOptions,
            $globalPackData
        );

        return $shipmentData;
    }

    /**
     * Get all address data for the recipient of a shipment.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return array
     */
    protected function _getAddressData($shipment)
    {
        $address = $shipment->getShippingAddress();
        $streetData = $this->_getStreetData($address);

        $data = array(
            'CompanyName'   => $address->getCompany(),
            'FirstName'     => $address->getFirstname(),
            'Name'          => $address->getLastname(),
            'Street'        => $streetData['streetname'],
            'HouseNr'       => $streetData['housenumber'],
            'HouseNrExt'    => $streetData['housenumberExtension'],
            'Zipcode'       => str_replace(' ', '', $address->getPostcode()),
            'City'          => $address->getCity(),
            'Countrycode'   => $address->getCountryId(),
            'CustomerEmail' => $address->getEmail(),
        );

        return $data;
    }

    /**
     * Get address data for PakjeGemak (post office) addresses.
     *
     * @param TIG_PostNL_Model_Core_Shipment $postnlShipment
     *
     * @return array
     */
    protected function _getPakjeGemakAddressData($postnlShipment)
    {
        $pakjeGemakAddress = $postnlShipment->getPakjeGemakAddress();

        /**
         * If there is no PakjeGemak address to export, return an empty array.
         */
        if (!$pakjeGemakAddress) {
            $data = array(
                'PG_CompanyName' => '',
                'PG_Street'      => '',
                'PG_HouseNr'     => '',
                'PG_HouseNrExt'  => '',
                'PG_Zipcode'     => '',
                'PG_City'        => '',
                'PG_Countrycode' => '',
            );

            return $data;
        }

        $streetData = $this->_getStreetData($pakjeGemakAddress);

        $companyName = $pakjeGemakAddress->getCompany();
        if (!$companyName) { //PostNL Checkout stores the company name in the lastname field
            $pakjeGemakAddress->getname();
        }

        $data = array(
            'PG_CompanyName' => $companyName,
            'PG_Street'      => $streetData['streetname'],
            'PG_HouseNr'     => $streetData['housenumber'],
            'PG_HouseNrExt'  => $streetData['housenumberExtension'],
            'PG_Zipcode'     => str_replace(' ', '', $pakjeGemakAddress->getPostcode()),
            'PG_City'        => $pakjeGemakAddress->getCity(),
            'PG_Countrycode' => $pakjeGemakAddress->getCountryId(),
        );

        return $data;
    }

    /**
     * Get Parcelware reference data for this shipment
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
     * Gets product options based on a specified shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     *
     * @return array
     */
    protected function _getProductOptions(TIG_PostnL_Model_Core_Shipment $postnlShipment)
    {
        $postnlOrder = $postnlShipment->getPostnlOrder();
        if (!$postnlOrder) {
            return array('', '');
        }

        $type = $postnlOrder->getType();
        $availableProductOptions = $this->getAvailableProductOptions();
        if (!array_key_exists($type, $availableProductOptions)) {
            return array('', '');
        }

        $option = $availableProductOptions[$type];

        $productOptions = array(
            'Characteristic' => $option['Characteristic'],
            'Option'         => $option['Option'],

        );

        return $productOptions;
    }

    /**
     * Gets the product code for the specified shipment. If the product code is an EPS combilabel code, return it's
     * regular counterpart instead.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     *
     * @return int|mixed
     */
    protected function _getParcelwareProductCode(TIG_PostnL_Model_Core_Shipment $postnlShipment)
    {
        $productCode = $postnlShipment->getProductCode();

        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $combiLabelProductCodes = $helper->getCombiLabelProductCodes();
        if (in_array($productCode, $combiLabelProductCodes)) {
            $productCode = array_search($productCode, $combiLabelProductCodes);
        }

        return $productCode;
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
        $shipmentType = $this->_getParcelwareShipmentType($postnlShipment);

        $globalPackData = array(
            'ShipmentType'           => $shipmentType,
            'HandleAsNonDeliverable' => 0,
            'Invoice'                => 0,
            'Certificate'            => 0,
            'License'                => 0,
            'Currency'               => 'EUR',
        );

        /**
         * Check if the shipment should be treated as abandoned when it can't be delivered or if it should be returned
         * to the sender.
         */
        if ($postnlShipment->getTreatAsAbandoned()) {
            $globalPackData['HandleAsNonDeliverable'] = 1;
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
            || ($globalPackData['License'] == 0
                && $globalPackData['Certificate'] == 0
            )
        ) {
            $shipmentId = $shipment->getIncrementId();
            $globalPackData['Invoice'] = $shipmentId;
        }

        /**
         * Add information about the contents of the shipment
         */
        $itemCount = 0;
        /** @noinspection PhpParamsInspection */
        $items = $this->_sortCustomsItems($shipment->getAllItems());

        /** @var TIG_PostNL_Helper_Data $helper */
        $helper = Mage::helper('postnl');

        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
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
            $itemWeight = $helper->standardizeWeight(
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
                'Product_' . $itemCount . '_HSTariffNr'      => (string) $this->_getHSTariff($item),
                'Product_' . $itemCount . '_CountryOfOrigin' => $this->_getCountryOfOrigin($item),
            );

            $globalPackData = array_merge($globalPackData, $itemData);
        }

        return $globalPackData;
    }

    /**
     * Gets the numeric shipment type for this shipment.
     *
     * @param TIG_PostnL_Model_Core_Shipment $postnlShipment
     *
     * @return string|int
     */
    protected function _getParcelwareShipmentType(TIG_PostnL_Model_Core_Shipment $postnlShipment)
    {
        $shipmentType = $postnlShipment->getGlobalpackShipmentType();
        /** @var TIG_PostNL_Helper_Cif $helper */
        $helper = Mage::helper('postnl/cif');
        $numericShipmentTypes = $helper->getNumericShipmentTypes();

        if (array_key_exists($shipmentType, $numericShipmentTypes)) {
            $shipmentType = $numericShipmentTypes[$shipmentType];
        }

        return $shipmentType;
    }

    /**
     * Gets CSV headers for the Parcelware export file.
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
            'CustomerEmail',
            'SMSNr',
            'PG_CompanyName',
            'PG_Street',
            'PG_HouseNr',
            'PG_HouseNrExt',
            'PG_Zipcode',
            'PG_City',
            'PG_Countrycode',
            'ProductCodeDelivery',
            'GroupSequence',
            'GroupCount',
            'ConfirmDate',
            'DeliveryDate',
            'Barcode',
            'MainBarcode',
            'ContractReference',
            'ContractName',
            'SenderReference',
            'InsuredAmount',
            'Characteristic',
            'Option',
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
     * Get a shipment item's HS tariff.
     *
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @return string
     *
     * @see TIG_PostNL_Model_Core_Cif::_getHSTariff()
     */
    protected function _getHSTariff(Mage_Sales_Model_Order_Shipment_Item $shipmentItem)
    {
        $hsTariff = parent::_getHSTariff($shipmentItem);
        if ($hsTariff == '000000') {
            return '';
        }

        return $hsTariff;
    }

    /**
     * Get the contract reference number
     *
     * @return string
     */
    protected function _getContractReference()
    {
        $contractReference = Mage::getStoreConfig(self::XPATH_CONTRACT_REF_NR, $this->getStoreId());

        return $contractReference;
    }

    /**
     * Get the contract name
     *
     * @return string
     */
    protected function _getContractName()
    {
        $contractName = Mage::getStoreConfig(self::XPATH_CONTRACT_NAME, $this->getStoreId());

        return $contractName;
    }

    /**
     * Get the sender address reference number
     *
     * @return string
     */
    protected function _getSenderReference()
    {
        $senderReference = Mage::getStoreConfig(self::XPATH_SENDER_REF_NR, $this->getStoreId());

        return $senderReference;
    }
}
