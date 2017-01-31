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
                ->setMethods(array('GetDeliveryDate'))
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

    public function differentOptionsForBelgiumDataProvider()
    {
        return array(
            array('NL', 'pickup', false),
            array('BE', 'pickup', true),
            array('NL', 'delivery', false),
            array('BE', 'delivery', false),
        );
    }

    /**
     * @dataProvider differentOptionsForBelgiumDataProvider
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

    public function getDeliveryDateOptionsArrayProvider()
    {
        return array(
            /**
             * Sameday tests
             */
            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next friday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next friday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next friday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next friday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', false, array('Daytime')),

            array('next friday 10:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next friday 10:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next friday 10:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next friday 13:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Evening')),
            array('next friday 23:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Evening')),

            array('next thursday 10:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next thursday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),

            array('next thursday 10:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next thursday 10:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 10:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 13:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Evening')),
            array('next thursday 23:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Sameday', 'Evening')),
            array('next thursday 23:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 23:00', 'Cooled', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '0', false, array('Daytime')),

            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),
            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next friday 10:00', 'Regular', 0, 'NL', 'delivery', false, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),
            array('next friday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next friday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),
            array('next friday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next friday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),

            array('next friday 10:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),
            array('next friday 13:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Evening')),
            array('next friday 23:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Evening')),

            array('next thursday 10:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),

            array('next thursday 10:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),
            array('next thursday 13:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Evening')),
            array('next thursday 23:00', 'Cooled', 0, 'NL', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Sameday', 'Evening')),

            /**
             * BE
             */
            array('next thursday 10:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime')),
            array('next thursday 13:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime')),
            array('next thursday 23:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '1', true, array('Daytime')),
            array('next thursday 10:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime')),
            array('next thursday 13:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime')),
            array('next thursday 23:00', 'Regular', 0, 'BE', 'delivery', true, '10:30', '22:00', 0, '0', true, array('Daytime')),

            /**
             * BE Pickup
             */
            array('next thursday 10:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Pickup')),
            array('next thursday 13:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Pickup')),
            array('next thursday 23:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Pickup')),
            array('next thursday 10:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Pickup')),
            array('next thursday 13:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Pickup')),
            array('next thursday 23:00', 'Regular', 0, 'BE', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Pickup')),

            /**
             * NL Pickup
             */
            array('next thursday 10:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 10:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', true, array('Daytime', 'Evening')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '0', false, array('Daytime')),
            array('next thursday 10:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next thursday 10:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next thursday 13:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Daytime', 'Evening', 'Sunday')),
            array('next thursday 23:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', false, array('Daytime', 'Sunday')),

            'after_sunday_cutoff_before regular_cutoff' => array('next sunday 15:00', 'Regular', 0, 'NL', 'pickup', true, '10:30', '22:00', 0, '1', true, array('Sunday', 'Sameday', 'Evening')),
        );
    }

    /**
     * @param $timeStamp
     * @param $shipmentType
     * @param $shippingDuration
     * @param $country
     * @param $for
     * @param $enableSameDayDelivery
     * @param $sameDayDeliveryCutoff
     * @param $regularCutoff
     * @param $shippingDurationConfig
     * @param $enableSundayDelivery
     * @param $canUseEveningTimeframes
     * @param $expectedResult
     *
     * @dataProvider getDeliveryDateOptionsArrayProvider
     */
    public function testGetDeliveryDateOptionsArray(
        $timeStamp,
        $shipmentType,
        $shippingDuration,
        $country,
        $for,
        $enableSameDayDelivery,
        $sameDayDeliveryCutoff,
        $regularCutoff,
        $shippingDurationConfig,
        $enableSundayDelivery,
        $canUseEveningTimeframes,
        $expectedResult
    )
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');

        Mage::app()->getStore()->setConfig($helper::XPATH_ENABLE_SAMEDAY_DELIVERY, $enableSameDayDelivery);
        Mage::app()->getStore()->setConfig($helper::XPATH_SAMEDAY_CUTOFF_TIME, $sameDayDeliveryCutoff);
        Mage::app()->getStore()->setConfig($helper::XPATH_CUTOFF_TIME, $regularCutoff);
        Mage::app()->getStore()->setConfig($helper::XPATH_SHIPPING_DURATION, $shippingDurationConfig);
        Mage::app()->getStore()->setConfig($helper::XPATH_ENABLE_SUNDAY_DELIVERY, $enableSundayDelivery);

        $helperMock = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');

        $helperMock->expects($this->any())
            ->method('quoteIsFood')
            ->willReturn($shipmentType == 'Cooled');

        $helperMock->expects($this->any())
            ->method('getQuoteFoodType')
            ->willReturn($shipmentType == 'Cooled' ? 2 : 0);

        $helperMock->expects($this->any())
            ->method('canUseEveningTimeframes')
            ->willReturn($canUseEveningTimeframes);

        $helperMock->expects($this->any())
            ->method('getDateTime')
            ->willReturn(new DateTime($timeStamp));

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', array('postnl/deliveryOptions' => $helperMock));

        $method = new ReflectionMethod(get_class($instance), '_getDeliveryDateOptionsArray');
        $method->setAccessible(true);

        $result = $method->invokeArgs($instance, array($shippingDuration, $country, $for));

        $this->assertEquals($expectedResult, $result, 'Compare the contents of the array', 0.0, 10, true);
        $this->assertEquals(count($expectedResult), count($result));
    }

    public function cutoffTimesProvider()
    {
        return array(
            array('next thursday 10:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next thursday 10:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
            array('next thursday 15:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '22:00:00', '15:00:00'),
            array('next thursday 23:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next thursday 23:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),

            array('next thursday 10:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next thursday 10:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
            array('next thursday 15:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '22:00:00', '15:00:00'),
            array('next thursday 23:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next thursday 23:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),

            array('next friday 10:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next friday 10:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
            array('next friday 15:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '22:00:00', '15:00:00'),
            array('next friday 23:00', 'Regular', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '22:00:00', '15:00:00'),

            array('next friday 10:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next friday 10:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
            array('next friday 15:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next friday 15:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
            array('next friday 23:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', true, /* Response --> */ '10:30:00', '15:00:00'),
            array('next friday 23:00', 'Cooled', '22:00:00', '15:00:00', '10:30:00', false, /* Response --> */ '22:00:00', '15:00:00'),
        );
    }

    /**
     * @dataProvider cutoffTimesProvider
     *
     * @param $timestamp
     * @param $shipmentType
     * @param $regularDeliveryCutoff
     * @param $sundayDeliveryCutoff
     * @param $sameDayDeliveryCutoff
     * @param $enableSameDayDelivery
     * @param $monSatCutoff
     * @param $sundayCutoff
     *
     * @internal     param $expectedResult
     */
    public function testCutoffTimes(
        $timestamp,
        $shipmentType,
        $regularDeliveryCutoff,
        $sundayDeliveryCutoff,
        $sameDayDeliveryCutoff,
        $enableSameDayDelivery,
        $monSatCutoff,
        $sundayCutoff
    ) {
        /** @var TIG_PostNL_Helper_DeliveryOptions $helper */
        $helper = Mage::helper('postnl/deliveryOptions');

        Mage::app()->getStore()->setConfig($helper::XPATH_CUTOFF_TIME, $regularDeliveryCutoff);
        Mage::app()->getStore()->setConfig($helper::XPATH_SUNDAY_CUTOFF_TIME, $sundayDeliveryCutoff);
        Mage::app()->getStore()->setConfig($helper::XPATH_SAMEDAY_CUTOFF_TIME, $sameDayDeliveryCutoff);
        Mage::app()->getStore()->setConfig($helper::XPATH_ENABLE_SAMEDAY_DELIVERY, $enableSameDayDelivery);

        $helperMock = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');

        $helperMock->expects($this->any())
            ->method('quoteIsFood')
            ->willReturn($shipmentType == 'Cooled');

        $helperMock->expects($this->any())
            ->method('getQuoteFoodType')
            ->willReturn($shipmentType == 'Cooled' ? 2 : 0);

        $helperMock->expects($this->any())
            ->method('getDateTime')
            ->willReturn(new DateTime($timestamp));

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', array('postnl/deliveryOptions' => $helperMock));

        $method = new ReflectionMethod(get_class($instance), '_getCutOffTimes');
        $method->setAccessible(true);

        $result = $method->invokeArgs($instance, array(null));

        foreach ($result as $cutoff) {
            if ($cutoff['Day'] == '00') {
                $this->assertEquals($monSatCutoff, $cutoff['Time'], 'Assert that mon-sat has a cutoff of ' . $monSatCutoff);
            } elseif ($cutoff['Day'] == '07') {
                $this->assertEquals($sundayCutoff, $cutoff['Time'], 'Assert that sunday has a cutoff of ' . $sundayCutoff);
            }
        }
    }

    /**
     * @todo Expanded test cases, includes different countries and sunday sorting.
     * @return array
     */
    public function getDeliveryTimeframesOptionsArrayProvider()
    {
        return array(
            array('Regular', 'NL', true, true, true, false, array('Daytime', 'Sameday', 'Evening')),
            array('Regular', 'NL', true, true, true, true, array('Daytime', 'Sameday', 'Evening', 'Sunday')),
            array('Regular', 'NL', false, false, true, false, array('Daytime', 'Evening')),
            array('Regular', 'NL', false, false, false, false, array('Daytime')),
            array('Regular', 'NL', true, false, false, false, array('Daytime')),
            array('Regular', 'NL', true, true, false, false, array('Daytime', 'Sameday', 'Evening')),

            array('Cooled', 'NL', true, true, true, false, array('Sameday', 'Evening')),
            array('Cooled', 'NL', true, true, true, true, array('Sameday', 'Evening')),
            array('Cooled', 'NL', false, false, true, false, array('Daytime', 'Evening')),
            array('Cooled', 'NL', false, false, false, false, array('Daytime')),
            array('Cooled', 'NL', true, false, false, false, array('Sameday', 'Evening')),
            array('Cooled', 'NL', true, true, false, false, array('Sameday', 'Evening')),
        );
    }

    /**
     * @dataProvider getDeliveryTimeframesOptionsArrayProvider
     *
     * @param $shipmentType
     * @param $country
     * @param $canUseFoodDelivery
     * @param $canUseSameDayDelivery
     * @param $canUseEveningTimeframes
     * @param $canUseSundayDelivery
     * @param $expectedResult
     */
    public function testGetDeliveryTimeframesOptionsArray(
        $shipmentType,
        $country,
        $canUseFoodDelivery,
        $canUseSameDayDelivery,
        $canUseEveningTimeframes,
        $canUseSundayDelivery,
        $expectedResult
    )
    {
        $helperMock = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');

        $helperMock->expects($this->any())
            ->method('quoteIsFood')
            ->willReturn($shipmentType == 'Cooled');

        $helperMock->expects($this->any())
            ->method('getQuoteFoodType')
            ->willReturn($shipmentType == 'Cooled' ? 2 : 0);

        $helperMock->expects($this->any())
            ->method('canUseFoodDelivery')
            ->willReturn($canUseFoodDelivery);

        $helperMock->expects($this->any())
            ->method('canUseSameDayDelivery')
            ->willReturn($canUseSameDayDelivery);

        $helperMock->expects($this->any())
            ->method('canUseEveningTimeframes')
            ->willReturn($canUseEveningTimeframes);

        $helperMock->expects($this->any())
            ->method('getCache')
            ->willReturn(null);

        $helperMock->expects($this->any())
            ->method('canUseSundayDelivery')
            ->willReturn($canUseSundayDelivery);

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', array('postnl/deliveryOptions' => $helperMock));

        $method = new ReflectionMethod(get_class($instance), '_getDeliveryTimeframesOptionsArray');
        $method->setAccessible(true);

        $result = $method->invokeArgs($instance, array($country));

        $this->assertEquals($expectedResult, $result);
    }
}
