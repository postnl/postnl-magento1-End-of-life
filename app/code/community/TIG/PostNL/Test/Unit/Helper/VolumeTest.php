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
class TIG_PostNL_Test_Unit_Helper_VolumeTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_Volume
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/volume');
    }

    public function dataProvider()
    {
        return array(
            'Extra@Home enabled, item qty 1' => array(
                true,
                array(
                    array(
                        'sku' => 'test1',
                        'postnl_product_volume' => 50000,
                        'qty' => 1,
                        TIG_PostNL_Helper_Volume::ATTRIBUTE_CODE_PRODUCT_TYPE =>
                            TIG_PostNL_Helper_DeliveryOptions::EXTRA_AT_HOME_TYPE_REGULAR
                    )
                ),
                50000
            ),
            'Extra@Home disabled, item qty 1' => array(
                false,
                array(
                    array(
                        'sku' => 'test1',
                        'postnl_product_volume' => 50000,
                        'qty' => 1,
                        TIG_PostNL_Helper_Volume::ATTRIBUTE_CODE_PRODUCT_TYPE =>
                            TIG_PostNL_Helper_DeliveryOptions::EXTRA_AT_HOME_TYPE_REGULAR
                    )
                ),
                0
            ),
            'Extra@Home enabled, item qty 2' => array(
                true,
                array(
                    array(
                        'sku' => 'test1',
                        'postnl_product_volume' => 50000,
                        'qty' => 2,
                        TIG_PostNL_Helper_Volume::ATTRIBUTE_CODE_PRODUCT_TYPE =>
                            TIG_PostNL_Helper_DeliveryOptions::EXTRA_AT_HOME_TYPE_REGULAR
                    )
                ),
                100000
            )
        );
    }

    public function exceptionDataProvider()
    {
        return array(
            'Extra@Home enabled but no volume, item qty 1' => array(
                true,
                array(
                    array(
                        'sku' => 'test1',
                        'postnl_product_volume' => 0,
                        'qty' => 1,
                        TIG_PostNL_Helper_Volume::ATTRIBUTE_CODE_PRODUCT_TYPE =>
                            TIG_PostNL_Helper_DeliveryOptions::EXTRA_AT_HOME_TYPE_REGULAR
                    )
                ),
                'Warning : To successfully send orders with Extra@Home, you must fill
                in the product attributes name, weight and volume.'
            )
        );
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $extraAtHomeActive
     * @param $items
     * @param $expected
     */
    public function testGet($extraAtHomeActive, $items, $expected)
    {
        $productDictonaryMock = $this->getMock('TIG_PostNL_Helper_ProductDictionary');
        $productDictonaryMock->method('get')->willReturn($this->getProducts($items));

        $instance = $this->_getInstance();
        $this->setProperty('extraAtHomeEnabled', $extraAtHomeActive, $instance);
        $this->setProperty('productDictonary', $productDictonaryMock, $instance);

        $result = $instance->get($this->getProducts($items));

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider exceptionDataProvider
     *
     * @param $extraAtHomeActive
     * @param $items
     * @param $message
     */
    public function testGetException($extraAtHomeActive, $items, $message)
    {
        $productDictonaryMock = $this->getMock('TIG_PostNL_Helper_ProductDictionary');
        $productDictonaryMock->method('get')->willReturn($this->getProducts($items));

        $instance = $this->_getInstance();
        $this->setProperty('extraAtHomeEnabled', $extraAtHomeActive, $instance);
        $this->setProperty('productDictonary', $productDictonaryMock, $instance);

        $this->setExpectedException('TIG_PostNL_Exception', $message);

        $instance->get($this->getProducts($items));
    }

    /**
     * @param $items
     *
     * @return array
     */
    public function getProducts($items)
    {
        $products = array();

        foreach ($items as $item) {
            $product = new Varien_Object();
            $product->setData($item);
            $products[$product->getData('sku')] = $product;
        }

        return $products;
    }
}
