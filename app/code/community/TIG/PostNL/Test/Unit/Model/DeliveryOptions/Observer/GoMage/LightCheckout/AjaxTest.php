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
class TIG_PostNL_Test_Unit_Model_DeliveryOptions_Observer_GoMage_LightCheckout_AjaxTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected function observerMock($action = 'save_payment_methods')
    {
        $observer = $this->getMock('Varien_Event_Observer', array('getControllerAction', 'getRequest', 'getParam'));

        $observer->expects($this->once())
            ->method('getControllerAction')
            ->willReturnSelf();

        $observer->expects($this->once())
            ->method('getRequest')
            ->willReturnSelf();

        $observer->expects($this->once())
            ->method('getParam')
            ->with('action', false)
            ->willReturn($action);

        return $observer;
    }

    public function validateIdCheckProvider()
    {
        return array(
            array('save_payment_methods', '{"test":true}', array('error' => false, 'message' => null)),
            array('save_payment_methods', '{"error":true,"message":"got an error"}', array('error' => true, 'message' => 'got an error')),
            array('get_totals', '{"test":true}', false),
        );
    }

    /**
     * @param $action
     * @param $expected
     * @param $validate
     *
     * @dataProvider validateIdCheckProvider
     */
    public function testValidateIdCheck($action, $expected, $validate)
    {
        $observerMock = $this->observerMock($action);

        $idCheckMock = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck');

        $idCheckMock->expects($this->any())
            ->method('validate')
            ->willReturn($validate);

        $idCheckMock->expects($this->any())
            ->method('saveData');

        Mage::app()->getResponse()->setBody('{"test":true}');

        $instance = new TIG_PostNL_Model_DeliveryOptions_Observer_GoMage_LightCheckout_Ajax;
        $this->setProperty('_idCheckObserverModel', $idCheckMock, $instance);
        $instance->validateIdCheck($observerMock);

        $result = Mage::app()->getResponse()->getBody();
        $this->assertEquals($expected, $result);
    }
}
