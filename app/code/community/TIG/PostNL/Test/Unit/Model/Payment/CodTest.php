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
            '1' => array(
                false, // $useQuote
                false, // $isActive
                false, // $isVirtual
                false, // $isFood
                false, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                'PostNL COD is not available, because the quote is empty.' // $logMessage
            ),
            '2' => array(
                true, // $useQuote
                false, // $isActive
                false, // $isVirtual
                false, // $isFood
                false, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                false // $logMessage
            ),
            '3' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                false, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                false // $logMessage
            ),
            '4' => array(
                true, // $useQuote
                true, // $isActive
                true, // $isVirtual
                false, // $isFood
                false, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                'PostNL COD is not available, because the order is virtual.' // $logMessage
            ),
            '5' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                true, // $isFood
                false, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                false // $logMessage
            ),
            '6' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                false, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL',  // $country
                false, // $expected
                'PostNL COD is not available, because required fields are missing.' // $logMessage
            ),
            '7' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                true, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'US', // $country
                false, // $expected
                'PostNL COD is not available, because the shipping destination country is not allowed.' // $logMessage
            ),
            '8' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                true, // $codSettings
                true, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                'PostNL COD is not available, because the shipping address is a P.O. box.' // $logMessage
            ),
            '9' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                true, // $codSettings
                false, // $isPostbus
                true, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                'PostNL Cod is not available, because COD is not allowed in combination with Sunday Delivery.' // $logMessage
            ),
            'contains Extra@Home product' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                true, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                true, // $quoteIsExtraAtHome
                'NL', // $country
                false, // $expected
                '' // $logMessage
            ),
            'all tests are ok' => array(
                true, // $useQuote
                true, // $isActive
                false, // $isVirtual
                false, // $isFood
                true, // $allowForNonPostNL
                true, // $codSettings
                false, // $isPostbus
                false, // $isSunday
                false, // $quoteIsExtraAtHome
                'NL', // $country
                true, // $expected
                '' // $logMessage
            ),
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
     * @param $isSunday
     * @param $quoteIsExtraAtHome
     * @param $country
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
        $isSunday,
        $quoteIsExtraAtHome,
        $country,
        $expected,
        $logMessage
    )
    {
        $quote_id = 69;
        $models = array();
        $helpers = array();
        $store = Mage::app()->getStore();

        $quote = null;
        if ($useQuote) {
            $shippingAddress = $this->getMock('Mage_Sales_Model_Quote_Address');

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
                ->method('getId')
                ->willReturn($quote_id);

            $quote->expects($this->any())
                ->method('isVirtual')
                ->willReturn($isVirtual);

            $quote->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($shippingAddress);
        }

        $dataHelper = $this->getMock('TIG_PostNL_Helper_Data');
        $paymentHelper = $this->getMock('TIG_PostNL_Helper_Payment');
        $helpers['postnl'] = $dataHelper;
        $helpers['postnl/payment'] = $paymentHelper;

        $dataHelper->expects($this->any())
            ->method('quoteIsExtraAtHome')
            ->willReturn($quoteIsExtraAtHome);

        $paymentHelper->expects($this->any())
            ->method('quoteIsFood')
            ->willReturn($isFood);

        if ($logMessage) {
            $paymentHelper->expects($this->once())
                ->method('__')
                ->with($logMessage);

        }

        $postnlOrder = $this->getMock('TIG_PostNL_Core_Order', array('load', 'getType'));
        $models['postnl_core/order'] = $postnlOrder;

        $postnlOrder->expects($this->any())
            ->method('load')
            ->with($quote_id, 'quote_id')
            ->willReturnSelf();

        $postnlOrder->expects($this->any())
            ->method('getType')
            ->willReturn($isSunday ? 'Sunday' : 'Other');

        Mage::app()->getStore()->setConfig('payment/postnl_cod/allowspecific', 1);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/active', $isActive);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/allow_for_non_postnl', $allowForNonPostNL);
        Mage::app()->getStore()->setConfig('payment/postnl_cod/specificcountry', 'NL,BE');

        $bicnumber = $ibannumber = $accountnumber = '';
        if ($codSettings) {
            $bicnumber = 'bicnumber';
            $ibannumber = 'ibannumber';
            $accountnumber = 'accountnumber';
        }

        Mage::app()->getStore()->setConfig('postnl/cod/bic', $bicnumber);
        Mage::app()->getStore()->setConfig('postnl/cod/iban', $ibannumber);
        Mage::app()->getStore()->setConfig('postnl/cod/account_name', $accountnumber);

        Mage::app()->getStore()->setConfig('postnl/cod', array(
            'bic' => $bicnumber,
            'iban' => $ibannumber,
            'account_name' => $accountnumber,
        ));

        $instance = $this->_getInstance();
        $this->setProperty('_models', $models, $instance);
        $this->setProperty('_helpers', $helpers, $instance);
        $result = $instance->isAvailable($quote);

        $this->assertEquals($expected, $result);
    }
}
