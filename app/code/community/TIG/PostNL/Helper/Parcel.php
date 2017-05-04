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
class TIG_PostNL_Helper_Parcel extends Mage_Core_Helper_Abstract
{
    /**
     * Xpaths to PostNL product attributes.
     */
    const ATTRIBUTE_CODE_PRODUCT_TYPE = 'postnl_product_type';
    const ATTRIBUTE_PARCEL_COUNT = 'postnl_product_parcel_count';

    /**
     * Xpath to weight per parcel config setting.
     */
    const XPATH_WEIGHT_PER_PARCEL = 'postnl/packing_slip/weight_per_parcel';

    /**
     * Product types.
     */
    const PRODUCT_TYPE_NON_FOOD       = '0';
    const PRODUCT_TYPE_DRY_GROCERIES  = '1';
    const PRODUCT_TYPE_COOL_PRODUCTS  = '2';
    const PRODUCT_TYPE_AGE_CHECK      = '3';
    const PRODUCT_TYPE_BIRTHDAY_CHECK = '4';
    const PRODUCT_TYPE_ID_CHECK       = '5';
    const PRODUCT_TYPE_EXTRA_AT_HOME  = '6';

    /**
     * Gets the number of parcels in this shipment
     * based on it's weight and the configured parcel count of each product.
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment $shipment
     *
     * @param bool|array                                             $productList
     *
     * @return int
     */
    public function calculateParcelCount($shipment,$productList = false)
    {
        /**
         * @var TIG_PostNL_Helper_Cif $cifHelper
         */
        $cifHelper = Mage::helper('postnl/cif');

        /**
         * Only Dutch shipments that are not COD support multi-colli shipments.
         */
        if ($shipment->getShippingAddress()->getCountryId() != 'NL' || $cifHelper->isCodShipment($shipment)) {
            return 1;
        }

        /**
         * Get the weight per parcel.
         *
         * @var TIG_PostNL_Helper_Cif $helper
         */
        $weightPerParcel = Mage::getStoreConfig(
            self::XPATH_WEIGHT_PER_PARCEL, $shipment->getStoreId()
        );

        /**
         * Get all items in the shipment.
         */
        $items = $shipment->getAllItems();

        /**
         * Calculate the total configured parcel count and the remaining weight.
         */
        $parcelCount                 = 0;
        $remainingWeight             = 0;
        $hasProductsWithoutOwnParcel = false;

        if(!$productList) {
            $productList = $this->getProductDictionary(
                $items, array(self::ATTRIBUTE_CODE_PRODUCT_TYPE,
                              self::ATTRIBUTE_PARCEL_COUNT)
            );
        }
        /**
         * @var Mage_Sales_Model_Order_Shipment_Item $item
         */
        foreach ($items as $item) {
            /**
             * If the product does not exists in the products list its not a simple type.
             */
            if (!array_key_exists($item->getSku(), $productList)) {
                continue;
            }
            $product = $productList[$item->getSku()];
            unset($productList[$item->getSku()]);

            $productType        = $product->getData(self::ATTRIBUTE_CODE_PRODUCT_TYPE);
            $isAtHomeProduct    = $productType == self::PRODUCT_TYPE_EXTRA_AT_HOME;
            $productParcelCount = $product->getData(self::ATTRIBUTE_PARCEL_COUNT);

            if ($isAtHomeProduct) {
                $parcelCount += ($productParcelCount * $item->getQty());
            } else {
                $remainingWeight += ($item->getWeight() * $item->getQty());
                $hasProductsWithoutOwnParcel = true;
            }
        }

        /**
         * Calculate the remaining parcel count.
         */
        $remainingParcelCount = ceil($remainingWeight / $weightPerParcel);
        if ($remainingParcelCount < 1 && $hasProductsWithoutOwnParcel) {
            $remainingParcelCount = 1;
        }
        $parcelCount += $remainingParcelCount;

        return $parcelCount;
    }

    /**
     * Creates a dictionary of all simple products in the list based on SKU.
     *
     * @param array $items
     * @param array $attributesToSelect Additional custom attributes to add to the select.
     *
     * @return array
     */
    protected function getProductDictionary($items,$attributesToSelect){
        $productSkus = array_map(
            function ($item) {
                return $item->getSku();
            }, $items
        );
        $products   = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(
                $attributesToSelect
            )
            ->addFieldToFilter('sku', array('in' => $productSkus))
            ->addAttributeToFilter(
                'type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            );

        $productList = array();
        foreach ($products as $product) {
            $productList[$product->getSku()] = $product;
        }
        return $productList;
    }
}