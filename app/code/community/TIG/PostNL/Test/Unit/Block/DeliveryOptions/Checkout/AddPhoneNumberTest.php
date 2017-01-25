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
 * @copyright   Copyright (c) 2017 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Block_DeliveryOptions_Checkout_AddPhoneNumberTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber';

    /**
     * @return TIG_PostNL_Block_DeliveryOptions_Checkout_AddPhoneNumber
     */
    protected function _getInstance()
    {
        return new $this->_class();
    }

    /**
     * @test
     */
    public function blockShouldBeTheRightInstance()
    {
        $block = $this->_getInstance();

        $this->assertInstanceOf($this->_class, $block);
    }

    /**
     * @test
     */
    public function shouldGetTemplatePath()
    {
        $block = $this->_getInstance();

        $template = $block->getTemplate();
        $this->assertEquals('TIG/PostNL/delivery_options/addphonenumber.phtml', $template);
    }

    /**
     * @test
     */
    public function templateSHouldBeSettable()
    {
        $block = $this->_getInstance();
        $block->setTemplate('test');

        $template = $block->getTemplate();
        $this->assertEquals('test', $template);
    }

    /**
     * @test
     */
    public function shouldGetPhoneNumber()
    {
        $block = $this->_getInstance();

        $block->setPhoneNumber('test');

        $this->assertEquals('test', $block->getPhoneNumber());
    }

    /**
     * @test
     */
    public function shouldGetPhoneFromAddress()
    {
        $block = $this->_getInstance();

        $mockAddress = $this->getMock('Mage_Sales_Model_Quote_Address', array('getTelephone'));
        $mockAddress->expects($this->once())
                    ->method('getTelephone')
                    ->will($this->returnValue('testNumber'));

        $block->setShippingAddress($mockAddress);

        $this->assertEquals('testNumber', $block->getPhoneNumber());
    }

    /**
     * @test
     */
    public function shouldGetEmptyPhoneFromAddressWhenPhoneIsASingleDash()
    {
        $block = $this->_getInstance();

        $mockAddress = $this->getMock('Mage_Sales_Model_Quote_Address', array('getTelephone'));
        $mockAddress->expects($this->once())
                    ->method('getTelephone')
                    ->will($this->returnValue('-'));

        $block->setShippingAddress($mockAddress);

        $this->assertEquals('', $block->getPhoneNumber());
    }

    /**
     * @test
     */
    public function shouldGetAddressFromQuote()
    {
        $block = $this->_getInstance();

        $mockAddress = $this->getMock('Mage_Sales_Model_Quote_Address');

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');
        $mockQuote->expects($this->once())
                  ->method('getShippingAddress')
                  ->will($this->returnValue($mockAddress));

        $block->setQuote($mockQuote);

        $this->assertInstanceOf('Mage_Sales_Model_Quote_Address', $block->getShippingAddress());
    }

    /**
     * @test
     */
    public function shouldGetEmptyAddressWithoutAQuote()
    {
        $block = $this->_getInstance();

        $block->setQuote('');

        $this->assertInstanceOf('Mage_Sales_Model_Quote_Address', $block->getShippingAddress());
    }

    /**
     * @test
     */
    public function shouldGetQuoteFromSession()
    {
        $block = $this->_getInstance();

        $this->registerMockSessions(array('checkout'));

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');

        $mockSession = Mage::getSingleton('checkout/session');
        $mockSession->expects($this->once())
                    ->method('getQuote')
                    ->will($this->returnValue($mockQuote));

        $this->assertInstanceOf('Mage_Sales_Model_Quote', $block->getQuote());
    }
}
