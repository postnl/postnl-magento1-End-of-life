<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
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
class TIG_PostNL_Test_Unit_Helper_CifTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    private $defaultAvailableProductOptions;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->defaultAvailableProductOptions = Mage::getStoreConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_AVAILABLE_PRODUCT_OPTIONS
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        /**
         * Some unittests depend on the default config value of the product options.
         * By resetting this config option those tests won't be broken.
         */
        Mage::app()->getStore()->setConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_AVAILABLE_PRODUCT_OPTIONS,
            $this->defaultAvailableProductOptions
        );
    }

    /**
     * @return TIG_PostNL_Helper_Cif
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/cif');
    }

    /**
     * @return array
     */
    public function getExtraAtHomeProductCodesProvider()
    {
        return array(
            'no codes active' => array(
                '',
                0
            ),
            'no E@H codes active' => array(
                '3571,4878',
                0
            ),
            'single E@H codes active' => array(
                '3573,3575,3629',
                1
            ),
            'multiple E@H codes active' => array(
                '3445,3446,3653,3783,3790,4880',
                3
            ),
        );
    }

    /**
     * @param $productCodes
     * @param $expected
     *
     * @dataProvider getExtraAtHomeProductCodesProvider
     */
    public function testGetExtraAtHomeProductCodes($productCodes, $expected)
    {
        Mage::app()->getStore()->setConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_AVAILABLE_PRODUCT_OPTIONS,
            $productCodes
        );

        $instance = $this->_getInstance();
        $result = $instance->getExtraAtHomeProductCodes(true);

        $this->assertInternalType('array', $result);
        $this->assertCount($expected, $result);
    }
}
