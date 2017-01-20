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
class TIG_PostNL_Test_Unit_Model_Core_System_Config_Source_BirthdayCheckPakjegemakProductOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var TIG_PostNL_Model_Core_System_Config_Source_BirthdayCheckProductOptions
     */
    protected $_instance;

    public function setUp()
    {
        $this->_instance = Mage::getSingleton('postnl_core/system_config_source_BirthdayCheckPakjegemakProductOptions');
    }

    /**
     * @return array
     */
    public function hasBirtdayCheckProductCodesDataProvider()
    {
        return array(
            array('3572'),
            array('3575'),
            array('3582'),
            array('3585'),
        );
    }

    /**
     * @dataProvider hasBirtdayCheckProductCodesDataProvider
     * @param $productCode
     */
    public function testHasBirthdayCheckProductCodes($productCode)
    {
        $hasOption = false;
        $options   = $this->_instance->getOptions();

        foreach ($options as $option) {
            if ($option['value'] == $productCode) {
                $hasOption = true;
                break;
            }
        }

        $this->assertTrue($hasOption);
    }
}
