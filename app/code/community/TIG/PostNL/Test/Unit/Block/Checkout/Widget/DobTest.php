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
class TIG_PostNL_Test_Unit_Block_Checkout_Widget_DobTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_Block_Checkout_Widget_Dob';

    /**
     * @var null|TIG_PostNL_Helper_DeliveryOptions|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper = null;

    protected function setUp()
    {
        $this->_helper = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');
    }

    /**
     * @return TIG_PostNL_Block_Checkout_Widget_Dob
     */
    protected function _getInstance()
    {
        $class = new $this->_class();
        $this->setProperty('_helper', $this->_helper, $class);

        return $class;
    }

    public function dataProvider()
    {
        return array(
            array('isEnabled', 'opt', true, true, true),
            array('isEnabled', 'opt', false, true, true),
            array('isEnabled', 'opt', false, false, true),
            array('isEnabled', 'opt', true, false, true),

            array('isEnabled', 'req', true, true, true),
            array('isEnabled', 'req', false, true, true),
            array('isEnabled', 'req', false, false, true),
            array('isEnabled', 'req', true, false, true),

            array('isEnabled', '', true, true, true),
            array('isEnabled', '', false, true, false),
            array('isEnabled', '', false, false, false),
            array('isEnabled', '', true, false, false),

            array('isRequired', 'opt', true, true, true),
            array('isRequired', 'opt', false, true, false),
            array('isRequired', 'opt', false, false, false),
            array('isRequired', 'opt', true, false, false),

            array('isRequired', 'req', true, true, true),
            array('isRequired', 'req', false, true, true),
            array('isRequired', 'req', false, false, true),
            array('isRequired', 'req', true, false, true),

            array('isRequired', '', true, true, true),
            array('isRequired', '', false, true, false),
            array('isRequired', '', false, false, false),
            array('isRequired', '', true, false, false),
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsEnabled(
        $method,
        $dobValue,
        $canUseBirthdayCheckDelivery,
        $quoteIsBirthdayCheck,
        $result
    ) {
        Mage::app()->getStore()->setConfig('customer/address/dob_show', $dobValue);

        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'dob');
        $attribute->setData('is_visible', $dobValue != '' ? '1' : '0');
        $attribute->setData('is_required', $dobValue == 'req' ? '1' : '0');

        $this->_helper->expects($this->any())
            ->method('canUseBirthdayCheckDelivery')
            ->willReturn($canUseBirthdayCheckDelivery);

        $this->_helper->expects($this->any())
            ->method('quoteIsBirthdayCheck')
            ->willReturn($quoteIsBirthdayCheck);

        $this->assertEquals(
            $result,
            $this->_getInstance()->$method(),
            'Check that ' . $method . ' with' . PHP_EOL .
            '$dobValue on "' . $dobValue . '"' . PHP_EOL .
            '$canUseBirthdayCheckDelivery on ' . ($canUseBirthdayCheckDelivery ? 'true' : 'false') . PHP_EOL .
            '$quoteIsBirthdayCheck on ' . ($quoteIsBirthdayCheck ? 'true' : 'false') . PHP_EOL .
            'will return ' . ($result ? 'true' : 'false')
        );
    }
}
