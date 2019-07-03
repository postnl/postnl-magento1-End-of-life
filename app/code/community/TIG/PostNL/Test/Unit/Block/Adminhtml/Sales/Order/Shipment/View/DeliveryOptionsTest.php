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

use TIG_PostNL_Model_Core_Shipment as PostNLShipment;

class TIG_PostNL_Test_Unit_Block_Adminhtml_Sales_Order_Shipment_View_DeliveryOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_DeliveryOptions
     */
    public function _getInstance()
    {
        return new TIG_PostNL_Block_Adminhtml_Sales_Order_Shipment_View_DeliveryOptions();
    }

    /**
     * @return array
     */
    public function getShipmentTypeProvider()
    {
        return array(
            'domestic'      => array(PostNLShipment::SHIPMENT_TYPE_DOMESTIC, 'Domestic'),
            'domestic_cod'  => array(PostNLShipment::SHIPMENT_TYPE_DOMESTIC_COD, 'Domestic'),
            'avond'         => array(PostNLShipment::SHIPMENT_TYPE_AVOND, 'Domestic'),
            'avond_cod'     => array(PostNLShipment::SHIPMENT_TYPE_AVOND_COD, 'Domestic'),
            'pg'            => array(PostNLShipment::SHIPMENT_TYPE_PG, 'Post Office'),
            'pg_cod'        => array(PostNLShipment::SHIPMENT_TYPE_PG_COD, 'Post Office'),
            'pge'           => array(PostNLShipment::SHIPMENT_TYPE_PGE, 'Post Office'),
            'pge_cod'       => array(PostNLShipment::SHIPMENT_TYPE_PGE_COD, 'Post Office'),
            'pa'            => array(PostNLShipment::SHIPMENT_TYPE_PA, 'Parcel Dispenser'),
            'eps'           => array(PostNLShipment::SHIPMENT_TYPE_EPS, 'EU'),
            'globalpack'    => array(PostNLShipment::SHIPMENT_TYPE_GLOBALPACK, 'Non-EU'),
            'buspakje'      => array(PostNLShipment::SHIPMENT_TYPE_BUSPAKJE, 'Letter Box Parcel'),
            'sunday'        => array(PostNLShipment::SHIPMENT_TYPE_SUNDAY, 'Sunday Delivery'),
            'monday'        => array(PostNLShipment::SHIPMENT_TYPE_MONDAY, 'Monday Delivery'),
            'sameday'       => array(PostNLShipment::SHIPMENT_TYPE_SAMEDAY, 'Same Day Delivery'),
            'food'          => array(PostNLShipment::SHIPMENT_TYPE_FOOD, 'Food Delivery'),
            'cooled'        => array(PostNLShipment::SHIPMENT_TYPE_COOLED, 'Cooled Food Delivery'),
            'agecheck'      => array(PostNLShipment::SHIPMENT_TYPE_AGECHECK, 'Age Check'),
            'birthdaycheck' => array(PostNLShipment::SHIPMENT_TYPE_BIRTHDAYCHECK, 'Birthday Check'),
            'idcheck'       => array(PostNLShipment::SHIPMENT_TYPE_IDCHECK, 'ID Check'),
            'extra_at_home' => array(PostNLShipment::SHIPMENT_TYPE_EXTRAATHOME, 'Extra@Home'),
        );
    }

    /**
     * @param $shipmentType
     * @param $expected
     *
     * @dataProvider getShipmentTypeProvider
     */
    public function testGetShipmentType($shipmentType, $expected)
    {
        $postnlShipmentMock = $this->getMockBuilder('TIG_PostNL_Model_Core_Shipment')
            ->setMethods(array('getShipmentType'))
            ->getMock();
        $postnlShipmentMock->expects($this->once())->method('getShipmentType')->willReturn($shipmentType);

        $instance = $this->_getInstance();
        $instance->setPostnlShipment($postnlShipmentMock);

        $result = $instance->getShipmentType();
        $this->assertEquals($expected, $result);
    }
}
