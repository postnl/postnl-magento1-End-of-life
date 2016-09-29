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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Install_V12Test extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    public function attributesProvider()
    {
        return array(
            array('postnl_idcheck_type'),
        );
    }

    /**
     * @param $attribute
     *
     * @throws Mage_Core_Exception
     * @dataProvider attributesProvider
     */
    public function testIfAttributesExists($attribute)
    {
        $attr = Mage::getResourceModel('catalog/eav_attribute')->loadByCode('catalog_product', $attribute);

        $this->assertNotNull($attr->getId(), 'Check that the attribute ' .$attribute . ' does exists');
    }

    public function columnsProvider()
    {
        return array(
            array('postnl_core/order', 'idcheck_type', 'varchar'),
            array('postnl_core/order', 'idcheck_number', 'text'),
            array('postnl_core/order', 'idcheck_expiration_date', 'date'),
        );
    }

    /**
     * @dataProvider columnsProvider
     */
    public function testIfColumnsExists($model, $column, $type)
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName($model);
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $fields = $read->query('DESCRIBE `' . $tableName . '`')->fetchAll();

        foreach ($fields as $field) {
            if ($field['Field'] == $column) {
                $this->assertNotFalse(strpos($field['Type'], $type));
                return $this;
            }
        }

        $this->fail('Column ' . $column . ' not found in ' . $tableName . ' (' . $model . ')');
    }
}