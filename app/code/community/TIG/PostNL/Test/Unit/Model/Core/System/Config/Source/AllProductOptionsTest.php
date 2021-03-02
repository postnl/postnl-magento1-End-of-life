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
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Model_Core_System_Config_Source_AllProductOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions|null
     */
    protected $_instance = null;

    /**
     * @return TIG_PostNL_Model_Core_System_Config_Source_AllProductOptions
     */
    public function _getInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = Mage::getSingleton('postnl_core/system_config_source_allProductOptions');
        }

        return $this->_instance;
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
        $options = $this->getProtectedPropertyValue('_options');

        $this->assertArrayHasKey($productCode, $options);
    }

    /**
     * @return array
     */
    public function hasExtraAtHomeProductCodesProvider()
    {
        return array(
            array('3628'),
            array('3629'),
            array('3653'),
            array('3783'),
            array('3790'),
            array('3791'),
            array('3792'),
            array('3793'),
        );
    }

    /**
     * @param $productCode
     *
     * @dataProvider hasExtraAtHomeProductCodesProvider
     */
    public function testHasExtraAtHomeProductCodes($productCode)
    {
        $instance = $this->_getInstance();
        $options = $instance->getOptions(array(), true);

        $this->assertArrayHasKey($productCode, $options);
    }
}
