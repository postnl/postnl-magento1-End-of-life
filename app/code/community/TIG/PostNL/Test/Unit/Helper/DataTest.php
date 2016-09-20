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
class TIG_PostNL_Test_Unit_Helper_DataTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_Data
     */
    public function getInstance()
    {
        return Mage::helper('postnl');
    }

    public function checkIsQuoteProvider()
    {
        return array(
            array('Agecheck', true),
            array('Birthdaycheck', true),
            array('Idcheck', true),
            array(0, false),
            array('0', false),
            array('', false),
        );
    }

    /**
     * @dataProvider checkIsQuoteProvider
     */
    public function testCheckIsQuote($type, $result)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMock('Mage_Sales_Model_Quote_Item', array('getPostnlProductType'));

        $item->expects($this->once())
            ->method('getPostnlProductType')
            ->willReturn($type);

        /** @var Mage_Sales_Model_Quote|PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMock('Mage_Sales_Model_Quote', array('getId', 'getAllItems', 'getProduct'));

        $quote->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn(array($quote));

        $quote->expects($this->once())
            ->method('getProduct')
            ->willReturn($item);

        $this->setRegistryKey('postnl_quote_is_check_' . 1);

        $instance = $this->getInstance();
        $this->assertEquals(
            $result,
            $instance->quoteIsCheck($quote),
            'Check if that the type ' . $type . ' is ' . ($result ? '' : 'not ') . 'marked as Check type');
    }
}
