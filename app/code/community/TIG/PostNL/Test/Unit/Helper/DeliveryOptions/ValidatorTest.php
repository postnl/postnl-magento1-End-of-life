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

class TIG_PostNL_Test_Unit_Helper_DeliveryOptions_ValidatorTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    /**
     * @return TIG_PostNL_Helper_DeliveryOptions_Validator
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/deliveryOptions_validator');
    }

    public function invalidCitiesProvider()
    {
        return array(
            array('123'),
            array('random123'),
        );
    }

    /**
     * @dataProvider invalidCitiesProvider
     * @expectedException TIG_PostNL_Exception
     * @expectedExceptionMessage Invalid city supplied for getNearestLocations request:
     *
     * @param $city
     */
    public function testInvalidCities($city)
    {
        $this->_getInstance()->validateCity($city);
    }

    public function validCitiesProvider()
    {
        return array(
            array('amsterdam'),
            array('\'s gravenhage'),
            array('den haag'),
            array('Stad aan \'t Haringvliet'),
        );
    }

    /**
     * @dataProvider validCitiesProvider
     *
     * @param $city
     */
    public function testValidCities($city)
    {
        $this->_getInstance()->validateCity($city);
    }
}
