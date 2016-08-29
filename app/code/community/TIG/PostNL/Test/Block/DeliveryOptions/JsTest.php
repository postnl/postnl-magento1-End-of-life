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
class TIG_PostNL_Test_Block_DeliveryOptions_JsTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_Block_DeliveryOptions_Js';

    protected function _getInstance()
    {
        return new $this->_class;
    }

    /**
     * @test
     */
    public function apiKeyShouldBeReturned()
    {
        $block = $this->_getInstance();

        $block->setApiKey('test');

        $this->assertEquals('test', $block->getApiKey());
    }

    /**
     * @test
     */
    public function apiKeyShouldBeReturnedFromConfig()
    {
        $storeCode = Mage::app()->getStore()->getCode();
        Mage::app()->getConfig()->setNode("stores/{$storeCode}/postnl/google_maps/api_key", 'keyTest');

        $block = $this->_getInstance();

        $this->assertEquals('keyTest', $block->getApiKey());
    }

    /**
     * @test
     *
     * @dataProvider trueAndFalse
     */
    public function shouldOnlyRenderIfDeliveryOptionsAreAvailable($returnValue)
    {
        $this->markTestSkipped('Skip this test');

        $this->registerMockSessions(array('checkout'));

        $mockQuote = $this->getMock('Mage_Sales_Model_Quote');

        $mockSession = Mage::getSingleton('checkout/session');
        $mockSession->expects($this->once())
                    ->method('getQuote')
                    ->will($this->returnValue($mockQuote));

        $mockHelper = $this->getMock('TIG_PostNL_Helper_DeliveryOptions', array('canUseDeliveryOptions'));
        $mockHelper->expects($this->once())
                   ->method('canUseDeliveryOptions')
                   ->withAnyParameters()
                   ->will($this->returnValue($returnValue));

        $this->setHelperMock('postnl/deliveryOptions', $mockHelper);

        $html = $this->_getInstance()->toHtml();

        if ($returnValue === false) {
            $this->assertTrue(empty($html), 'Expected script to not render');
        } else {
            $rendered = strpos($html, '<script');
            $this->assertTrue($rendered !== false, 'Expected script to render');
        }
    }

    /**
     * @return array
     */
    public function trueAndFalse()
    {
        return array(
            array(true),
            array(false),
        );
    }
}