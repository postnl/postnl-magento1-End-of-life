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
class TIG_PostNL_Test_Unit_Block_DeliveryOptions_Checkout_DeliveryOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected function _getInstance()
    {
        return new TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions();
    }

    public function _getDeliveryDateProvider()
    {
        return array(
            array('', 'BE', 'delivery', null, true, true, 'POSTNL-0131', 'Invalid postcode supplied for GetDeliveryDate request:  Postcodes may only contain 4 numbers.'),
            array('aa', 'BE', 'delivery', null, true, true, 'POSTNL-0131', 'Invalid postcode supplied for GetDeliveryDate request: AA Postcodes may only contain 4 numbers.'),
            array('', 'NL', 'delivery', null, true, true, 'POSTNL-0131', 'Invalid postcode supplied for GetDeliveryDate request:  Postcodes may only contain 4 numbers and 2 letters.'),
            array('aa', 'NL', 'delivery', null, true, true, 'POSTNL-0131', 'Invalid postcode supplied for GetDeliveryDate request: AA Postcodes may only contain 4 numbers and 2 letters.'),
            array('1234ab', 'US', 'delivery', null, true, true, 'POSTNL-0235', 'Invalid country supplied for GetDeliveryDate request: US. Only "NL" and "BE" are allowed.'),

            array('1234AB', 'NL', 'delivery', '01-01-2001', true, true, null, null, '01-01-2001'),
            array('1234AB', 'NL', 'pickup', '01-01-2001', true, false, null, null, '01-01-2001'),
            array('1234AB', 'NL', 'pickup', '01-01-2001', true, true, null, null, '02-01-2001'),
            array('1234AB', 'BE', 'pickup', '01-01-2001', true, true, null, null, '01-01-2001'),
            array('1234AB', 'NL', 'pickup', '01-01-2001', false, true, null, null, '01-01-2001'),
        );
    }

    /**
     * @param $postcode
     * @param $country
     * @param $for
     * @param $cifResponse
     * @param $canUseSamedayDelivery
     * @param $isPastCutoff
     * @param $errorCode
     * @param $errorMessage
     *
     * @param $expected
     *
     * @throws Exception
     * @throws TIG_PostNL_Exception
     * @dataProvider _getDeliveryDateProvider
     */
    public function test_getDeliveryDate(
        $postcode,
        $country,
        $for,
        $cifResponse,
        $canUseSamedayDelivery,
        $isPastCutoff,
        $errorCode,
        $errorMessage,
        $expected = null
    )
    {
        $instance = $this->_getInstance();

        $quoteMock = $this->getMock('Mage_Sales_Model_Quote');

        $cifModelMock = $this->getMock('TIG_PostNL_Model_DeliveryOptions_Cif', array('setStoreId', 'getDeliveryDate'));

        $cifModelMock->expects($this->any())
            ->method('setStoreId')
            ->withAnyParameters()
            ->willReturnSelf();

        $cifModelMock->expects($this->any())
            ->method('getDeliveryDate')
            ->with($postcode, $country, $quoteMock, $for)
            ->willReturn($cifResponse);

        $deliverOptionsHelperMock = $this->getMock('TIG_PostNL_Helper_DeliveryOptions');

        $deliverOptionsHelperMock->expects($this->any())
            ->method('canUseSameDayDelivery')
            ->willReturn($canUseSamedayDelivery);

        $dateHelperMock = $this->getMock('TIG_PostNL_Helper_Date');

        $dateHelperMock->expects($this->any())
            ->method('isPastCutOff')
            ->willReturn($isPastCutoff);

        $dateHelperMock->expects($this->any())
            ->method('getDeliveryDateCorrection')
            ->willReturn(0);

        $this->setProperty('_models', array('postnl_deliveryoptions/cif' => $cifModelMock), $instance);
        $this->setProperty('_helpers', array(
            'postnl/deliveryOptions' => $deliverOptionsHelperMock,
            'postnl/date' => $dateHelperMock,
        ), $instance);

        try {
            $method = new ReflectionMethod(get_class($instance), '_getDeliveryDate');
            $method->setAccessible(true);
            $result = $method->invokeArgs($instance, array(
                $postcode,
                $country,
                $quoteMock,
                $for,
            ));

            $this->assertEquals($expected, $result, 'Check the result returned by _getDeliveryDate');
        } catch (TIG_PostNL_Exception $e) {
            if ($errorCode !== null) {
                $this->assertEquals($errorCode, $e->getCode(), 'The retrieved exception has the same code');
                $this->assertEquals($errorMessage, $e->getMessage(), 'The retrieved exception has the same message');
            } else {
                throw $e;
            }
        }
    }
}
