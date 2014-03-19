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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Model_DeliveryOptions_ServiceTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_DeliveryOptions_Service
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_deliveryoptions/service');
    }

    /**
     * @test
     */
    public function shouldBeInstanceOfTheRightClass()
    {
        $instance = $this->_getInstance();

        $this->assertInstanceOf('TIG_PostNL_Model_DeliveryOptions_Service', $instance);
    }

    /**
     * @test
     */
    public function getQuoteShouldReturnAQuote()
    {
        $instance = $this->_getInstance();

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');
        $instance->setQuote($mockQuote);

        $this->assertInstanceOf('Mage_Sales_Model_Quote', $instance->getQuote());
    }

    /**
     * @test
     */
    public function getPostnlOrderShouldReturnAPostnlOrder()
    {
        $instance = $this->_getInstance();

        $mockPostnlOrder = $this->getMock('TIG_PostNL_Model_Checkout_Order');
        $instance->setPostnlOrder($mockPostnlOrder);

        $this->assertInstanceOf('TIG_PostNL_Model_Checkout_Order', $instance->getPostnlOrder());
    }

    /**
     * @test
     */
    public function getPostnlOrderShouldReturnAPostnlOrderFromAQuote()
    {
        $instance = $this->_getInstance();

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');
        $mockQuote->expects($this->once())
                  ->method('getId');

        $instance->setQuote($mockQuote);

        $this->assertInstanceOf('TIG_PostNL_Model_Checkout_Order', $instance->getPostnlOrder());
    }

    /**
     * @test
     */
    public function saveOptionCostsShouldBeCallable()
    {
        $instance = $this->_getInstance();
        $isCallable = is_callable(array($instance, 'saveOptionCosts'));

        $this->assertTrue($isCallable);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function saveOptionCostsShouldThrowAnExceptionIfCostsAreNotAFloat()
    {
        $instance = $this->_getInstance();

        $instance->saveOptionCosts('test');
    }

    /**
     * @test
     */
    public function saveOptionCostsShouldSetPostnlOrderParameters()
    {
        $instance = $this->_getInstance();

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');
        $mockQuote->expects($this->once())
                  ->method('getId')
                  ->will($this->returnValue(1));

        $instance->setQuote($mockQuote);

        $mockPostnlOrder = $this->getMock(
            'TIG_PostNL_Model_Checkout_Order',
            array(
                'setQuoteId',
                'setIsActive',
                'setShipmentCosts',
                'save'
            )
        );
        $mockPostnlOrder->expects($this->once())
                        ->method('setQuoteId')
                        ->with(1)
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('setIsActive')
                        ->with(true)
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('setShipmentCosts')
                        ->with(1.5)
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('save')
                        ->will($this->returnSelf());

        $instance->setPostnlOrder($mockPostnlOrder);
        $instance->saveOptionCosts(1.5);
    }
}