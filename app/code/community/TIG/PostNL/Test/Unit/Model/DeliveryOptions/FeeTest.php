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
class TIG_PostNL_Test_Unit_Model_DeliveryOptions_FeeTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    protected $_class = 'TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions';

    /**
     * @return TIG_PostNL_Block_DeliveryOptions_Checkout_DeliveryOptions
     */
    protected function _getInstance()
    {
        return new $this->_class();
    }

    /**
     * @test
     *
     * @dataProvider saveTypeProvider
     *
     * @param string $type
     * @param float $result
     *
     * TODO: Change this to an integration test to handle different config values (use $free_shipping_fee and $path)
     */
    public function testFees($type, $result, $free_shipping_fee, $path)
    {
        $block = $this->_getInstance();

        $this->assertInstanceOf($this->_class, $block);

        $this->assertEquals($block->getFee($type), $result);
    }

    /**
     * @return array
     */
    public function saveTypeProvider()
    {
        return array(
            array(
                'type'              => 'evening',
                'result'            => 2,
                'free_shipping_fee' => '0',
                'path'              => 'postnl/delivery_options/evening_timeframe_fee',
            ),
            array(
                'type'              => 'sunday',
                'result'            => 0,
                'free_shipping_fee' => '0',
                'path'              => 'postnl/delivery_options/sunday_delivery_fee',
            ),
            array(
                'type'              => 'sameday',
                'result'            => 0,
                'free_shipping_fee' => '0',
                'path'              => 'postnl/delivery_options/sameday_delivery_fee',
            ),
            array(
                'type'              => null,
                'result'            => 0,
                'free_shipping_fee' => '0',
                'path'              => null,
            ),
        );
    }
}
