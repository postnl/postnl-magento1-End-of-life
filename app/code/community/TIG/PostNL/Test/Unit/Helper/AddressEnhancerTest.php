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
class TIG_PostNL_Test_Unit_Helper_AddressEnhancerTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_AddressEnhancer
     */
    public function _getInstance()
    {
        /** @var TIG_PostNL_Helper_AddressEnhancer $instance */
        $instance = Mage::helper('postnl/addressEnhancer');
        return $instance;
    }

    public function addressDataProvider()
    {
        return array(
            'when empty street data' => array(
                null,
                array(
                    'streetname'           => '',
                    'housenumber'          => '',
                    'housenumberExtension' => '',
                    'fullStreet'           => '',
                )

            ),
            'when street is array' => array(
                array('Kabelweg', '37'),
                array(
                    'streetname'           => 'Kabelweg',
                    'housenumber'          => '37',
                    'housenumberExtension' => ''
                )
            ),
            'when street is flat' => array(
                'Kabelweg 37',
                array(
                    'streetname'           => 'Kabelweg',
                    'housenumber'          => '37',
                    'housenumberExtension' => ''
                )
            )
        );
    }

    /**
     * @param $address
     * @param $expected
     *
     * @dataProvider addressDataProvider
     */
    public function testGet($address, $expected)
    {
        $instance = $this->_getInstance();
        $instance->set($address);

        $this->assertEquals($expected, $instance->get());
    }

    public function testSetWithInvalidData()
    {
        $instance = $this->_getInstance();

        try {
            $instance->set('12345');
        } catch (TIG_PostNL_Exception $exception) {
            $this->assertEquals(
                $exception->getMessage(),
                'Unable to extract the house number, could not find a number inside the street value'
            );
            return;
        }

        $this->fail('We expected an exception but we got none');
    }
}
