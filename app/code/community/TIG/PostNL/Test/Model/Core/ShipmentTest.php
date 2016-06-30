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
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class ShipmentTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
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

    public function testCanGenerateReturnBarcodeWhenBuspakje()
    {
        $this->_getInstance()->setIsDomesticShipment(false);
        $this->_getInstance()->setIsBuspakjeShipment(false);

        $this->_getInstance()->setShipmentId(false);
        $this->_getInstance()->setShipment(false);

        $this->assertFalse($this->_getInstance()->canGenerateReturnBarcode());
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
}