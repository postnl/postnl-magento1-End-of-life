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
class TIG_PostNL_Test_Unit_Model_DeliveryOptions_Observer_IdCheckTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck
     */
    protected function _getInstance()
    {
        return new TIG_PostNL_Model_DeliveryOptions_Observer_IdCheck();
    }

    public function validateProvider()
    {
        return array(
            array(
                'IDCheck',
                array(),
                'Please provide a document type'
            ),
            array(
                'IDCheck',
                array(
                    'postnl_idcheck' => array(
                        'type' => 'wrong',
                    ),
                ),
                'Please provide a valid document type'
            ),
            array(
                'IDCheck',
                array(
                    'postnl_idcheck' => array(
                        'type' => 'abroad_passport',
                        'number' => '',
                    ),
                ),
                'Please provide a document number',
            ),
            array(
                'IDCheck',
                array(
                    'postnl_idcheck' => array(
                        'type' => 'abroad_passport',
                        'number' => '1234',
                        'expiration_date_full' => '',
                    ),
                ),
                'Please provide a expiration date',
            ),
            array(
                'IDCheck',
                array(
                    'postnl_idcheck' => array(
                        'type' => 'abroad_passport',
                        'number' => '1234',
                        'expiration_date_full' => '12-04-2112',
                    ),
                ),
                false
            ),
            array('AgeCheck', array(), false),
            array('BirthdayCheck', array(), 'Please provide a valid birthday'),
            array('BirthdayCheck', array('billing'=>array('dob' => '29-09-1999')), false),
        );
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($shipmentType, $postData, $error)
    {
        $_POST = $postData;

        $mockHelper = $this->getMock('TIG_PostNL_Helper_Data', array('getQuoteIdCheckType'));
        $mockHelper->expects($this->once())
            ->method('getQuoteIdCheckType')
            ->willReturn($shipmentType);

        $addressMock = $this->getMock('Mage_Sales_Model_Quote_Address');

        if ($error) {
            $addressMock->expects($this->once())
                ->method('addError')
                ->with($error);
        } else {
            $addressMock->expects($this->never())
                ->method('addError');
        }

        $observer = $this->getMock('Varien_Event_Object', array('getAddress'));
        $observer->expects($this->once())
            ->method('getAddress')
            ->willReturn($addressMock);

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', array('postnl' => $mockHelper), $instance);

        $instance->validate($observer);
    }

    public function saveDataProvider()
    {
        return array(
            array(
                'IDCheck',
                true,
                array(
                    'postnl_idcheck' => array(
                        'type' => 'abroud_passport',
                        'number' => '12345',
                        'expiration_date_full' => '12-12-2112',
                    ),
                )
            ),
            array('AgeCheck', true, array()),
            array('BirthdayCheck', true, array('billing'=>array('dob'=>'29-09-1999'))),
            array('BirthdayCheck', false, array('billing'=>array('dob'=>'29-09-1999'))),
        );
    }

    /**
     * @dataProvider saveDataProvider
     */
    public function testSaveData($shipmentType, $dobIsVisible, $postData)
    {
        $_POST = $postData;

        $mockHelper = $this->getMock('TIG_PostNL_Helper_Data');

        $mockHelper->expects($this->once())
            ->method('getQuoteIdCheckType')
            ->willReturn($shipmentType);

        $mockOrder = $this->getMock('TIG_PostNL_Model_Core_Order', array(
            'setIdcheckType',
            'setIdcheckNumber',
            'setIdcheckExpirationDate',
        ));

        $mockService = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Service');
        $mockService->expects($this->once())
            ->method('saveDeliveryOption')
            ->with($this->callback(function ($arguments) use (
                $shipmentType
            ) {
                if ($shipmentType != $arguments['type']) {
                    return false;
                }

                return true;
            }));

        $mockService->expects($this->any())
            ->method('getPostnlOrder')
            ->willReturn($mockOrder);

        $this->registerMockSessions(array('checkout'));
        $mockQuote = $this->getMock('Mage_Sales_Model_Quote', array('setCustomerDob'));

        Mage::getSingleton('eav/config')->getAttribute('customer', 'dob')->setIsVisible($dobIsVisible);

        if ($shipmentType == 'BirthdayCheck' && !$dobIsVisible) {
            $mockQuote->expects($this->once())
                ->method('setCustomerDob')
                ->with($postData['billing']['dob']);
        } elseif ($shipmentType == 'IDCheck') {
            $mockOrder->expects($this->once())
                ->method('setIdcheckType')
                ->with($postData['postnl_idcheck']['type']);

            $mockOrder->expects($this->once())
                ->method('setIdcheckNumber')
                ->with($postData['postnl_idcheck']['number']);

            $mockOrder->expects($this->once())
                ->method('setIdcheckExpirationDate')
                ->with($postData['postnl_idcheck']['expiration_date']);
        }

        $mockSession = Mage::getSingleton('checkout/session');
        $mockSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($mockQuote));

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', array('postnl' => $mockHelper), $instance);
        $this->setProperty('_serviceModel', $mockService, $instance);

        $observer = $this->getMock('Varien_Event_Object');

        $instance->saveData($observer);
    }
}