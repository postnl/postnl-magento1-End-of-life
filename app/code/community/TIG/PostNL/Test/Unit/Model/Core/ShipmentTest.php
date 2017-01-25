<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Model_Core_ShipmentTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var null|TIG_PostNL_Model_Core_Shipment
     */
    protected $_instance = null;

    public function setUp()
    {
        $this->setShippingAddress('NL');
    }

    public function _getInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = Mage::getModel('postnl_core/shipment');
        }

        return $this->_instance;
    }

    protected function setShippingAddress($country)
    {
        $address = new Varien_Object();
        $address->setCountryId($country);

        $shipment = new Varien_Object();
        $shipment->setShippingAdddress($address);

        $this->_getInstance()->setShipment($shipment);
        $this->_getInstance()->setShippingAddress($address);

        return $this;
    }

    public function testCanGenerateReturnBarcodeWhenFood()
    {
        $this->_getInstance()->setIsDomesticShipment(true);
        $this->_getInstance()->setIsBuspakjeShipment(false);
        $this->_getInstance()->setIsFoodShipment(true);

        $this->assertFalse($this->_getInstance()->canGenerateReturnBarcode());
    }

    public function testCanGenerateReturnBarcodeWhenNoShipmentId()
    {
        $this->_getInstance()->setIsDomesticShipment(true);
        $this->_getInstance()->setIsBuspakjeShipment(false);
        $this->_getInstance()->setIsFoodShipment(false);

        $this->_getInstance()->setShipmentId(false);
        $this->_getInstance()->setShipment(false);

        $this->assertFalse($this->_getInstance()->canGenerateReturnBarcode());
    }

    public function testCanGenerateReturnBarcodeWhenNoShipment()
    {
        $this->_getInstance()->setIsDomesticShipment(true);
        $this->_getInstance()->setIsBuspakjeShipment(false);
        $this->_getInstance()->setIsFoodShipment(false);

        $this->_getInstance()->setShipmentId(10);

        $this->assertTrue($this->_getInstance()->canGenerateReturnBarcode());
    }

    public function testCanGenerateReturnBarcode()
    {
        $this->_getInstance()->setIsDomesticShipment(true);
        $this->_getInstance()->setIsBuspakjeShipment(false);
        $this->_getInstance()->setIsFoodShipment(false);

        $this->_getInstance()->setShipmentId(10);
        $this->_getInstance()->setShipment(array());

        $this->_getInstance()->unsetReturnBarcode();

        $this->assertTrue($this->_getInstance()->canGenerateReturnBarcode());
    }

    public function canGenerateReturnBarcodeWhenNotNLDataProvider()
    {
        return array(
            array('NL', true),
            array('BE', false),
            array('DE', false),
            array('US', false),
        );
    }

    /**
     * @dataProvider canGenerateReturnBarcodeWhenNotNLDataProvider
     */
    public function testCanGenerateReturnBarcodeWhenNotNL($country, $result)
    {
        $this->setShippingAddress($country);

        $this->_getInstance()->setIsDomesticShipment(true);
        $this->_getInstance()->setIsBuspakjeShipment(false);
        $this->_getInstance()->setIsFoodShipment(false);

        $this->_getInstance()->setShipmentId(10);
        $this->_getInstance()->setShipment(array());

        $this->_getInstance()->unsetReturnBarcode();

        $this->assertEquals($result, $this->_getInstance()->canGenerateReturnBarcode());
    }

    public function testHasPakjegemakBeNotInsuredConfig()
    {
        $value = Mage::app()->getStore()
            ->getConfig(TIG_PostNL_Model_Core_Shipment::XPATH_DEFAULT_PAKJEGEMAK_BE_NOT_INSURED_PRODUCT_OPTION);

        $this->assertNotEmpty($value);
    }

    public function isDomesticShipmentProvider()
    {
        return array(
            /** All check fail */
            array(false, true, 'NL', 'BE', false, false),

            /** Can use Dutch products */
            array(false, true, 'NL', 'BE', true, true),

            /** Can use Dutch products but is not BE */
            array(false, true, 'NL', 'US', true, false),

            /** Domestic and Shipping country are the same */
            array(false, true, 'NL', 'NL', null, true),

            /** Has no shipping address */
            array(false, false, null, null, null, false),

            /** The shipment is already marked as domestic. */
            array(true, null, null, null, null, true),
        );
    }

    /**
     * @param $isDomesticShipment
     * @param $hasShippingAddress
     * @param $country
     * @param $domesticCountry
     * @param $canUseDutchProducts
     * @param $expected
     *
     * @internal     param $canUseDutchProduct
     * @dataProvider isDomesticShipmentProvider
     */
    public function testIsDomesticShipment(
        $isDomesticShipment,
        $hasShippingAddress,
        $country,
        $domesticCountry,
        $canUseDutchProducts,
        $expected
    )
    {
        $instance = $this->_getInstance();

        /** @noinspection PhpUndefinedMethodInspection */
        $instance->setIsDomesticShipment($isDomesticShipment);

        if ($hasShippingAddress) {
            $shippingAddressMock = $this->getMock('Mage_Sales_Model_Order_Address', array('getCountryId'));

            $shippingAddressMock->expects($this->once())->method('getCountryId')->willReturn($country);

            $instance->setData('shipping_address', $shippingAddressMock);
        }

        $dataHelperMock = $this->getMock('TIG_PostNL_Helper_Data');
        $dataHelperMockExpectation = $dataHelperMock->expects($this->any());
        $dataHelperMockExpectation->method('getDomesticCountry');
        $dataHelperMockExpectation->willReturn($domesticCountry);

        $deliveryOptionsHelperMock = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');
        $deliveryOptionsHelperMockExpectation = $deliveryOptionsHelperMock->expects($this->any());
        $deliveryOptionsHelperMockExpectation->method('canUseDutchProducts');
        $deliveryOptionsHelperMockExpectation->willReturn($canUseDutchProducts);

        $instance->setData('helper_data', $dataHelperMock);
        $instance->setData('helper_deliveryOptions', $deliveryOptionsHelperMock);

        $result = $this->_getInstance()->isDomesticShipment();
        $this->assertEquals($expected, $result);
    }
}
