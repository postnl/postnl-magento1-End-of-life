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
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     *
     * @throws Mage_Core_Exception
     *
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['postnl']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['postnl']['fields']['import']['value'];
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
        if (isset($this->_importIso2Countries[$row[0]])) {
            $countryId = $this->_importIso2Countries[$row[0]];
        } elseif (isset($this->_importIso3Countries[$row[0]])) {
            $countryId = $this->_importIso3Countries[$row[0]];
        } elseif ($row[0] == '*' || $row[0] == '') {
            $countryId = '0';
        } else {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid country "%s" in row #%s.',
                $row[0],
                $rowNumber
            );
            return false;
        }

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
        if ($row[2] == '*' || $row[2] == '') {
            $zipCode = '*';
        } else {
            $zipCode = $row[2];
        }

        // validate condition value
        $value = $this->_parseDecimalValue($row[3]);
        if ($value === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid %s "%s" in the Row #%s.',
                $this->_getConditionFullName($this->_importConditionName), $row[3], $rowNumber
            );
            return false;
        }

        // validate price
        $price = $this->_parseDecimalValue($row[4]);
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Invalid shipping price "%s" in row #%s.',
                $row[4],
                $rowNumber
            );
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%s-%d-%s-%F", $countryId, $regionId, $zipCode, $value);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('postnl')->__(
                'Duplicate row #%s (country "%s", region/state "%s", zip "%s" and value "%s").',
                $rowNumber,
                $row[0],
                $row[1],
                $zipCode,
                $value
            );
            return false;
        }
        $this->_importUniqueHash[$hash] = true;

        return array(
            $this->_importWebsiteId,    // website_id
            $countryId,                 // dest_country_id
            $regionId,                  // dest_region_id,
            $zipCode,                   // dest_zip
            $this->_importConditionName,// condition_name,
            $value,                     // condition_value
            $price                      // price
        );
    }
}