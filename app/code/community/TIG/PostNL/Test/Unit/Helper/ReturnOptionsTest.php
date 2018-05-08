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
class TIG_PostNL_Test_Unit_Helper_ReturnOptionsTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_ReturnOptions
     */
    protected function _getInstance()
    {
        return Mage::helper('postnl/returnOptions');
    }

    public function getDataProvider()
    {
        return array(
            'when shipping is NL and return country as well' => array(
                'NL',
                'NL',
                false,
                '2285'
            ),
            'when shipping is NL and return country as well, as array' => array(
                'NL',
                'NL',
                true,
                array(
                    'value'   => '2285',
                    'route'   => 'nl_nl',
                    'default' => true,
                )
            ),

            'when shipping is NL and return country is BE' => array(
                'BE',
                'NL',
                false,
                '3250'
            ),

            'when shipping is BE and return country is BE' => array(
                'BE',
                'BE',
                false,
                '4882'
            ),

            'when shipping is empty and return country is BE' => array(
                '',
                'BE',
                false,
                false
            ),

            'when shipping is GE and return country is BE' => array(
                'GE',
                'BE',
                false,
                false
            ),

            'when shipping is BE, data asArray and return country is NL' => array(
                'BE',
                'NL',
                true,
                array(
                    'value'   => '3250',
                    'route'   => 'nl_be',
                    'default' => true,
                )
            ),

            'when shipping is NL, data asArray and return country is BE' => array(
                'NL',
                'BE',
                true,
                false
            ),

            'when shipping is BE, data asArray and return country is BE' => array(
                'BE',
                'BE',
                true,
                array(
                    'value'   => '4882',
                    'route'   => 'be_be',
                    'default' => true
                )
            )
        );
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param $country
     * @param $returnCountry
     * @param $asArray
     * @param $expectedResult
     *
     */
    public function testGet($country, $returnCountry, $asArray, $expectedResult)
    {
        Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)
            ->setConfig('postnl/cif_address/country', $returnCountry);

        $result = $this->_getInstance()->get($country, $asArray);
        $this->assertEquals($expectedResult, $result);
    }
}
