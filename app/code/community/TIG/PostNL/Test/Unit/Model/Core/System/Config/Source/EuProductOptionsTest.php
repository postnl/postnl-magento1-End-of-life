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
class TIG_PostNL_Test_Unit_Model_Core_System_Config_Source_EuProductOptionsTest
    extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @var TIG_PostNL_Model_Core_System_Config_Source_EuProductOptions
     */
    protected $_instance;

    public function setUp()
    {
        $this->_instance = Mage::getSingleton('postnl_core/system_config_source_euProductOptions');
    }

    public function dataProvider()
    {
        return array(
            'When EPS script is loaded' => array(
                array('4952', '4938', '4941'), true
            ),
            'Without EPS script' => array(
                array('4952', '4938'), false
            )
        );
    }

    /**
     * @dataProvider dataProvider
     * @param $productCodes
     * @param $epsScriptEnabled
     */
    public function testGetOptions($productCodes, $epsScriptEnabled)
    {
        $this->setHelper($epsScriptEnabled);
        $result  = array();
        foreach ($this->_instance->getOptions() as $option) {
            $result[] = $option['value'];
        }

        $this->assertEquals($productCodes, $result);
    }

    public function dataProviderOnlyAvond()
    {
        return array(
            'When EPS script is loaded' => array(
                array('4938', '4941'), true
            ),
            'Without EPS script' => array(
                array('4938'), false
            )
        );
    }

    /**
     * @dataProvider dataProviderOnlyAvond
     *
     * @param $productCodes
     * @param $epsScriptEnabled
     */
    public function testGetAvailableAvondOptions($productCodes, $epsScriptEnabled)
    {
        Mage::app()->getStore()->setConfig(
            'postnl/grid/supported_product_options',
            '4952,4938,4941'
        );

        $this->setHelper($epsScriptEnabled);

        $options = $this->_instance->getAvailableAvondOptions();
        $result  = array();
        foreach ($options as $option) {
            $result[] = $option['value'];
        }
        $this->assertEquals($productCodes, $result);
    }

    /**
     * @param $epsScriptEnabled
     */
    public function setHelper($epsScriptEnabled)
    {
        $dataHelperMock = $this->getMock('TIG_PostNL_Helper_Data');
        $dataHelperExpects = $dataHelperMock->method('canUseEpsBEOnlyOption');
        $dataHelperExpects->willReturn($epsScriptEnabled);

        $dataHelperExpectsCountry = $dataHelperMock->method('getDomesticCountry');
        $dataHelperExpectsCountry->willReturn('BE');

        $this->setProperty('_helper', $dataHelperMock, $this->_instance);
    }
}
