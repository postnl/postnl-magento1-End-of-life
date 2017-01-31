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
class TIG_PostNL_Test_Unit_Helper_DataTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_Data
     */
    public function _getInstance()
    {
        return Mage::helper('postnl');
    }

    /**
     * @param $type
     *
     * @return mixed
     * @throws Exception
     */
    protected function convertIDCheckType($type)
    {
        /** @var TIG_PostNL_Helper_DeliveryOptions $deliveryOptionsHelper */
        $deliveryOptionsHelper = Mage::app()->getConfig()->getHelperClassName('postnl/deliveryOptions');

        if ($type === 'AgeCheck') {
            return $deliveryOptionsHelper::IDCHECK_TYPE_AGE;
        } elseif ($type === 'BirthdayCheck') {
            return $deliveryOptionsHelper::IDCHECK_TYPE_BIRTHDAY;
        } elseif ($type === 'IDCheck') {
            return $deliveryOptionsHelper::IDCHECK_TYPE_ID;
        }

        return $type;
    }

    public function canUsePakjegemakNotInsuredDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * @dataProvider canUsePakjegemakNotInsuredDataProvider
     */
    public function testCanUsePakjegemakNotInsured($enabled, $result)
    {
        $instance = $this->_getInstance();
        $instance->setCache(false);

        Mage::app()->getStore()->setConfig(TIG_PostNL_Helper_Data::XPATH_ALLOW_PAKJEGEMAK_NOT_INSURED, $enabled);

        $this->assertEquals($result, $instance->canUsePakjeGemakBeNotInsured());
    }

    public function testCanUsePakjegemakNotInsuredUsesCache()
    {
        $value = uniqid();
        $instance = $this->_getInstance();

        $cacheMock = $this->getMock('TIG_PostNL_Model_Core_Cache', array(
            'hasPostnlCoreCanUsePakjegemakNotInsured',
            'getPostnlCoreCanUsePakjegemakNotInsured',
        ));

        $cacheMock
            ->expects($this->once())
            ->method('hasPostnlCoreCanUsePakjegemakNotInsured')
            ->willReturn(true);

        $cacheMock
            ->expects($this->once())
            ->method('getPostnlCoreCanUsePakjegemakNotInsured')
            ->willReturn($value);

        $this->setProperty('_cache', $cacheMock);

        $cache = $instance->getCache();
        $cache->setPostnlCoreCanUsePakjegemakNotInsured($value);

        $result = $instance->canUsePakjeGemakBeNotInsured();
        $this->assertEquals($value, $result);
    }

    public function testCanUsePakjegemakNotInsuredSavesCache()
    {
        $value = true;
        $instance = $this->_getInstance();

        Mage::app()->getStore()->setConfig(TIG_PostNL_Helper_Data::XPATH_ALLOW_PAKJEGEMAK_NOT_INSURED, $value);

        $cacheMock = $this->getMock('TIG_PostNL_Model_Core_Cache', array(
            'hasPostnlCoreCanUsePakjegemakNotInsured',
            'setPostnlCoreCanUsePakjegemakNotInsured',
        ));

        $cacheMock
            ->expects($this->once())
            ->method('hasPostnlCoreCanUsePakjegemakNotInsured')
            ->willReturn(false);

        $cacheMock
            ->expects($this->once())
            ->method('setPostnlCoreCanUsePakjegemakNotInsured')
            ->with($value)
            ->willReturnSelf();

        $this->setProperty('_cache', $cacheMock);

        $result = $instance->canUsePakjeGemakBeNotInsured();
        $this->assertEquals($value, $result);
    }

    public function checkIsQuoteProvider()
    {
        return array(
            array('quoteIsAgeCheck', 'AgeCheck', true),
            array('quoteIsAgeCheck', 'BirthdayCheck', false),
            array('quoteIsAgeCheck', 'IDCheck', false),
            array('quoteIsAgeCheck', 0, false),
            array('quoteIsAgeCheck', '0', false),
            array('quoteIsAgeCheck', '', false),
            array('quoteIsBirthdayCheck', 'AgeCheck', false),
            array('quoteIsBirthdayCheck', 'BirthdayCheck', true),
            array('quoteIsBirthdayCheck', 'IDCheck', false),
            array('quoteIsBirthdayCheck', 0, false),
            array('quoteIsBirthdayCheck', '0', false),
            array('quoteIsBirthdayCheck', '', false),
            array('quoteIsIDCheck', 'AgeCheck', false),
            array('quoteIsIDCheck', 'BirthdayCheck', false),
            array('quoteIsIDCheck', 'IDCheck', true),
            array('quoteIsIDCheck', 0, false),
            array('quoteIsIDCheck', '0', false),
            array('quoteIsIDCheck', '', false),
        );
    }

    /**
     * @dataProvider checkIsQuoteProvider
     *
     * @param $method
     * @param $oldType
     * @param $result
     *
     * @throws Exception
     */
    public function testQuoteIsCheck($method, $oldType, $result)
    {
        $type = $this->convertIDCheckType($oldType);

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

        $this->setRegistryKey('postnl_quote_is_age_check_' . 1);
        $this->setRegistryKey('postnl_quote_is_birthday_check_' . 1);
        $this->setRegistryKey('postnl_quote_is_id_check_' . 1);

        $instance = $this->_getInstance();
        $this->assertEquals(
            $result,
            $instance->$method($quote),
            'Check ' . $method . ' with type ' . $type
        );
    }

    public function getQuoteIdCheckTypeProvider()
    {
        return array(
            array(true, false, false, 'AgeCheck'),
            array(false, true, false, 'IDCheck'),
            array(false, false, true, 'BirthdayCheck'),
        );
    }

    /**
     * @param $ageCheck
     * @param $idCheck
     * @param $birthdayCheck
     * @param $oldResult
     *
     * @dataProvider getQuoteIdCheckTypeProvider
     */
    public function testGetQuoteIdCheckType($ageCheck, $idCheck, $birthdayCheck, $oldResult)
    {
        $quote = $this->getMock('Mage_Sales_Model_Quote');

        $quote->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->setRegistryKey('postnl_quote_is_age_check_1', $ageCheck);
        $this->setRegistryKey('postnl_quote_is_birthday_check_1', $birthdayCheck);
        $this->setRegistryKey('postnl_quote_is_id_check_1', $idCheck);

        $instance = $this->_getInstance();

        $result = $this->convertIDCheckType($oldResult);
        $this->assertEquals($result, $instance->getQuoteIdCheckType($quote), 'The result should be ' . $result);
    }

    public function isIdevOscProvider()
    {
        return array(
            array('magento_onepagecheckout', false),
            array('idev_onestepcheckout', true),
            array('gomage_lightcheckout', false),
            array('other', false),
        );
    }

    /**
     * @dataProvider isIdevOscProvider
     */
    public function testIsIdevOsc($value, $result)
    {
        $helper = $this->_getInstance();
        Mage::app()->getStore()->setConfig($helper::XPATH_CHECKOUT_EXTENSION, $value);

        $this->assertEquals($result, $helper->isIdevOsc());
    }

    public function quoteHasIDCheckProductsProvider()
    {
        return array(
            array(null, false, false, false, false),
            array(null, true, false, false, true),
            array(null, false, true, false, true),
            array(null, false, false, true, true),
            array(false, false, false, false, false),
            array(false, true, false, false, false),
            array(false, false, true, false, false),
            array(false, false, false, true, false),
        );
    }

    /**
     * @param $cacheValue
     * @param $isAgeCheck
     * @param $isBirthdayCheck
     * @param $isIDCheck
     * @param $expected
     *
     * @dataProvider quoteHasIDCheckProductsProvider
     */
    public function testQuoteHasIDCheckProducts($cacheValue, $isAgeCheck, $isBirthdayCheck, $isIDCheck, $expected)
    {
        $quote_id = rand(0, 2000);

        $quoteMock = $this->getMock('Mage_Sales_Model_Quote');

        $getIdExpected = $quoteMock->expects($this->any());
        $getIdExpected->method('getId');
        $getIdExpected->willReturn($quote_id);

        $this->setRegistryKey('postnl_quote_has_id_check_products_' . $quote_id, $cacheValue);
        $this->setRegistryKey('postnl_quote_is_age_check_' . $quote_id, $isAgeCheck);
        $this->setRegistryKey('postnl_quote_is_birthday_check_' . $quote_id, $isBirthdayCheck);
        $this->setRegistryKey('postnl_quote_is_id_check_' . $quote_id, $isIDCheck);

        $instance = $this->_getInstance();
        $result = $instance->quoteHasIDCheckProducts($quoteMock);

        $this->assertEquals($result, $expected);
    }
}
