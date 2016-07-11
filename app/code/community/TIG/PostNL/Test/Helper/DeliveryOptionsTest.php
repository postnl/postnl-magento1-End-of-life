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
class TIG_PostNL_Test_Helper_DeliveryOptionsTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_DeliveryOptions
     */
    protected function _getInstance()
    {
        return Mage::helper('postnl/deliveryOptions');
    }

    /**
     * @test
     */
    public function shouldBeOfTheRightInstance()
    {
        $helper = $this->_getInstance();
        $this->assertInstanceOf('TIG_PostNL_Helper_DeliveryOptions', $helper);
    }

    /**
     * @test
     */
    public function shouldAllowSundaySorting()
    {
        $this->markTestSkipped('Skip this test');

        Mage::app()->getStore()->setConfig('postnl/cif_labels_and_confirming/allow_sunday_sorting', true);

        $helper = $this->_getInstance();

        $this->assertTrue($helper->canUseSundaySorting());
    }

    /**
     * @test
     */
    public function shouldDisallowSundaySorting()
    {
        $this->markTestSkipped('Skip this test');

        $this->resetMagento();

        Mage::app()->getStore()->setConfig('postnl/cif_labels_and_confirming/allow_sunday_sorting', false);

        $helper = $this->_getInstance();

        $this->assertTrue(!$helper->canUseSundaySorting());
    }

    public function testCanUseDutchProductsByCountryDataProvder()
    {
        return array(
            array(
                'country' => 'BE',
                'shouldPass' => true,
            ),
            array(
                'country' => 'NL',
                'shouldPass' => true,
            ),
            array(
                'country' => 'DE',
                'shouldPass' => false,
            ),
        );
    }

    /**
     * @param $country
     * @param $shouldPass
     *
     * @dataProvider testCanUseDutchProductsByCountryDataProvder
     */
    public function testCanUseDutchProductsByCountry($country, $shouldPass)
    {
        $helper = $this->_getInstance();

        $this->setProperty('_canUseDutchProducts', null);
        $this->setProperty('_domesticCountry', $country);

        Mage::app()->getStore()->setConfig($helper::XPATH_USE_DUTCH_PRODUCTS, '1');

        $this->assertEquals($shouldPass, $helper->canUseDutchProducts());
    }

    public function testCanUseDutchProductsWhenDisabledProvder()
    {
        return array(
            array(
                'country' => 'BE',
                'shouldPass' => false,
            ),
            array(
                'country' => 'NL',
                'shouldPass' => true,
            ),
            array(
                'country' => 'DE',
                'shouldPass' => false,
            ),
        );
    }

    /**
     * @param $country
     * @param $shouldPass
     *
     * @dataProvider testCanUseDutchProductsWhenDisabledProvder
     */
    public function testCanUseDutchProductsWhenDisabled($country, $shouldPass)
    {
        $helper = $this->_getInstance();

        $this->setProperty('_canUseDutchProducts', null);
        $this->setProperty('_domesticCountry', $country);

        Mage::app()->getStore()->setConfig($helper::XPATH_USE_DUTCH_PRODUCTS, '0');

        $this->assertEquals($shouldPass, $helper->canUseDutchProducts());
    }

    public function testCanUseDutchProductsUsesCache()
    {
        $value = uniqid();
        $this->setProperty('_canUseDutchProducts', $value);

        $this->assertEquals($value, $this->_getInstance()->canUseDutchProducts());
    }

    public function testCanUseDeliveryOptionsForQuoteIsVirtual()
    {
        $helper = $this->_getInstance();
        $quote = $this->getMock('Mage_Sales_Model_Quote');

        $quote->expects($this->once())
            ->method('isVirtual')
            ->willReturn(true);

        $this->assertFalse($helper->canUseDeliveryOptionsForQuote($quote));

        $error = Mage::registry('postnl_delivery_options_can_use_delivery_options_errors');
        $this->assertEquals('POSTNL-0104', $error[0]['code']);
    }

    public function testCanUseDeliveryOptionsForQuoteIsBuspakje()
    {
        $helper = $this->_getInstance();
        $quote = $this->getMock('Mage_Sales_Model_Quote');

        $quote->expects($this->once())
            ->method('isVirtual')
            ->willReturn(false);

        $quote->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->setRegistryKey('is_buspakje_config_applicable_to_quote_1', true);

        $this->assertFalse($helper->canUseDeliveryOptionsForQuote($quote));

        $error = Mage::registry('postnl_delivery_options_can_use_delivery_options_errors');
        $this->assertEquals('POSTNL-0190', $error[0]['code']);
    }

    public function testCanUseDeliveryOptionsForQuoteIsBuspakjeEnabled()
    {
        $helper = $this->_getInstance();
        $quote = $this->getMock('Mage_Sales_Model_Quote');

        $quote->expects($this->once())
            ->method('isVirtual')
            ->willReturn(false);

        $quote->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->setRegistryKey('is_buspakje_config_applicable_to_quote_1', true);
        $this->setRegistryKey('can_show_options_for_buspakje_1', false);

        $this->assertFalse($helper->canUseDeliveryOptionsForQuote($quote));

        $error = Mage::registry('postnl_delivery_options_can_use_delivery_options_errors');
        $this->assertEquals('POSTNL-0190', $error[0]['code']);
    }
}