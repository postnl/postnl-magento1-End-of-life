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
class TIG_PostNL_Test_Unit_Model_Payment_CodTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Model_Payment_Cod
     */
    protected function _getInstance()
    {
        return Mage::getModel('postnl_payment/cod');
    }

    public function isAvailableDataProvider()
    {
        return array(
            array(false, false, false, false, false, false, false, 'NL', 'NL,BE', false, 'PostNL COD is not available, because the quote is empty.'),
            array(true, false, false, false, false, false, false, 'NL', 'NL,BE', false, false),
            array(true, true, false, false, false, false, false, 'NL', 'NL,BE', false, false),
            array(true, true, true, false, false, false, false, 'NL', 'NL,BE', false, 'PostNL COD is not available, because the order is virtual.'),
            array(true, true, false, true, false, false, false, 'NL', 'NL,BE', false, false),
            array(true, true, false, false, true, false, false, 'NL', 'NL,BE', false, 'PostNL COD is not available, because required fields are missing.'),
            array(true, true, false, false, true, true, false, 'US', 'NL,BE', false, 'PostNL COD is not available, because the shipping destination country is not allowed.'),
            array(true, true, false, false, true, true, true, 'NL', 'NL,BE', false, 'PostNL COD is not available, because the shipping address is a P.O. box.'),
            array(true, true, false, false, true, true, false, 'NL', 'NL,BE', true, false),
        );
    }

    /**
     * @param $useQuote
     * @param $isActive
     * @param $isVirtual
     * @param $isFood
     * @param $allowForNonPostNL
     * @param $codSettings
     * @param $isPostbus
     * @param $country
     * @param $allowedCountries
     * @param $expected
     * @param $logMessage
     *
     * @internal     param $result
     *
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable(
        $useQuote,
        $isActive,
        $isVirtual,
        $isFood,
        $allowForNonPostNL,
        $codSettings,
        $isPostbus,
        $country,
        $allowedCountries,
        $expected,
        $logMessage
    )
    {
        $helpers = array();
        $store = Mage::app()->getStore();

        $quote = null;
        if ($useQuote) {
            $shippingAddress = $this->getMOck('Mage_Sales_Model_Quote_Address');

            $shippingAddress->expects($this->any())
                ->method('getStreetFull')
                ->willReturn($isPostbus ? 'thisisapostbuslocation' : 'kabelweg');

            $shippingAddress->expects($this->any())
                ->method('getCountry')
                ->willReturn($country);

            $quote = $this->getMock('Mage_Sales_Model_Quote');

            $quote->expects($this->atLeastOnce())
                ->method('getStoreId')
                ->willReturn($store->getId());

            $quote->expects($this->any())
                ->method('isVirtual')
                ->willReturn($isVirtual);

            $quote->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($shippingAddress);
        }

        $paymentHelper = $this->getMock('TIG_PostNL_Helper_Payment');
        $helpers['postnl/payment'] = $paymentHelper;

        $paymentHelper->expects($this->any())
            ->method('quoteIsFood')
            ->willReturn($isFood);

        if ($logMessage) {
            $paymentHelper->expects($this->once())
                ->method('__')
                ->with($logMessage);

        }

        Mage::app()->getStore()->setConfig('payment/postnl_cod/allowspecific', 1);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/active', $isActive);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/allow_for_non_postnl', $allowForNonPostNL);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/specificcountry', $allowedCountries);

        if ($codSettings) {
            Mage::app()->getStore()->setConfig('postnl/cod/bic', 'bicnumber');
            Mage::app()->getStore()->setConfig('postnl/cod/iban', 'ibannumber');
            Mage::app()->getStore()->setConfig('postnl/cod/account_name', 'accountnumber');

            Mage::app()->getStore()->setConfig('postnl/cod', array(
                'bic' => 'bicnumber',
                'iban' => 'ibannumber',
                'account_name' => 'accountnumber',
            ));
        }

        $instance = $this->_getInstance();
        $this->setProperty('_helpers', $helpers, $instance);
        $result = $instance->isAvailable($quote);

        $this->assertEquals($expected, $result);
    }
}