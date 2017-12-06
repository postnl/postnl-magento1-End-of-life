<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_ProductDictionary extends Mage_Core_Helper_Abstract
{
    /**
     * Creates a dictionary of all simple products in the list based on SKU.
     *
     * @param array $items
     * @param array $attributesToSelect Additional custom attributes to add to the select.
     *
     * @return Mage_Catalog_Model_Product[] array
     */
    public function get($items, $attributesToSelect)
    {
        $productSkus = array_map(function ($item) {
            return $item->getSku();
        }, $items);

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(
                $attributesToSelect
            )
            ->addFieldToFilter('sku', array('in' => $productSkus))
            ->addAttributeToFilter(
                'type_id',
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            );

        $productList = array();
        foreach ($products as $product) {
            $productList[$product->getSku()] = $product;
        }

        return $productList;
    }
}