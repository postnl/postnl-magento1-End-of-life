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
class TIG_PostNL_Test_Unit_Helper_ContentDescriptionTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_ContentDescription
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/contentDescription');
    }

    public function dataProvider()
    {
        return array(
            'Description for one Product' => array(
                array(
                    array(
                        'sku'  => 'P1',
                        'name' => 'Product 1'
                    )
                ),
                'Product 1'
            ),
            'Description for products where there is a large title' => array(
                array(
                    array(
                        'sku'  => 'P1',
                        'name' => 'Product 1'
                    ),
                    array(
                        'sku'  => 'P2',
                        'name' => 'Product with a large title'
                    ),
                    array(
                        'sku'  => 'P3',
                        'name' => 'Product 3'
                    )
                ),
                'Product 1, Product with a large ...'
            ),
            'Description for two products' => array(
                array(
                    array(
                        'sku'  => 'P1',
                        'name' => 'Product 1',
                    ),
                    array(
                        'sku'  => 'P2',
                        'name' => 'Product 2'
                    )
                ),
                'Product 1, Product 2'
            )
        );
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $items
     * @param $expected
     */
    public function testGet($items, $expected)
    {
        $shipmentMock = $this->getMock('Mage_Sales_Model_Order_Shipment');
        $shipmentMock->method('getAllItems')->willReturn($this->getProducts($items));

        $instance = $this->_getInstance();
        $result = $instance->get($shipmentMock);

        $this->assertEquals($expected, $result);
    }

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