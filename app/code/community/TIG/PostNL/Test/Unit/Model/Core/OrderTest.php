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
class TIG_PostNL_Test_Unit_Model_Core_OrderTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected function _getInstance()
    {
        return Mage::getModel('postnl_core/order');
    }

    /**
     * @test
     */
    public function shouldBeTheRightClass()
    {
        $instance = $this->_getInstance();

        $this->assertInstanceOf('TIG_PostNL_Model_Core_Order', $instance);
    }

    /**
     * @test
     */
    public function shouldBeAbleToSetMobilePhoneNumber()
    {
        $instance = $this->_getInstance();
        $instance->setMobilePhoneNumber('testNumber', true);

        $this->assertEquals('testNumber', $instance->getData('mobile_phone_number'));
    }

    /**
     * @param $number
     * @param $expected
     *
     * @test
     *
     * @dataProvider phoneProvider
     */
    public function shouldBeAbleToParseMobilePhoneNumber($number, $expected)
    {
        $instance = $this->_getInstance();
        $instance->setMobilePhoneNumber($number);

        $this->assertSame($expected, $instance->getData('mobile_phone_number'));
    }

    /**
     * @return array
     */
    public function phoneProvider()
    {
        return array(
            array(
                'number'   => '0612345678',
                'expected' => '+31612345678',
            ),
            array(
                'number'   => '0031687654321',
                'expected' => '+31687654321',
            ),
            array(
                'number'   => '+31641103362',
                'expected' => '+31641103362',
            ),
        );
    }

    /**
     * @test
     *
     * @expectedException TIG_PostNL_Exception
     * @expectedExceptionCode POSTNL-0149
     */
    public function shouldThrowAnExceptionIfInvalidPhoneNumber()
    {
        $instance = $this->_getInstance();
        $instance->setMobilePhoneNumber('testNumber');
    }

    public function isCheckProvider()
    {
        return array(
            array('AgeCheck', true),
            array('BirthdayCheck', true),
            array('IDCheck', true),
            array(3, true),
            array(4, true),
            array(5, true),
            array('wrong', false),
        );
    }

    /**
     * @dataProvider isCheckProvider
     */
    public function testIsIDCheck($type, $response)
    {
        $model = Mage::getModel('postnl_core/order');
        $model->setType($type);

        $this->assertEquals($response, $model->isIDCheck());
    }
}
