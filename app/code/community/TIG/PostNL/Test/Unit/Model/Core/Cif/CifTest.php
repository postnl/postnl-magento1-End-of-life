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

class TIG_PostNL_Test_Unit_Model_Core_Cif_CifTest extends \TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return false|Mage_Core_Model_Abstract|TIG_PostNL_Model_Core_Cif
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_core/cif');
    }

    public function testIsTheCorrectClass()
    {
        $this->assertInstanceOf('TIG_PostNL_Model_Core_Cif', $this->_getInstance());
    }

    public function testMultipleParcels()
    {
        $parcelCount = 3;

        $soapClient = $this->getSoapClient();

        $confirming = $soapClient->expects($this->once());
        $confirming->method('Confirming');
        $confirming->willReturn((object)['ConfirmingResponseShipment' => (object)[]]);
        $confirming->with($this->callback(function ($soapParams) use ($parcelCount) {
            $this->assertCount($parcelCount, $soapParams['Shipments']['Shipment']);
            $shipment = $soapParams['Shipments']['Shipment'][0];
            $this->assertEquals('barcode 0', $shipment['Barcode']);
            $this->assertEquals('mainbarcode', $shipment['Groups']['Group']['MainBarcode']);

            return true;
        }));

        $instance = $this->_getInstance();
        $instance->setData('soap_client', $soapClient);

        $postnlShipment = $this->prepareShipment($parcelCount);

        $instance->confirmAllShipments($postnlShipment->reveal(), $parcelCount);
    }

    public function testThrowsAnExceptionOnAnEmpty()
    {
        $parcelCount = 1;
        $postnlShipment = $this->prepareShipment($parcelCount);

        $soapClient = $this->getSoapClient();
        $confirming = $soapClient->expects($this->once());
        $confirming->method('Confirming');
        $confirming->willReturn((object)[]);

        $instance = $this->_getInstance();
        $instance->setData('soap_client', $soapClient);

        try {
            $instance->confirmAllShipments($postnlShipment->reveal(), $parcelCount);
        } catch (TIG_PostNL_Exception $exception) {
            $this->assertEquals(
                $exception->getMessage(),
                'Invalid confirmShipment response: stdClass::__set_state(array(' . PHP_EOL . '))');
            return;
        }

        $this->fail('We expected an exception but we got none');
    }

    public function testThrowsAnExceptionOnAnInvalidResponse()
    {
        $parcelCount = 1;
        $postnlShipment = $this->prepareShipment($parcelCount);

        $soapClient = $this->getSoapClient();
        $confirming = $soapClient->expects($this->once());
        $confirming->method('Confirming');
        $confirming->willReturn(null);

        $instance = $this->_getInstance();
        $instance->setData('soap_client', $soapClient);

        try {
            $instance->confirmAllShipments($postnlShipment->reveal(), $parcelCount);
        } catch (TIG_PostNL_Exception $exception) {
            $this->assertEquals($exception->getMessage(), 'Invalid confirmShipment response: NULL');
            return;
        }

        $this->fail('We expected an exception but we got none');
    }

    /**
     * @param $parcelCount
     *
     * @return \Prophecy\Prophecy\ObjectProphecy|TIG_PostNL_Model_Core_Shipment
     */
    protected function prepareShipment($parcelCount)
    {
        $order    = new TIG_PostNL_Model_Core_Order;
        $address  = new Mage_Sales_Model_Order_Address();
        $shipment = new Mage_Sales_Model_Order_Shipment();

        /** @var TIG_PostNL_Model_Core_Shipment|\Prophecy\Prophecy\ObjectProphecy $postnlShipment */
        $postnlShipment = $this->prophesize('TIG_PostNL_Model_Core_Shipment');
        $postnlShipment->getOrder()->willReturn($order);
        $postnlShipment->getShipment()->willReturn($shipment);
        $postnlShipment->getDeliveryDate()->willReturn('2016-11-19');
        $postnlShipment->getStoreId()->willReturn(1);
        $postnlShipment->getReturnBarcode()->willReturn('100');
        $postnlShipment->getShippingAddress()->willReturn($address);
        $postnlShipment->getParcelCount()->willReturn($parcelCount);
        $postnlShipment->getTotalWeight(true, true)->willReturn(100);
        $postnlShipment->isExtraAtHome()->willReturn(true);
        $postnlShipment->getDownPartnerId()->willReturn(null);
        $postnlShipment->getDownPartnerBarcode()->willReturn(null);
        $postnlShipment->getProductCode()->willReturn(3085);
        $postnlShipment->getPostnlOrder()->willReturn(null);
        $postnlShipment->getPakjeGemakAddress()->willReturn(null);
        $postnlShipment->isExtraCover()->willReturn(false);
        $postnlShipment->isCod()->willReturn(false);
        $postnlShipment->isGlobalShipment()->willReturn(false);
        $postnlShipment->isBirthdayCheckShipment()->willReturn(false);
        $postnlShipment->isIDCheckShipment()->willReturn(false);
        $postnlShipment->hasPgLocationCode()->willReturn(false);
        $postnlShipment->getMainBarcode()->willReturn('mainbarcode');
        $postnlShipment->getBarcode(0)->willReturn('barcode 0');
        $postnlShipment->getBarcode(1)->willReturn('barcode 1');
        $postnlShipment->getBarcode(2)->willReturn('barcode 2');

        return $postnlShipment;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSoapClient()
    {
        $soapClient = $this->getMockBuilder('SoapClient');
        $soapClient->disableOriginalConstructor();
        $soapClient->setMethods(['Confirming']);
        $soapClient = $soapClient->getMock();

        return $soapClient;
    }
}
