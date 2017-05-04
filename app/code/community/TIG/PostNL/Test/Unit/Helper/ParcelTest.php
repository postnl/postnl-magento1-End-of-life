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
class TIG_PostNL_Test_Unit_Helper_ParcelTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_Parcel
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/parcel');
    }

    public function calculateParcelCountDataProvider(){
        return array(
            'domestic_cod_2ConfiguredParcels'=>
                array('NL',true,array(
                    array(
                        'sku'=>'test1',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_EXTRA_AT_HOME,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>1,
                    ),
                    array(
                        'sku'=>'test2',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_EXTRA_AT_HOME,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>1,
                    ),
                ),1),

            'domestic_notCod_2ConfiguredParcels'=>
                array('NL',false,array(
                    array(
                        'sku'=>'test1',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_EXTRA_AT_HOME,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>1,
                    ),
                    array(
                        'sku'=>'test2',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_EXTRA_AT_HOME,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>1,
                    ),
                ),2),

            'domestic_notCod_2ConfiguredParcels_notExtraAtHome'=>
                array('NL',false,array(
                    array(
                        'sku'=>'test1',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>0,
                    ),
                    array(
                        'sku'=>'test2',
                        'weight'=>1,
                        'qty'=>1,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>12,
                    ),
                ),1),

            'domestic_notCod_2ConfiguredParcels_notExtraAtHome'=>
                array('NL',false,array(
                    array(
                        'sku'=>'test1',
                        'weight'=>10,
                        'qty'=>50,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>0,
                    ),
                    array(
                        'sku'=>'test2',
                        'weight'=>11,
                        'qty'=>50,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>12,
                    ),
                ),1),

            'domestic_notCod_2ConfiguredParcels_notExtraAtHome_heighWeight'=>
                array('NL',false,array(
                    array(
                        'sku'=>'test1',
                        'weight'=>200,
                        'qty'=>50,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>0,
                    ),
                    array(
                        'sku'=>'test2',
                        'weight'=>210,
                        'qty'=>50,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_CODE_PRODUCT_TYPE=>
                            TIG_PostNL_Helper_Parcel::PRODUCT_TYPE_ID_CHECK,
                        TIG_PostNL_Helper_Parcel::ATTRIBUTE_PARCEL_COUNT=>12,
                    ),
                ),2),
        );
    }

    /**
     *
     * @dataProvider calculateParcelCountDataProvider
     *
     * @param string $countryCode
     * @param bool $isCod
     * @param array $orderItems
     * @param int $expectedParcelCount
     */
    public function testCalculateParcelCount(
        $countryCode, $isCod, $orderItems, $expectedParcelCount
    ) {
        /**
         * Mock the Magento shipment to overwrite the products.
         *
         * @var Mage_Sales_Model_Order_Shipment $shipmentMock
         */
        $products = array();
        foreach ($orderItems as $orderItem) {
            $product =  new Varien_Object();
            $product->setData($orderItem);
            $products[$product->getSku()] = $product;
        }

        $shipmentMock = $this->getMock('Mage_Sales_Model_Order_Shipment');
        $shipmentMock->method('getAllItems')->willReturn($products);

        /**
         * Set shipping address to overwrite the destination country code.
         *
         * @var Mage_Sales_Model_Order_Address $shippingAddress
         */
        $shippingAddress = new Mage_Sales_Model_Order_Address;
        $shippingAddress->setCountryId($countryCode);
        $shipmentMock->method('getShippingAddress')->willReturn($shippingAddress);

        /**
         *
         * @var Mage_Sales_Model_Order_Payment $payment
         */
        $payment = new Mage_Sales_Model_Order_Payment;
        $payment->setMethod($isCod?'postnl_cod':'postnl_notcod');
        $shipmentMock->setPayment($payment);
        $shipmentMock->method('getPayment')->willReturn($payment);

        /**
         * @var Mage_Sales_Model_Order $orderMock
         */
        $orderMock = $this->getMock('Mage_Sales_Model_Order');
        $orderMock->method('getPayment')->willReturn($payment);
        $shipmentMock->method('getOrder')->willReturn($orderMock);

        $result = $this->_getInstance()->calculateParcelCount(
            $shipmentMock, $products
        );

        $this->assertequals($expectedParcelCount, $result);
    }
}
