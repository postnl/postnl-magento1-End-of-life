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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Carrier_Resource_Matrixrate extends Mage_Shipping_Model_Resource_Carrier_Tablerate
{
    /**
     * Define main table and id field name.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('postnl_carrier/matrixrate', 'pk');
    }

    /**
     * Return table rate array or false by rate request.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array|false
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $adapter = $this->_getReadAdapter();

        /**
         * Get the bound values for the select conditions.
         */
        $bind = array(
            ':website_id'  => (int) $request->getWebsiteId(),
            ':country_id'  => "%{$request->getDestCountryId()}%",
            ':region_id'   => (int) $request->getDestRegionId(),
            ':postcode'    => $request->getDestPostcode(),
            ':weight'      => $request->getPackageWeight(),
            ':subtotal'    => $request->getBaseSubtotalInclTax(),
            ':qty'         => $request->getPackageQty(),
        );

        /**
         * Get the request's parcel type. This is 'regular' by default.
         *
         * If the request has specified a parcel type, use that. Otherwise if the request contains any items, get the
         * quote from the first item and check if the quote is a letter box parcel.
         */
        $parcelType = 'regular';
        if ($request->hasData('parcel_type')) {
            $parcelType = $request->getData('parcel_type');
        }

        $bind[':parcel_type'] = $parcelType;

        /**
         * Get the base select query.
         */
        $select = $adapter->select()
                          ->from($this->getMainTable())
                          ->where('(website_id = :website_id) OR (website_id = ?)', Mage_Core_Model_App::ADMIN_STORE_ID)
                          ->order(
                              array(
                                  'website_id DESC',
                                  'parcel_type DESC',
                                  'dest_country_id DESC',
                                  'dest_region_id DESC',
                                  'dest_zip DESC',
                                  'weight DESC',
                                  'subtotal DESC',
                                  'qty DESC',
                              )
                          )
                          ->limit(1);

        /**
         * Render destination condition.
         */
        $orWhere = '('
                 . implode(
                     ') OR (',
                     array(
                         "dest_country_id LIKE :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
                         "dest_country_id LIKE :country_id AND dest_region_id = :region_id AND dest_zip = ''",
                         /**
                          * Handle asterisk in dest_zip field.
                          */
                         "dest_country_id LIKE :country_id AND dest_region_id = :region_id AND dest_zip = '*'",
                         "dest_country_id LIKE :country_id AND dest_region_id = 0 AND dest_zip = '*'",
                         "dest_country_id = '0' AND dest_region_id = :region_id AND dest_zip = '*'",
                         "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = '*'",

                         "dest_country_id LIKE :country_id AND dest_region_id = 0 AND dest_zip = ''",
                         "dest_country_id LIKE :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
                         "dest_country_id LIKE :country_id AND dest_region_id = 0 AND dest_zip = '*'",
                     )
                 )
                 . ')';
        $select->where($orWhere);

        /**
         * Add PostNL matrix rate specific conditions.
         */
        $select->where('weight <= :weight');
        $select->where('subtotal <= :subtotal');
        $select->where('qty <= :qty');
        $select->where("(parcel_type = :parcel_type) OR (parcel_type = '*')");

        $result = $adapter->fetchRow($select, $bind);

        if (!$result) {
            return false;
        }

        /**
         * Normalize destination zip code.
         */
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        }

        $result['cost'] = 0;

        return $result;
    }

    /**
     * Upload matrix rate file and import data from it.
     *
     * @param Varien_Object $object
     *
     * @throws TIG_PostNL_Exception
     *
     * @return $this
     */
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['postnl']['fields']['matrix_import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['postnl']['fields']['matrix_import']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());

        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importUniqueHash    = array();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;

        $io   = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 8) {
            $io->streamClose();
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Invalid PostNL Matrix Rates File Format'),
                'POSTNL-0194'
            );
        }

        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 1;
            $importData = array();

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website ID
            $condition = array(
                'website_id = ?'     => $this->_importWebsiteId,
            );
            $adapter->delete($this->getMainTable(), $condition);

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();

            Mage::helper('postnl')->logException($e);
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('An error occurred while importing the matrix rates.'),
                'POSTNL-0195'
            );
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('postnl')->__(
                'File has not been imported. See the following list of errors: %s',
                implode(" \n", $this->_importErrors)
            );
            throw new TIG_PostNL_Exception($error, 'POSTNL-0196');
        }

        return $this;
    }

    /**
     * Import matrix rate data from an array.
     *
     * @param array $data
     *
     * @return $this
     *
     * @throws Mage_Core_Exception
     * @throws TIG_PostNL_Exception
     */
    public function import(array $data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 1;
            $importData = array();

            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();

            // delete old data by website ID
            $condition = array(
                'website_id = ?'     => $this->_importWebsiteId,
            );
            $adapter->delete($this->getMainTable(), $condition);

            foreach ($data as $key => $line) {
                $rowNumber ++;

                if (empty($line)) {
                    continue;
                }

                $row = $this->_getImportRow($line, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }

            $this->_saveImportData($importData);
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();

            Mage::helper('postnl')->logException($e);
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('An error occurred while importing the matrix rates.'),
                'POSTNL-0195'
            );
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('postnl')->__(
                'Data has not been imported. See the following list of errors: %s',
                implode(" \n", $this->_importErrors)
            );
            throw new TIG_PostNL_Exception($error, 'POSTNL-0199');
        }

        return $this;
    }

    /**
     * Validate row for import and return table rate array or false.
     *
     * Error will be add to _importErrors array.
     *
     * @param array $row
     * @param int   $rowNumber
     *
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 8) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid PostNL matrix rates format in row #%s',
                $rowNumber
            );
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        // validate country
        $countries = explode(',', $row[0]);
        $countryIds = array();
        foreach ($countries as $country) {
            $country = trim($country);
            if (isset($this->_importIso2Countries[$country])) {
                $countryIds[] = $this->_importIso2Countries[$country];
            } elseif (isset($this->_importIso3Countries[$country])) {
                $countryIds[] = $this->_importIso3Countries[$country];
            } elseif ($country == '*' || $country == '') {
                $countryIds[] = '0';
            } else {
                $this->_importErrors[] = Mage::helper('postnl')->__(
                    'Invalid country "%s" in row #%s.',
                    $country,
                    $rowNumber
                );

                return false;
            }
        }
        $countryId = implode(',', $countryIds);

        // validate region
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$row[1]])) {
            $regionId = $this->_importRegions[$countryId][$row[1]];
        } elseif ($row[1] == '*' || $row[1] == '') {
            $regionId = 0;
        } else {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid region/state "%s" in row #%s.',
                $row[1],
                $rowNumber
            );
            return false;
        }

        // detect zip code
        if ($row[2] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[2];
        }

        // validate weight
        $weight = $this->_parseDecimalValue($row[3]);
        if ($weight === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid weight "%s" in row #%s.',
                $row[3],
                $rowNumber
            );
            return false;
        }

        // validate subtotal
        $subtotal = $this->_parseDecimalValue($row[4]);
        if ($subtotal === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid subtotal "%s" in row #%s.',
                $row[4],
                $rowNumber
            );
            return false;
        }

        // validate qty
        $qty = $this->_parseIntegerValue($row[5]);
        if ($qty === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid quantity "%s" in row #%s.',
                $row[5],
                $rowNumber
            );
            return false;
        }

        // validate parcel type
        $parcelType = $this->_importParcelType($row[6]);
        if (!$parcelType) {
            $allowedParcelTypes = array(
                '*',
                'letter_box',
                'regular'
            );

            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid parcel type "%s" in row #%s. Valid values are: "%s".',
                $row[6],
                $rowNumber,
                implode('", "', $allowedParcelTypes)
            );
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[7]);
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid shipping price "%s" in row #%s.',
                $row[4],
                $rowNumber
            );
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F-%F-%d-%s", $countryId, $regionId, $zipCode, $weight, $subtotal, $qty, $parcelType);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Duplicate row #%s (country "%s", region/state "%s", zip "%s", weight "%s", subtotal "%s", quantity ' .
                '"%s" and parcel type "%s").',
                $rowNumber,
                $row[0],
                $row[1],
                $zipCode,
                $row[3],
                $row[4],
                $row[5],
                $row[6]
            );
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array(
            $this->_importWebsiteId, // website_id
            $countryId,              // dest_country_id
            $regionId,               // dest_region_id,
            $zipCode,                // dest_zip
            $weight,                 // weight,
            $subtotal,               // subtotal
            $qty,                    // quantity
            $parcelType,             // parcel type
            $price                   // price
        );
    }

    /**
     * Parse and validate positive integer value.
     *
     * Return false if value is not decimal or is not positive.
     *
     * @param string $value
     *
     * @return bool|int
     */
    protected function _parseintegerValue($value)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $value = (int) $value;

        if ($value < 0) {
            return false;
        }

        return $value;
    }

    /**
     * Save import data batch.
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array(
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_zip',
                'weight',
                'subtotal',
                'qty',
                'parcel_type',
                'price',
            );
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Import the parcel type column.
     *
     * @param $parcelType
     *
     * @return string|false
     */
    protected function _importParcelType($parcelType)
    {
        $formattedType = false;
        switch ($parcelType) {
            case '':                 //no break
            case '0':                //no break
            case '*':
                $formattedType = '*';
                break;
            case 'letter_box':       //no break
            case 'letterbox':        //no break
            case 'buspakje':         //no break
            case 'bus_pakje':        //no break
            case 'brievenbuspakje':  //no break
            case 'brievenbus pakje': //no break
            case 'letterboxparcel':  //no break
            case 'letter box parcel':
                $formattedType = 'letter_box';
                break;
            case 'regular':          //no break
            case 'standaard':        //no break
            case 'pakket':           //no break
            case 'belpakje':         //no break
            case 'parcel':           //no break
            case 'package':
                $formattedType = 'regular';
                break;
            //no default
        }

        return $formattedType;
    }
}