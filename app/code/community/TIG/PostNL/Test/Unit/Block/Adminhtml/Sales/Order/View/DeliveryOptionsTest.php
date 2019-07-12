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
class TIG_PostNL_Test_Unit_Block_Adminhtml_Sales_Order_View_DeliveryOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions
     */
    public function _getInstance()
    {
        return new TIG_PostNL_Block_Adminhtml_Sales_Order_View_DeliveryOptions();
    }

    /**
     * @return array
     */
    public function getShipmentTypeProvider()
    {
        return array(
            'pa'                           => array('PA', 'NL', null, 'Parcel Dispenser'),
            'pge'                          => array('PGE', 'NL', null, 'Post Office'),
            'pg'                           => array('PG', 'NL', null, 'Post Office'),
            'avond'                        => array('Avond', 'NL', null, 'Domestic'),
            'sunday'                       => array('Sunday', 'NL', null, 'Sunday Delivery'),
            'monday'                       => array('Monday', 'NL', null, 'Monday Delivery'),
            'sameday'                      => array('Sameday', 'NL', null, 'Same Day Delivery'),
            'overdag'                      => array('Overdag', 'NL', null, 'Domestic'),
            'food'                         => array('Food', 'NL', null, 'Food Delivery'),
            'cooled'                       => array('Cooledfood', 'NL', null, 'Cooled Food Delivery'),
            'agecheck'                     => array('AgeCheck', 'NL', null, 'Age Check'),
            'birthdaycheck'                => array('BirthdayCheck', 'NL', null, 'Birthday Check'),
            'idcheck'                      => array('IDCheck', 'NL', null, 'ID Check'),
            'extra_at_home'                => array('ExtraAtHome', 'NL', null, 'Extra@Home'),
            'domestic by domestic country' => array('invalidType', 'NL', false, 'Domestic'),
            'domestic by dutch products'   => array('invalidType', 'NL', true, 'Domestic'),
            'eps'                          => array('invalidType', 'BE', false, 'EU'),
            'globalpack'                   => array('invalidType', 'USA', false, 'Non-EU'),
        );
    }

    /**
     * @param $shipmentType
     * @param $countryId
     * @param $useDutchProducts
     * @param $expected
     *
     * @dataProvider getShipmentTypeProvider
     */
    public function testGetShipmentType($shipmentType, $countryId, $useDutchProducts, $expected)
    {
        $this->setRegistryKey('can_use_dutch_products', $useDutchProducts);

        $shippingAddressMock = $this->getMockBuilder('Mage_Sales_Model_Order_Address')
            ->setMethods(array('getCountryId'))
            ->getMock();
        $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn($countryId);

        $orderMock = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->setMethods(array('getPayment', 'getMethod', 'getShippingAddress'))
            ->getMock();
        $orderMock->expects($this->once())->method('getPayment')->willReturnSelf();
        $orderMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddressMock);

        $postnlOrderMock = $this->getMockBuilder('TIG_PostNL_Model_Core_Order')
            ->setMethods(array('getType'))
            ->getMock();
        $postnlOrderMock->expects($this->once())->method('getType')->willReturn($shipmentType);

        $instance = $this->_getInstance();
        $instance->setOrder($orderMock);
        $instance->setPostnlOrder($postnlOrderMock);

        $result = $instance->getShipmentType();
        $this->assertEquals($expected, $result);
    }
}
