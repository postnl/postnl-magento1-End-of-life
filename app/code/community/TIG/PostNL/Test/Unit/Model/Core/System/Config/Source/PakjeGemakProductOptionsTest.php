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
class TIG_PostNL_Test_Unit_Model_Core_System_Config_Source_PakjeGemakProductOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions
     */
    protected $_instance;

    public function setUp()
    {
        /** @var TIG_PostNL_Model_Core_System_Config_Source_PakjeGemakProductOptions _instance */
        $this->_instance = Mage::getSingleton('postnl_core/system_config_source_pakjeGemakProductOptions');

        $helper = Mage::helper('postnl');
        $helper->setCache(false);

        $this->setProperty('_helper', $helper, $this->_instance);
    }

    public function hasPakjegemakBeProductCodesDataProvider()
    {
        return array(
            array('4878'),
            array('4880'),
        );
    }

    /**
     * @dataProvider hasPakjegemakBeProductCodesDataProvider
     */
    public function testHasPakjegemakBeProductCodes($productCode)
    {
        $hasOption = false;
        $options = $this->_instance->getOptions();

        foreach ($options as $option) {
            if ($option['value'] == $productCode) {
                $hasOption = true;
                break;
            }
        }

        $this->assertTrue($hasOption);
    }

    public function hasPakjegemakNotInsuredDataProvider()
    {
        return array(
            array(true, true),
            array(false, false),
            array(true, true, array('isBelgiumOnly' => true)),
            array(true, false, array('isBelgiumOnly' => false)),
            array(true, false, array('isExtraCover' => true)),
            array(true, true, array('isExtraCover' => false)),
            array(true, false, array('isCod' => true)),
            array(true, true, array('isCod' => false)),
            array(true, false, array('isPge' => true)),
            array(true, true, array('isPge' => false)),
        );
    }

    /**
     * @dataProvider hasPakjegemakNotInsuredDataProvider
     */
    public function testHasPakjegemakNotInsured($enabled, $available, $flags = array())
    {
        $helper = Mage::helper('postnl');
        $cache = $helper->getCache();
        $helper->setCache(false);

        $this->setProperty('_helper', $helper, $this->_instance);

        Mage::app()->getStore()->setConfig(TIG_PostNL_Helper_Data::XPATH_ALLOW_PAKJEGEMAK_NOT_INSURED, $enabled);

        $hasOption = false;
        $options = $this->_instance->getOptions($flags);
        foreach ($options as $option) {
            if ($option['value'] == 4936) {
                $hasOption = true;
                break;
            }
        }

        $this->assertEquals($available, $hasOption);
        $helper->setCache($cache);
    }

    public function hasIDCheckPakjegemakProvider()
    {
        return array(
            array('getAgeCheckOptions'),
            array('getBirthdayCheckOptions'),
            array('getIDCheckOptions'),
        );
    }

    /**
     * @dataProvider hasIDCheckPakjegemakProvider
     */
    public function testHasIDCheckPakjegemak($method)
    {
        $options = $this->_instance->$method();
        $this->assertNotEquals(0, count($options), 'Assert that the getIDCheckOptions method returns options');
    }
}
