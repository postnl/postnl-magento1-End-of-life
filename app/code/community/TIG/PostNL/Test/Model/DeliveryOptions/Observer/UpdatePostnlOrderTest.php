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
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Model_DeliveryOptions_Observer_UpdatePostnlOrderTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder
     */
    protected function _getInstance()
    {
        return new TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder();
    }

    /**
     * @test
     */
    public function shouldGetTheOrderFromTheObserver()
    {
        $this->markTestSkipped('Test is not working at this moment');

        $mockObserver = $this->getMock('Varien_Event_Observer', array('getOrder'));
        $mockObserver->expects($this->once())
                     ->method('getOrder')
                     ->will($this->returnValue(null));

        $observer = $this->_getInstance($mockObserver);
        $this->assertInstanceOf(
             'TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder',
                 $observer->updatePostnlOrder($mockObserver)
        );
    }

    /**
     * @test
     */
    public function shouldGetAPostnlOrder()
    {
        $this->markTestSkipped('Skip this test');

        $mockOrder = $this->getMock('Mage_Sales_Model_Order', array('getQuoteId'));
        $mockOrder->expects($this->once())
                  ->method('getQuoteId')
                  ->will($this->returnValue(1));

        $mockPostnlOrder = $this->getMock('TIG_PostNL_Model_Core_Order', array('load', 'getId', 'getOrderId'));
        $mockPostnlOrder->expects($this->once())
                        ->method('load')
                        ->with(1, 'quote_id')
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('getId')
                        ->will($this->returnValue(null));

        $mockObserver = $this->getMock('Varien_Event_Observer', array('getOrder'));
        $mockObserver->expects($this->once())
                     ->method('getOrder')
                     ->will($this->returnValue($mockOrder));

        $this->setModelMock('postnl_core/order', $mockPostnlOrder);

        $observer = $this->_getInstance($mockObserver);
        $this->assertInstanceOf(
             'TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder',
                 $observer->updatePostnlOrder($mockObserver)
        );
    }

    /**
     * @test
     */
    public function shouldUpdateThePostnlOrder()
    {
        $this->markTestSkipped('Skip this test');

        $mockOrder = $this->getMock('Mage_Sales_Model_Order', array('getQuoteId', 'getId'));
        $mockOrder->expects($this->once())
                  ->method('getQuoteId')
                  ->will($this->returnValue(1));
        $mockOrder->expects($this->once())
                  ->method('getId')
                  ->will($this->returnValue(3));

        $mockPostnlOrder = $this->getMock(
            'TIG_PostNL_Model_Core_Order',
            array('load', 'getId', 'getOrderId', 'setOrderId', 'save', 'getIsPakjeGemak')
        );
        $mockPostnlOrder->expects($this->once())
                        ->method('load')
                        ->with(1, 'quote_id')
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('getId')
                        ->will($this->returnValue(2));
        $mockPostnlOrder->expects($this->once())
                        ->method('getOrderId')
                        ->will($this->returnValue(false));
        $mockPostnlOrder->expects($this->once())
                        ->method('setOrderId')
                        ->with(3)
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('save')
                        ->will($this->returnSelf());
        $mockPostnlOrder->expects($this->once())
                        ->method('getIsPakjeGemak')
                        ->will($this->returnValue(false));

        $mockObserver = $this->getMock('Varien_Event_Observer', array('getOrder'));
        $mockObserver->expects($this->once())
                     ->method('getOrder')
                     ->will($this->returnValue($mockOrder));

        $this->setModelMock('postnl_core/order', $mockPostnlOrder);

        $observer = $this->_getInstance($mockObserver);
        $this->assertInstanceOf(
             'TIG_PostNL_Model_DeliveryOptions_Observer_UpdatePostnlOrder',
                 $observer->updatePostnlOrder($mockObserver)
        );
    }
}