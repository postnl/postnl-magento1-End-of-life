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

class TIG_PostNL_Test_Unit_Helper_ProductCodeTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    public function setUp()
    {
        parent::setUp();

        foreach ($this->getXpathOptions() as $xpath => $value) {
            Mage::app()->getStore(0)->setConfig($xpath, $value);
        }
    }

    /**
     * Returns the instance. Should be overridden.
     *
     * @return TIG_PostNL_Helper_ProductCode
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/productCode');
    }

    public function getXpathOptions()
    {
        return array(
            PostNLShipment::XPATH_DEFAULT_STANDARD_COD_PRODUCT_OPTION              => 'domestic_cod',
            PostNLShipment::XPATH_DEFAULT_EVENING_PRODUCT_OPTION                   => 'avond',
            PostNLShipment::XPATH_DEFAULT_EVENING_COD_PRODUCT_OPTION               => 'avond_cod',
            PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_BE_BE_PRODUCT_OPTION          => 'pg_be_be',
            PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_NL_BE_PRODUCT_OPTION          => 'pg_nl_be',
            PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_PRODUCT_OPTION                => 'pg',
            PostNLShipment::XPATH_DEFAULT_PAKJEGEMAK_COD_PRODUCT_OPTION            => 'pg_cod',
            PostNLShipment::XPATH_DEFAULT_PGE_PRODUCT_OPTION                       => 'pge',
            PostNLShipment::XPATH_DEFAULT_PGE_COD_PRODUCT_OPTION                   => 'pge_cod',
            PostNLShipment::XPATH_DEFAULT_PAKKETAUTOMAAT_PRODUCT_OPTION            => 'pa',
            PostNLShipment::XPATH_DEFAULT_EU_BE_PRODUCT_OPTION                     => 'eps_be',
            PostNLShipment::XPATH_DEFAULT_EU_PRODUCT_OPTION                        => 'eps',
            PostNLShipment::XPATH_DEFAULT_GLOBAL_PRODUCT_OPTION                    => 'globalpack',
            PostNLShipment::XPATH_DEFAULT_BUSPAKJE_PRODUCT_OPTION                  => 'buspakje',
            PostNLShipment::XPATH_DEFAULT_SUNDAY_PRODUCT_OPTION                    => 'sunday',
            PostNLShipment::XPATH_DEFAULT_SAMEDAY_PRODUCT_OPTION                   => 'sameday',
            PostNLShipment::XPATH_DEFAULT_FOOD_PRODUCT_OPTION                      => 'food',
            PostNLShipment::XPATH_DEFAULT_COOLED_PRODUCT_OPTION                    => 'cooled',
            PostNLShipment::XPATH_DEFAULT_AGECHECK_PICKUP_PRODUCT_OPTION           => 'agecheck_pickup',
            PostNLShipment::XPATH_DEFAULT_AGECHECK_DELIVERY_PRODUCT_OPTION         => 'agecheck_delivery',
            PostNLShipment::XPATH_DEFAULT_BIRTHDAYCHECK_PICKUP_PRODUCT_OPTION      => 'birthdaycheck_pickup',
            PostNLShipment::XPATH_DEFAULT_BIRTHDAYCHECK_DELIVERY_PRODUCT_OPTION    => 'birthdaycheck_delivery',
            PostNLShipment::XPATH_DEFAULT_IDCHECK_PICKUP_PRODUCT_OPTION            => 'idcheck_pickup',
            PostNLShipment::XPATH_DEFAULT_IDCHECK_DELIVERY_PRODUCT_OPTION          => 'idcheck_delivery',
            PostNLShipment::XPATH_DEFAULT_STATED_ADDRESS_ONLY_OPTION               => 'stated_address_only',
            PostNLShipment::XPATH_DEFAULT_EXTRA_AT_HOME_PRODUCT_OPTION             => 'extra_at_home'
        );
    }

    public function returnsTheRightCodeProvider()
    {
        return array(
            'domestic_cod'  => array(PostNLShipment::SHIPMENT_TYPE_DOMESTIC_COD, 'domestic_cod'),
            'avond'         => array(PostNLShipment::SHIPMENT_TYPE_AVOND, 'avond'),
            'avond_cod'     => array(PostNLShipment::SHIPMENT_TYPE_AVOND_COD, 'avond_cod'),
            'pg'            => array(PostNLShipment::SHIPMENT_TYPE_PG, 'pg'),
            'pg_cod'        => array(PostNLShipment::SHIPMENT_TYPE_PG_COD, 'pg_cod'),
            'pge'           => array(PostNLShipment::SHIPMENT_TYPE_PGE, 'pge'),
            'pge_cod'       => array(PostNLShipment::SHIPMENT_TYPE_PGE_COD, 'pge_cod'),
            'pa'            => array(PostNLShipment::SHIPMENT_TYPE_PA, 'pa'),
            'eps'           => array(PostNLShipment::SHIPMENT_TYPE_EPS, 'eps'),
            'globalpack'    => array(PostNLShipment::SHIPMENT_TYPE_GLOBALPACK, 'globalpack'),
            'buspakje'      => array(PostNLShipment::SHIPMENT_TYPE_BUSPAKJE, 'buspakje'),
            'sunday'        => array(PostNLShipment::SHIPMENT_TYPE_SUNDAY, 'sunday'),
            'sameday'       => array(PostNLShipment::SHIPMENT_TYPE_SAMEDAY, 'sameday'),
            'food'          => array(PostNLShipment::SHIPMENT_TYPE_FOOD, 'food'),
            'cooled'        => array(PostNLShipment::SHIPMENT_TYPE_COOLED, 'cooled'),
            'agecheck'      => array(PostNLShipment::SHIPMENT_TYPE_AGECHECK, 'agecheck_delivery'),
            'birthdaycheck' => array(PostNLShipment::SHIPMENT_TYPE_BIRTHDAYCHECK, 'birthdaycheck_delivery'),
            'idcheck'       => array(PostNLShipment::SHIPMENT_TYPE_IDCHECK, 'idcheck_delivery'),
            'extra_at_home' => array(PostNLShipment::SHIPMENT_TYPE_EXTRAATHOME, 'extra_at_home'),
        );
    }

    /**
     * @dataProvider returnsTheRightCodeProvider
     *
     * @param $shipmentType
     * @param $expectedProductOption
     */
    public function testReturnsTheRightCode($shipmentType, $expectedProductOption)
    {
        $postnlOrderMock = $this->prepareMocks();

        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals($expectedProductOption, $result);
    }

    public function testAgeCheckWithPakjegemak()
    {
        $postnlOrderMock = $this->prepareMocks();
        $isPakjeGemak = $postnlOrderMock->method('isPakjeGemak');
        $isPakjeGemak->willReturn(true);

        $shipmentType = TIG_PostNL_Model_Core_Shipment::SHIPMENT_TYPE_AGECHECK;
        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals('agecheck_pickup', $result);
    }

    public function testBirthdayCheckWithPakjegemak()
    {
        $postnlOrderMock = $this->prepareMocks();
        $isPakjeGemak = $postnlOrderMock->method('isPakjeGemak');
        $isPakjeGemak->willReturn(true);

        $shipmentType = TIG_PostNL_Model_Core_Shipment::SHIPMENT_TYPE_BIRTHDAYCHECK;
        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals('birthdaycheck_pickup', $result);
    }

    public function testIDCheckWithPakjegemak()
    {
        $postnlOrderMock = $this->prepareMocks();
        $isPakjeGemak = $postnlOrderMock->method('isPakjeGemak');
        $isPakjeGemak->willReturn(true);

        $shipmentType = TIG_PostNL_Model_Core_Shipment::SHIPMENT_TYPE_IDCHECK;
        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals('idcheck_pickup', $result);
    }

    public function testEveningWithStatedAddress()
    {
        $this->markTestIncomplete('Creating test is in progress');

        $postnlOrderMock = $this->prepareMocks(array('hasOptions', 'getOptions'));
        $postnlOrderMock->method('hasOptions')->willReturn(true);
        $postnlOrderMock->method('getOptions')->willReturn(array('only_stated_address' => true));


        $shipmentType = TIG_PostNL_Model_Core_Shipment::SHIPMENT_TYPE_AVOND;
        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals('stated_address_only', $result);
    }

    public function testExtraAtHome()
    {
        $postnlOrderMock = $this->prepareMocks();

        $shipmentType = TIG_PostNL_Model_Core_Shipment::SHIPMENT_TYPE_EXTRAATHOME;
        $result = $this->_getInstance()->getDefault(0, $shipmentType, $postnlOrderMock);
        $this->assertEquals('extra_at_home', $result);
    }

    /**
     * @param array $methods
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareMocks($methods = array())
    {
        $allProductOptionsMock = $this->getMock('TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions');
        $allProductOptionsMock->method('getPepsOptions')->willReturn(
            array(6350 => '6350 - Priority packets tracked', 6550 => '6550 - Priority packets tracked bulk',
                  6940 => '6940 - Priority packets tracked sorted', 6942 => '6942 - Priority packets tracked boxable sorted')
        );

        $instance = $this->_getInstance();
        $this->setProperty('allProductOptions', $allProductOptionsMock, $instance);

        $orderMock           = $this->getMock('Mage_Sales_Model_Order');
        $shippingAddressMock = $this->getMockBuilder('Mage_Sales_Model_Order_Address')
            ->setMethods(array('getCountryId'))
            ->getMock();
        $shippingAddressMock->expects($this->any())->method('getCountryId')->willReturn('NL');

        $getShippingAddress = $orderMock->method('getShippingAddress');
        $getShippingAddress->willReturn($shippingAddressMock);

        $postnlOrderMock = $this->getMock('TIG_PostNL_Model_Core_Order', $methods);
        $getOrder        = $postnlOrderMock->method('getOrder');
        $getOrder->willReturn($orderMock);

        return $postnlOrderMock;
    }
}
