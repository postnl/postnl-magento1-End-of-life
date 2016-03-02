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
class TIG_PostNL_Model_Resource_Catalog_Product extends Mage_Catalog_Model_Resource_Product
{
    /**
     * Retrieve attribute's raw value from DB.
     *
     * Unlike it's parent method, this method will return the actual raw value. Meaning a value which would be
     * considered empty will be returned as well.
     *
     * @param int                       $entityId
     * @param int|string|array          $attribute Attribute ids or codes
     * @param int|Mage_Core_Model_Store $store
     *
     * @return false|string|null
     */
    public function getAttributeRawValue($entityId, $attribute, $store)
    {
        if (!$entityId || empty($attribute)) {
            return false;
        }
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $attributesData     = array();
        $staticAttributes   = array();
        $typedAttributes    = array();
        $staticTable        = null;
        $adapter            = $this->_getReadAdapter();

        foreach ($attribute as $_attribute) {
            /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
            $_attribute = $this->getAttribute($_attribute);
            if (!$_attribute) {
                continue;
            }
            $attributeCode = $_attribute->getAttributeCode();
            $attrTable     = $_attribute->getBackend()->getTable();
            $isStatic      = $_attribute->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attributeCode;
                $staticTable = $attrTable;
            } else {
                /**
                 * That structure needed to avoid farther sql joins for getting attribute's code by id
                 */
                $typedAttributes[$attrTable][$_attribute->getId()] = $attributeCode;
            }
        }

        /**
         * Collecting static attributes
         */
        if ($staticAttributes) {
            $select = $adapter->select()->from($staticTable, $staticAttributes)
                              ->where($this->getEntityIdField() . ' = :entity_id');
            $attributesData = $adapter->fetchRow($select, array('entity_id' => $entityId));
        }

        /**
         * Collecting typed attributes, performing separate SQL query for each attribute type table
         */
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }

        $store = (int)$store;
        if ($typedAttributes) {
            foreach ($typedAttributes as $table => $_attributes) {
                $select = $adapter->select()
                                  ->from(array('default_value' => $table), array('attribute_id'))
                                  ->where('default_value.attribute_id IN (?)', array_keys($_attributes))
                                  ->where('default_value.entity_type_id = :entity_type_id')
                                  ->where('default_value.entity_id = :entity_id')
                                  ->where('default_value.store_id = ?', 0);
                $bind = array(
                    'entity_type_id' => $this->getTypeId(),
                    'entity_id'      => $entityId,
                );

                if ($store != $this->getDefaultStoreId()) {
                    $valueExpr = $adapter->getCheckSql('store_value.value IS NULL',
                        'default_value.value', 'store_value.value');
                    $joinCondition = array(
                        $adapter->quoteInto('store_value.attribute_id IN (?)', array_keys($_attributes)),
                        'store_value.entity_type_id = :entity_type_id',
                        'store_value.entity_id = :entity_id',
                        'store_value.store_id = :store_id',
                    );

                    $select->joinLeft(
                           array('store_value' => $table),
                               implode(' AND ', $joinCondition),
                               array('attr_value' => $valueExpr)
                    );

                    $bind['store_id'] = $store;

                } else {
                    $select->columns(array('attr_value' => 'value'), 'default_value');
                }

                $result = $adapter->fetchPairs($select, $bind);
                foreach ($result as $attrId => $value) {
                    $attrCode = $typedAttributes[$table][$attrId];
                    $attributesData[$attrCode] = $value;
                }
            }
        }

        if (sizeof($attributesData) == 1) {
            $_data = each($attributesData);
            $attributesData = $_data[1];
        }

        /**
         * An empty array just means that there is no row for this entity and attribute.
         */
        if (is_array($attributesData) && empty($attributesData)) {
            return false;
        }

        return $attributesData;
    }
}
