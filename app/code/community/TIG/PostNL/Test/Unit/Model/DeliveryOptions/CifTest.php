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
class TIG_PostNL_Test_Unit_Model_DeliveryOptions_CifTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var null|TIG_PostNL_Model_DeliveryOptions_Cif
     */
    protected $_instance = null;

    /**
     * @var null|SoapClient
     */
    protected $_soapClient = null;

    /**
     * @var null|Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    /**
     * @return TIG_PostNL_Model_DeliveryOptions_Cif
     */
    public function _getInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = Mage::getModel('postnl_deliveryoptions/cif');
        }

        return $this->_instance;
    }

    /**
     * @return SoapClient
     */
    public function _getSoapClient()
    {
        if ($this->_soapClient === null) {
            $this->_soapClient = $this->getMockBuilder('SoapClient')
                ->disableOriginalConstructor()
                ->setMethods(['GetDeliveryDate'])
                ->getMock();
        }

        return $this->_soapClient;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function _getQuote()
    {
        if ($this->_quote === null)
        {
            $mockCollection = $this->getMockBuilder('Mage_Eav_Model_Entity_Collection_Abstract')
                ->disableOriginalConstructor()
                ->getMock();

            $mockCollection->expects($this->once())
                ->method('getIterator')
                ->willReturn(new ArrayIterator());

            $mockCollection->expects($this->once())
                ->method('getColumnValues')
                ->will($this->returnValue(array()));

            $mockQuote = $this->getMock('Mage_Sales_Model_Quote');
            $mockQuote->expects($this->any())
                ->method('getStoreId')
                ->will($this->returnValue(1));

            $mockQuote->expects($this->once())
                ->method('getItemsCollection')
                ->will($this->returnValue($mockCollection));

            $this->_quote = $mockQuote;
        }

        return $this->_quote;
    }


    /**
     * @test
     *
     * @expectedException TIG_PostNL_Exception
     */
    public function shouldThrowAnExceptionIfInvalidResponse()
    {
        $instance = $this->_getInstance();
        $quote = $this->_getQuote();
        $mockSoapClient = $this->_getSoapClient();

        $instance->setSoapClient($mockSoapClient);
        $instance->getDeliveryDate('1014 BA', 'NL', $quote);
    }

    /**
     * @test
     */
    public function shouldReturnCorrectDeliveryDate()
    {
        $dateTomorrow = new DateTime('now + 1day');
        $expectedDateTime = $dateTomorrow->format('d-m-Y');

        $soapResponse = new stdClass();
        $soapResponse->DeliveryDate = $expectedDateTime;

        $mockSoapClient = $this->_getSoapClient();
        $mockSoapClient->expects($this->once())
            ->method('GetDeliveryDate')
            ->willReturn($soapResponse);

        $quote = $this->_getQuote();
        $instance = $this->_getInstance();
        $instance->setSoapClient($mockSoapClient);
        $deliveryDate = $instance->getDeliveryDate('1014 BA', 'NL', $quote);

        $this->assertEquals($expectedDateTime, $deliveryDate);
    }

    public function differentOptionsForBelgiumDataProvder()
    {
        return array(
            array('NL', 'pickup', false),
            array('BE', 'pickup', true),
            array('NL', 'delivery', false),
            array('BE', 'delivery', false),
        );
    }

    /**
     * @dataProvider differentOptionsForBelgiumDataProvder
     */
    public function testDifferentOptionsForBelgium($country, $type, $shouldContainPickup)
    {
        $dateTomorrow = new DateTime('now + 1day');
        $expectedDateTime = $dateTomorrow->format('d-m-Y');

        $soapResponse = new stdClass();
        $soapResponse->DeliveryDate = $expectedDateTime;

        $mockSoapClient = $this->_getSoapClient();
        $mockSoapClient->expects($this->once())
            ->method('GetDeliveryDate')
            ->with( $this->callback( function ($arguments) use ($shouldContainPickup) {
                if ($shouldContainPickup) {
                    return in_array('Pickup', $arguments['GetDeliveryDate']['Options']);
                } else {
                    return !in_array('Pickup', $arguments['GetDeliveryDate']['Options']);
                }
            }))
            ->willReturn($soapResponse);

        $quote = $this->_getQuote();
        $instance = $this->_getInstance();
        $instance->setSoapClient($mockSoapClient);
        $instance->getDeliveryDate('2000', $country, $quote, $type);
    }
}