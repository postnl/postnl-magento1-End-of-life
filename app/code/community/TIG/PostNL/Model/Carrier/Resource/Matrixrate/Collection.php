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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Carrier_Resource_Matrixrate_Collection
    extends Mage_Shipping_Model_Resource_Carrier_Tablerate_Collection
{
    /**
     * Define resource model and item.
     */
    protected function _construct()
    {
        $this->_init('postnl_carrier/matrixrate');
        $this->_shipTable       = $this->getMainTable();
        $this->_countryTable    = $this->getTable('directory/country');
        $this->_regionTable     = $this->getTable('directory/country_region');
    }

    /**
     * Initialize select, add country iso3 code and region name, and define default sorting.
     */
    public function _initSelect()
    {
        Mage_Core_Model_Resource_Db_Collection_Abstract::_initSelect();

        $this->_select->joinLeft(
            array('region_table' => $this->_regionTable),
            'region_table.region_id = main_table.dest_region_id',
            array('dest_region' => 'code')
        );

        $this->addOrder('dest_country_id', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region',     self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip',        self::SORT_ORDER_ASC);
        $this->addOrder('weight',          self::SORT_ORDER_ASC);
        $this->addOrder('subtotal',        self::SORT_ORDER_ASC);
        $this->addOrder('qty',             self::SORT_ORDER_ASC);
    }
}