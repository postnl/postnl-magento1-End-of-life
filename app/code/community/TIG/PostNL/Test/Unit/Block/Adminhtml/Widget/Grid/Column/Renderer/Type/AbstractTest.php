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
use TIG_PostNL_Block_Adminhtml_Widget_Grid_Column_Renderer_Type_Abstract as RendererTypeAbstract;

class TIG_PostNL_Test_Unit_Block_Adminhtml_Widget_Grid_Column_Renderer_Type_AbstractTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return RendererTypeAbstract
     */
    public function _getInstance()
    {
        return new RendererTypeAbstract();
    }

    /**
     * @return array
     */
    public function getShipmentTypeRenderedValueProvider()
    {
        return array(
            'domestic'   => array(PostNLShipment::SHIPMENT_TYPE_DOMESTIC, array('Domestic')),
            'domestic_cod' => array(PostNLShipment::SHIPMENT_TYPE_DOMESTIC_COD, array('Domestic', 'COD')),
            'avond'      => array(PostNLShipment::SHIPMENT_TYPE_AVOND, array('Domestic', 'Evening Delivery')),
            'avond_cod'  => array(PostNLShipment::SHIPMENT_TYPE_AVOND_COD, array('Domestic', 'Evening Delivery + COD')),
            'pg'         => array(PostNLShipment::SHIPMENT_TYPE_PG, array('Post Office')),
            'pg_cod'     => array(PostNLShipment::SHIPMENT_TYPE_PG_COD, array('Post Office', 'COD')),
            'pge'        => array(PostNLShipment::SHIPMENT_TYPE_PGE, array('Post Office', 'Early Pickup')),
            'pge_cod'    => array(PostNLShipment::SHIPMENT_TYPE_PGE_COD, array('Post Office', 'Early Pickup + COD')),
            'pa'         => array(PostNLShipment::SHIPMENT_TYPE_PA, array('Parcel Dispenser')),
            'eps'        => array(PostNLShipment::SHIPMENT_TYPE_EPS, array('EPS')),
            'globalpack' => array(PostNLShipment::SHIPMENT_TYPE_GLOBALPACK, array('GlobalPack')),
            'buspakje'   => array(PostNLShipment::SHIPMENT_TYPE_BUSPAKJE, array('Letter Box Parcel')),
            'sunday'     => array(PostNLShipment::SHIPMENT_TYPE_SUNDAY, array('Sunday Delivery')),
            'monday'     => array(PostNLShipment::SHIPMENT_TYPE_MONDAY, array('Monday Delivery')),
            'sameday'    => array(PostNLShipment::SHIPMENT_TYPE_SAMEDAY, array('Same Day Delivery')),
            'food'       => array(PostNLShipment::SHIPMENT_TYPE_FOOD, array('Food Delivery')),
            'cooled'     => array(PostNLShipment::SHIPMENT_TYPE_COOLED, array('Cooled Food Delivery')),
            'agecheck'   => array(PostNLShipment::SHIPMENT_TYPE_AGECHECK, array('Age Check')),
            'birthdaycheck' => array(PostNLShipment::SHIPMENT_TYPE_BIRTHDAYCHECK, array('Birthday Check')),
            'idcheck'    => array(PostNLShipment::SHIPMENT_TYPE_IDCHECK, array('ID Check')),
            'extra_at_home' => array(PostNLShipment::SHIPMENT_TYPE_EXTRAATHOME, array('Extra@Home')),
        );
    }

    /**
     * @param $type
     * @param $expectedLabel
     *
     * @dataProvider getShipmentTypeRenderedValueProvider
     */
    public function testGetShipmentTypeRenderedValue($type, $expectedContains)
    {
        $rowId = rand(0, 1000);

        $row = new Varien_Object();
        $row->setId($rowId);

        $instance = $this->_getInstance();
        $result = $instance->getShipmentTypeRenderedValue($type, $row);

        $this->assertContains((string)$rowId, $result);

        foreach ($expectedContains as $expected) {
            $this->assertContains($expected, $result);
        }
    }

    /**
     * @return array
     */
    public function getOrderTypeRenderedValueProvider()
    {
        return array(
            'avond'         => array('Avond', 'NL', array('Domestic', 'Evening Delivery')),
            'pge'           => array('PGE', 'NL', array('Post Office', 'Early Pickup')),
            'sunday'        => array('Sunday', 'NL', array('Sunday Delivery')),
            'monday'        => array('Monday', 'NL', array('Monday Delivery')),
            'sameday'       => array('Sameday', 'NL', array('Same Day Delivery')),
            'food'          => array('Food', 'NL', array('Food Delivery')),
            'cooled'        => array('Cooledfood', 'NL', array('Cooled Food Delivery')),
            'agecheck'      => array('AgeCheck', 'NL', array('Age Check')),
            'birthdaycheck' => array('BirthdayCheck', 'NL', array('Birthday Check')),
            'idcheck'       => array('IDCheck', 'NL', array('ID Check')),
            'extra_at_home' => array('ExtraAtHome', 'NL', array('Extra@Home')),
            'domestic'      => array('invalidType', 'NL', array('Domestic')),
            'eps'           => array('invalidType', 'BE', array('EPS')),
            'globalpack'    => array('invalidType', 'USA', array('GlobalPack')),
        );
    }

    /**
     * @param $type
     * @param $country
     * @param $expectedContains
     *
     * @dataProvider getOrderTypeRenderedValueProvider
     */
    public function testGetOrderTypeRenderedValue($type, $country, $expectedContains)
    {
        $rowId = rand(0, 1000);

        $row = new Varien_Object();
        $row->setId($rowId);
        $row->setData(RendererTypeAbstract::DELIVERY_OPTION_TYPE_COLUMN, $type);

        $instance = $this->_getInstance();
        $result = $instance->getOrderTypeRenderedValue($country, $row);

        $this->assertContains((string)$rowId, $result);

        foreach ($expectedContains as $expected) {
            $this->assertContains($expected, $result);
        }
    }
}
