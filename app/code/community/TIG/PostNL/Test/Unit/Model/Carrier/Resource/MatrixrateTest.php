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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Model_Carrier_Resource_MatrixrateTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Carrier_Resource_Matrixrate
     */
    protected function getInstance()
    {
        return Mage::getResourceModel('postnl_carrier/matrixrate');
    }

    public function uploadAndImportProvider()
    {
        $path = realpath(dirname(__FILE__) . '/../../../../Fixtures/Matrixrate');

        $output = array();
        $files = glob($path . '/*.csv');
        foreach ($files as $file) {
            $output[] = array(
                $file,
                str_replace('.csv', '', basename($file)),
            );
        }

        return $output;
    }

    /**
     * @param $file
     * @param $type
     *
     * @throws Exception
     * @dataProvider uploadAndImportProvider
     */
    public function testUploadAndImport($file, $type)
    {
        // Delete all existing records
        /** @var TIG_PostNL_Model_Carrier_Resource_Matrixrate_Collection $collection */
        $collection = Mage::getModel('postnl_carrier/matrixrate')->getCollection();
        foreach ($collection as $model) {
            $model->delete();
        }

        $_FILES['groups']['tmp_name']['postnl']['fields']['matrix_import']['value'] = $file;

        $object = new Varien_Object;
        $object->setData('scope_id', 1);

        $instance = $this->getInstance();
        $instance->uploadAndImport($object);

        /** @var TIG_PostNL_Model_Carrier_Resource_Matrixrate $collection */
        $collection = Mage::getModel('postnl_carrier/matrixrate')->getCollection();
        $collection->addFieldToFilter('parcel_type', $type);

        $fileContents = file_get_contents($file);
        $lines = count(explode("\n", trim($fileContents))) - 1; // Don't count the header
        $count = $collection->count();
        $this->assertEquals($lines, $count, 'Expect that the file ' . $type . ' has ' . $lines . ' lines');
    }
}
