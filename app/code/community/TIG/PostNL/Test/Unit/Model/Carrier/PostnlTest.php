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
class TIG_PostNL_Test_Unit_Model_Carrier_PostnlTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Carrier_Postnl
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_carrier/postnl');
    }

    /**
     * @test
     */
    public function itShouldBeOfTheProperInstance()
    {
        $carrier = $this->_getInstance();

        $this->assertInstanceOf('TIG_PostNL_Model_Carrier_Postnl', $carrier);
    }

    public function collectRatesProvider()
    {
        $foodErrorMessage = 'Food shipments can only be delivered within the Netherlands.';
        $idCheckErrorMessage = 'ID Check shipments can only be delivered within the Netherlands.';

        return array(
            /** Is Active */
            array(false, null, null, null, null, false),

            /** Country ID */
            array(true, 'NL', 'NL', null, null, false),

            /** Food error */
            array(true, null, 'BE', true, 'food', $foodErrorMessage),

            /** IDCHeck error */
            array(true, null, 'BE', true, 'agecheck', $idCheckErrorMessage),
            array(true, null, 'BE', true, 'birthdaycheck', $idCheckErrorMessage),
            array(true, null, 'BE', true, 'idcheck', $idCheckErrorMessage),

            array(true, null, null, false, 'idcheck', true),
        );
    }

    /**
     * @param $isActive
     * @param $domesticCountry
     * @param $shippingCountry
     * @param $hasError
     * @param $shipmentType
     * @param $expected
     *
     * @dataProvider collectRatesProvider
     */
    public function testCollectRates(
        $isActive,
        $domesticCountry,
        $shippingCountry,
        $hasError,
        $shipmentType,
        $expected
    )
    {
        $carrier = $this->_getInstance();

        $requestMock = $this->getMock('Mage_Shipping_Model_Rate_Request', array('getDestCountryId', 'getParcelType'));
        $helperMock = $this->getMock('TIG_PostNL_Helper_Carrier', array(
            'getDomesticCountry',
            'canUseStandard',
            'canUseEps'
        ));
        $carrier->setData('helper', $helperMock);

        $destCountryId = $requestMock->expects($this->any());
        $destCountryId->method('getDestCountryId');
        $destCountryId->willReturn($shippingCountry);

        Mage::app()->getStore()->setConfig('carriers/postnl/active', $isActive);

        $domesticCountryExpects = $helperMock->expects($this->any());
        $domesticCountryExpects->method('getDomesticCountry');
        $domesticCountryExpects->willReturn($domesticCountry);

        $canUseStandard = $helperMock->expects($this->any());
        $canUseStandard->method('canUseStandard');
        $canUseStandard->willReturn(false);

        $canUseStandard = $helperMock->expects($this->any());
        $canUseStandard->method('canUseEps');
        $canUseStandard->willReturn(true);

        $getParcelType = $requestMock->expects($this->any());
        $getParcelType->method('getParcelType');
        $getParcelType->willReturn($shipmentType);

        $result = $carrier->collectRates($requestMock);

        if ($hasError) {
            /** @var Mage_Shipping_Model_Rate_Result $result */
            $this->assertInstanceOf('Mage_Shipping_Model_Rate_Result', $result);

            $rates = $result->getAllRates();

            /** @var Mage_Shipping_Model_Rate_Result_Error $errorRate */
            $errorRate = $rates[0];

            $this->assertInstanceOf('Mage_Shipping_Model_Rate_Result_Error', $errorRate);

            $this->assertEquals($expected, $errorRate->getErrorMessage());
        } elseif($expected === false) {
            $this->assertEquals($expected, $result);
        } else {
            /** @var Mage_Shipping_Model_Rate_Result $result */
            $this->assertInstanceOf('Mage_Shipping_Model_Rate_Result', $result);
        }
    }
}
