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
class TIG_PostNL_Helper_Volume extends Mage_Core_Helper_Abstract
{
    const ATTRIBUTE_VOLUME = 'postnl_product_volume';
    const ATTRIBUTE_CODE_PRODUCT_TYPE = 'postnl_product_type';

    /**
     * @var bool
     */
    protected $extraAtHomeEnabled;

    /**
     * @var TIG_PostNL_Helper_ProductDictionary
     */
    protected $productDictonary;

    /**
     * TIG_PostNL_Helper_Volume constructor.
     */
    public function __construct()
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptions */
        $deliveryOptions  = Mage::helper('postnl/deliveryOptions');
        $this->extraAtHomeEnabled = $deliveryOptions->canUseExtraAtHomeDelivery(false);
        $this->productDictonary = Mage::helper('postnl/productDictionary');
    }

    /**
     * @param $items
     *
     * @return int
     * @throws TIG_PostNL_Exception
     */
    public function get($items)
    {
        $productList = $this->productDictonary->get($items, array(
            self::ATTRIBUTE_CODE_PRODUCT_TYPE, self::ATTRIBUTE_VOLUME
        ));

        if (empty($productList) || !$this->extraAtHomeEnabled) {
            return 0;
        }

        $volume = 0;
        /** @var Mage_Sales_Model_Order_Shipment_Item $item */
        foreach ($items as $item) {
            $volume += $this->getVolume($productList, $item);
            unset($productList[$item->getSku()]);
        }

        if ($volume < 1) {
            throw new TIG_PostNL_Exception(
                Mage::helper('postnl')->__('Warning : To successfully send orders with Extra@Home, you must fill
                in the product attributes name, weight and volume.'),
                'POSTNL-0231'
            );
        }

        return $volume;
    }

    /**
     * @param $productList
     * @param Mage_Sales_Model_Order_Shipment_Item $item
     *
     * @return int
     */
    protected function getVolume($productList, $item)
    {
        if (!isset($productList[$item->getSku()])) {
            return 0;
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = $productList[$item->getSku()];
        $type    = $product->getData(self::ATTRIBUTE_CODE_PRODUCT_TYPE);

        if ($type != TIG_PostNL_Helper_DeliveryOptions::EXTRA_AT_HOME_TYPE_REGULAR) {
            return 0;
        }

        $volume = $product->getData(self::ATTRIBUTE_VOLUME);
        return $volume * $this->getQty($item);
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment_Item $item
     *
     * @return mixed
     */
    protected function getQty($item)
    {
        return $item->getQty() ?: $item->getQtyOrdered();
    }
}
