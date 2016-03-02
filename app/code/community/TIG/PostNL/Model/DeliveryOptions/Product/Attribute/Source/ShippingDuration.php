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
 */
class TIG_PostNL_Model_DeliveryOptions_Product_Attribute_Source_ShippingDuration
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options) {
            return $this->_options;
        }

        $helper = Mage::helper('postnl');

        $options = array(
            array(
                'label' => $helper->__('Use configuration value'),
                'value' => '-1'
            ),
            array(
                'value' => 0,
                'label' => '0 ' . $helper->__('days'),
            ),
            array(
                'value' => 1,
                'label' => '1 ' . $helper->__('day'),
            ),
            array(
                'value' => 2,
                'label' => '2 ' . $helper->__('days'),
            ),
            array(
                'value' => 3,
                'label' => '3 ' . $helper->__('days'),
            ),
            array(
                'value' => 4,
                'label' => '4 ' . $helper->__('days'),
            ),
            array(
                'value' => 5,
                'label' => '5 ' . $helper->__('days'),
            ),
            array(
                'value' => 6,
                'label' => '6 ' . $helper->__('days'),
            ),
            array(
                'value' => 7,
                'label' => '7 ' . $helper->__('days'),
            ),
            array(
                'value' => 8,
                'label' => '8 ' . $helper->__('days'),
            ),
            array(
                'value' => 9,
                'label' => '9 ' . $helper->__('days'),
            ),
            array(
                'value' => 10,
                'label' => '10 ' . $helper->__('days'),
            ),
            array(
                'value' => 11,
                'label' => '11 ' . $helper->__('days'),
            ),
            array(
                'value' => 12,
                'label' => '12 ' . $helper->__('days'),
            ),
            array(
                'value' => 13,
                'label' => '13 ' . $helper->__('days'),
            ),
            array(
                'value' => 14,
                'label' => '14 ' . $helper->__('days'),
            ),
        );

        $this->_options = $options;
        return $options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'default'   => null,
            'extra'     => null,
            'type'      => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            'is_null'   => true,
            'comment'   => $attributeCode . ' column',
        );

        $columnDefinition = array($attributeCode => $column);
        return $columnDefinition;
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array($this->getAttribute()->getAttributeCode())
        );

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        $select = Mage::getResourceModel('eav/entity_attribute')
                      ->getFlatUpdateSelect($this->getAttribute(), $store);

        return $select;
    }
}
