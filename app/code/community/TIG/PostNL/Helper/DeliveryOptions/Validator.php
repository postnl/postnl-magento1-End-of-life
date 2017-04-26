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

class TIG_PostNL_Helper_DeliveryOptions_Validator extends Mage_Core_Helper_Abstract
{
    const CITY_NAME_REGEX = '#^[a-zA-Z]+(?:(?:\\s+|-)[a-zA-Z]+)*$#';
    const STREET_NAME_REGEX = "#^[\p{L}0-9\s,\'-.]*$#u";

    /**
     * @param $city
     *
     * @throws TIG_PostNL_Exception
     */
    public function validateCity($city)
    {
        $cityValidator = new Zend_Validate_Regex(array('pattern' => self::CITY_NAME_REGEX));

        /**
         * Some Dutch cities start with an apostrophe. They won't get past the city validation if we leave it there.
         */
        $cityToValidate = str_replace('\'', '', $city);
        if (!$cityValidator->isValid($cityToValidate)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid city supplied for getNearestLocations request: %s',
                    $city
                ),
                'POSTNL-0242'
            );
        }
    }

    /**
     * @param $housenumber
     *
     * @throws TIG_PostNL_Exception
     */
    public function validateHousenumber($housenumber)
    {
        $housenumberValidator      = new Zend_Validate_Digits();
        if (!$housenumberValidator->isValid($housenumber)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid housenumber supplied for getNearestLocations request: %s',
                    $housenumber
                ),
                'POSTNL-0242'
            );
        }
    }

    /**
     * @param $street
     *
     * @throws TIG_PostNL_Exception
     */
    public function validateStreet($street)
    {
        $streetValidator      = new Zend_Validate_Regex(array('pattern' => self::STREET_NAME_REGEX));
        if (!$streetValidator->isValid($street)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid street supplied for getNearestLocations request: %s',
                    $street
                ),
                'POSTNL-0242'
            );
        }
    }

    public function validatePostcode($country, $postcode)
    {
        $validator = new Zend_Validate_PostCode('nl_' . $country);
        if (!$validator->isValid($postcode)) {
            throw new TIG_PostNL_Exception(
                $this->__(
                    'Invalid postcode supplied for getNearestLocations request: %s',
                    $postcode
                ),
                'POSTNL-0118'
            );
        }
    }
}
