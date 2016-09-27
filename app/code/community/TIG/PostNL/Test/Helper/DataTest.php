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
class TIG_PostNL_Test_Helper_DataTest extends TIG_PostNL_Test_Framework_TIG_Test_TestCase
{
    public function _getInstance()
    {
        return Mage::helper('postnl');
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
}
